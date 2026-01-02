<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Internal\Repository\EventRepository;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Exceptions\PreconditionFailedException;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AuthorizationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\EventSynchronizerInterface;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\EventSynchronizerTrait;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway\Office365EventGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor\EventImportProcessor;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use DateTimeZone;
use Psr\Container\NotFoundExceptionInterface;

class Office365EventSynchronizer extends AbstractOffice365Synchronizer implements EventSynchronizerInterface
{
	private const MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION = 1;

	use EventSynchronizerTrait;

	public function __construct(
		private readonly Office365GatewayProvider $gatewayProvider,
		private readonly EventConnectionRepository $eventConnectionRepository,
		private readonly EventImportProcessor $importProcessor,
		private readonly EventRepository $eventRepository,
		ConnectionProvider $connectionProvider,
		ConnectionManager $connectionManager,
		SectionConnectionRepository $sectionConnectionRepository,
		RequestLogger $logger,
	)
	{
		parent::__construct($connectionProvider, $connectionManager, $sectionConnectionRepository, $logger);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws RepositoryReadException
	 */
	public function sendEvent(Event $event): void
	{
		$ownerId = (int)$event->getOwner()?->getId();

		if ($ownerId === 0)
		{
			throw new ArgumentException('Event has no owner');
		}

		$connection = $this->getUserConnection($ownerId);

		if (!$connection)
		{
			return;
		}

		$sectionConnection = $this->getSectionConnection($event->getSection()->getId(), $connection->getId());

		if (!$sectionConnection)
		{
			throw new SynchronizerException(
				sprintf('Section connection for event "%s" not found', $event->getSection()->getId()),
				isRecoverable: false
			);
		}

		$this->configureLoggerContext($ownerId, $event->getId());

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		$eventConnection = $this->getEventConnection($event->getId(), $connection->getId());

		if ($eventConnection)
		{
			if (!$sectionConnection->isActive())
			{
				$eventConnection
					->setVersion($event->getVersion())
					->setLastSyncStatus(Dictionary::SYNC_STATUS['update'])
				;

				$this->eventConnectionRepository->save($eventConnection);

				return;
			}

			$this->updateEvent($event, $eventConnection, $connection, $sectionConnection, $gateway);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		if (!$sectionConnection->isActive())
		{
			return;
		}

		$this->createEvent($event, $connection, $sectionConnection, $gateway);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 */
	private function createEvent(
		Event $event,
		Connection $connection,
		SectionConnection $sectionConnection,
		Office365EventGateway $gateway,
	): void
	{
		$eventConnection =
			(new EventConnection())
				->setEvent($event)
				->setConnection($connection)
				// For supporting a legacy logic
				->setLastSyncStatus(Dictionary::SYNC_STATUS['create'])
		;

		try
		{
			$eventResponse = $gateway->createEvent($event, $sectionConnection->getVendorSectionId());

			$eventConnection
				->setData($eventResponse->customData)
				->setVersion($event->getVersion())
				->setEntityTag($eventResponse->etag)
				->setRecurrenceId($eventResponse->recurrence)
				->setVendorEventId($eventResponse->id)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVendorVersionId($eventResponse->version)
			;
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (NotFoundException $e)
		{
			// If the section was deleted on the vendor side
			$sectionConnection
				->setActive(false)
				->setSyncToken(null)
				->setPageToken(null)
				->setLastSyncDate(new Date())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
			;

			$this->sectionConnectionRepository->save($sectionConnection);

			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($eventConnection);
		}

		if ($event->isRecurrence())
		{
			$this->deleteExcludedInstances($event);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	private function updateEvent(
		Event $event,
		EventConnection $eventConnection,
		Connection $connection,
		SectionConnection $sectionConnection,
		Office365EventGateway $gateway,
	): void
	{
		if ($eventConnection->getVersion() === $event->getVersion())
		{
			$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success']);

			$this->eventConnectionRepository->save($eventConnection);

			return;
		}

		try
		{
			$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

			if ($eventConnection->getVendorEventId())
			{
				$eventResponse = $gateway->updateEvent($event, $eventConnection->getVendorEventId());
			}
			else
			{
				// EventConnection may have an empty value of vendorEventId because in legacy logic
				// it creates in case of deleted section on the vendor's side.
				// See Office365EventSynchronizer::createEvent logic
				$eventResponse = $gateway->createEvent($event, $sectionConnection->getVendorSectionId());
			}

			$eventConnection
				->setData($eventResponse->customData)
				->setVersion($event->getVersion())
				->setEntityTag($eventResponse->etag)
				->setRecurrenceId($eventResponse->recurrence)
				->setVendorEventId($eventResponse->id)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVendorVersionId($eventResponse->version)
			;

			$this->eventConnectionRepository->save($eventConnection);
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			$this->eventConnectionRepository->save($eventConnection);

			throw new SynchronizerException(
				sprintf('Office365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (NotFoundException $e)
		{
			// If the event was deleted on the vendor side
			$this->eventConnectionRepository->delete($eventConnection->getId());

			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			$this->eventConnectionRepository->save($eventConnection);

			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function deleteEvent(string $vendorEventId, int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$this->configureLoggerContext($userId, $vendorEventId);

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		try
		{
			$gateway->deleteEvent($vendorEventId);

			$this->eventConnectionRepository->deleteByVendorId($vendorEventId);

			$this->connectionManager->updateConnection($connection);
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException $e)
		{
			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 */
	private function deleteExcludedInstances(Event $event): void
	{
		$excludedDateCollection = $event->getExcludedDateCollection();

		if (!$excludedDateCollection)
		{
			return;
		}

		$instances = $this->eventRepository->getEventInstances($event);

		$instancesDates = [];

		foreach ($instances as $instance)
		{
			if ($originalDateFrom = $instance->getOriginalDateFrom())
			{
				$instancesDates[] = $originalDateFrom->format('Ymd');
			}
		}

		foreach ($excludedDateCollection->getCollection() as $excludedDate)
		{
			if (!in_array($excludedDate->format('Ymd'), $instancesDates, true))
			{
				$this->deleteInstance($event, $excludedDate);
			}
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws RepositoryReadException
	 * @throws LogicException
	 */
	public function sendInstance(Event $event): void
	{
		$ownerId = (int)$event->getOwner()?->getId();

		if ($ownerId === 0)
		{
			throw new ArgumentException('Event has no owner');
		}

		$connection = $this->getUserConnection($ownerId);

		if (!$connection)
		{
			return;
		}

		$sectionConnection = $this->getSectionConnection($event->getSection()->getId(), $connection->getId());

		if (!$sectionConnection)
		{
			throw new SynchronizerException(
				sprintf('Section connection for event "%s" not found', $event->getSection()->getId()),
				isRecoverable: false
			);
		}

		$this->configureLoggerContext($ownerId, $event->getId());

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		$eventConnection = $this->getEventConnection($event->getId(), $connection->getId());

		if ($eventConnection && $eventConnection->getVendorEventId())
		{
			if (!$sectionConnection->isActive())
			{
				$eventConnection
					->setVersion($event->getVersion())
					->setLastSyncStatus(Dictionary::SYNC_STATUS['update'])
				;

				$this->eventConnectionRepository->save($eventConnection);

				return;
			}

			$this->updateInstance($event, $eventConnection, $connection, $gateway);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		if (!$sectionConnection->isActive())
		{
			return;
		}

		$this->createInstance($event, $connection, $gateway, $eventConnection);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws RepositoryReadException
	 * @throws LogicException
	 */
	private function createInstance(
		Event $event,
		Connection $connection,
		Office365EventGateway $gateway,
		?EventConnection $eventConnection = null
	): void
	{
		try
		{
			$masterEventConnection = $this->getMasterEventConnection($event, $connection);
		}
		catch (SynchronizerException $e)
		{
			if ($e->getCode() === self::MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION)
			{
				throw new SynchronizerException(
					$e->getMessage(),
					isRecoverable: false
				);
			}

			throw $e;
		}

		$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		if (!$eventConnection)
		{
			$eventConnection = new EventConnection();
		}

		$eventConnection
			->setEvent($event)
			->setConnection($connection)
			->setLastSyncStatus(Dictionary::SYNC_STATUS['create'])
		;

		try
		{
			if (
				($originalDateFrom = $event->getOriginalDateFrom())
				&& $originalDateFrom->format('Ymd') !== $event->getStart()->format('Ymd')
			)
			{
				$instance = $gateway->getInstanceForDay(
					$masterEventConnection->getVendorEventId(),
					$originalDateFrom->getDate(),
				);
			}
			else
			{
				$instance = $gateway->getInstanceForDay(
					$masterEventConnection->getVendorEventId(),
					$event->getStart()->getDate(),
				);
			}

			if (!$instance)
			{
				throw new SynchronizerException(
					message: sprintf('Instances for event "%s" not found', $masterEventConnection->getEvent()->getId()),
					isRecoverable: false,
				);
			}

			$eventConnection->setVendorEventId($instance->id);

			$eventResponse = $gateway->updateEvent($event, $eventConnection->getVendorEventId());

			$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success']);

			$eventConnection
				->setData($eventResponse->customData)
				->setVersion($event->getVersion())
				->setEntityTag($eventResponse->etag)
				->setRecurrenceId($eventResponse->recurrence)
				->setVendorEventId($eventResponse->id)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVendorVersionId($eventResponse->version)
			;

			$this->connectionManager->updateConnection($connection);
		}
		catch (SynchronizerException $e)
		{
			throw $e;
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException|Exception $e)
		{
			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($masterEventConnection);
			$this->eventConnectionRepository->save($eventConnection);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	private function updateInstance(
		Event $event,
		EventConnection $eventConnection,
		Connection $connection,
		Office365EventGateway $gateway,
	): void
	{
		$masterEventConnection = $this->getMasterEventConnection($event, $connection);
		$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		try
		{
			$eventResponse = $gateway->updateEvent($event, $eventConnection->getVendorEventId());

			$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success']);

			$eventConnection
				->setData($eventResponse->customData)
				->setVersion($event->getVersion())
				->setEntityTag($eventResponse->etag)
				->setVendorEventId($eventResponse->id)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVendorVersionId($eventResponse->version)
			;
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			if ((int)$e->getCode() !== 400)
			{
				throw new SynchronizerException(
					sprintf('Office365 API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
				);
			}
		}
		finally
		{
			$this->eventConnectionRepository->save($masterEventConnection);

			$this->eventConnectionRepository->save($eventConnection);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws RepositoryReadException
	 */
	public function deleteInstance(Event $event, Date $excludedDate): void
	{
		$ownerId = (int)$event->getOwner()?->getId();

		if ($ownerId === 0)
		{
			throw new ArgumentException('Event has no owner');
		}

		$connection = $this->getUserConnection($ownerId);

		if (!$connection)
		{
			return;
		}

		$sectionConnection = $this->sectionConnectionRepository->findOneBySectionAndConnectionId(
			$event->getSection()->getId(),
			$connection->getId(),
		);

		if (!$sectionConnection)
		{
			throw new SynchronizerException(
				sprintf('Section connection for event "%s" not found', $event->getSection()->getId()),
				isRecoverable: false
			);
		}

		$this->configureLoggerContext($ownerId, $event->getId());

		$eventConnection = $this->getEventConnection($event->getId(), $connection->getId());

		if (!$eventConnection)
		{
			throw new SynchronizerException(
				sprintf('Event connection for event "%s" not found', $event->getId()),
				isRecoverable: false
			);
		}

		if ($event->isInstance())
		{
			$this->deleteEvent($eventConnection->getVendorEventId(), $ownerId);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		try
		{
			$preparedExcludedDate = clone $excludedDate->getDate();

			if ($preparedExcludedDate instanceof DateTime)
			{
				$excludedTimeZone =
					$event->getStartTimeZone()
						? $event->getStartTimeZone()->getTimeZone()
						: new DateTimeZone('UTC')
				;

				$preparedExcludedDate = new DateTime(
					$preparedExcludedDate->format('Ymd 000000'),
					'Ymd His',
					$excludedTimeZone,
				);
			}

			$gateway->deleteInstance(
				$eventConnection->getVendorEventId(),
				$preparedExcludedDate,
			);

			// Delete return nothing. You can't update etag.
			$eventConnection
				->setEntityTag()
				->setVersion($event->getVersion())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			;

			$this->connectionManager->updateConnection($connection);
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException $e)
		{
			throw new SynchronizerException(
				sprintf('Office365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($eventConnection);
		}
	}

	/**
	 * @throws BaseException
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws SystemException
	 * @throws \DateInvalidTimeZoneException
	 * @throws \DateMalformedStringException
	 */
	public function importEvents(int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$sectionConnections = $this->sectionConnectionRepository->getByConnection($connection->getId());

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionConnections as $sectionConnection)
		{
			try
			{
				if (!$sectionConnection->isActive() || !$sectionConnection->getSection())
				{
					continue;
				}

				$this->importSectionEvents($sectionConnection);
			}
			catch (NotFoundException)
			{
				// If the section was deleted on the vendor side
				$sectionConnection
					->setActive(false)
					->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
				;

				$this->sectionConnectionRepository->save($sectionConnection);
			}
			catch (ApiException|DtoValidationException|ArgumentException|Exception|PreconditionFailedException $e)
			{
				throw new SynchronizerException(
					sprintf('Office 365 API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
				);
			}
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws NotFoundExceptionInterface
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws UnexpectedException
	 * @throws \DateInvalidTimeZoneException
	 * @throws \DateMalformedStringException
	 * @throws SystemException
	 */
	public function importSectionEvents(SectionConnection $sectionConnection): void
	{
		$connection = $sectionConnection->getConnection();

		$this->configureLoggerContext($connection->getOwner()->getId(), $sectionConnection->getSection()?->getId());

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		$breakingFlag = false;

		do
		{
			try
			{
				$eventList = $gateway->getDeltaEvents($sectionConnection);

				$breakingFlag = $this->processResponseAfterDelta($sectionConnection, $eventList);

				if (empty($eventList->getItems()))
				{
					break;
				}

				$this->importProcessor->import($sectionConnection, $eventList);
			}
			catch (GoneException)
			{
				if ($sectionConnection->getPageToken())
				{
					$sectionConnection->setPageToken(null);
				}
				elseif ($sectionConnection->getSyncToken())
				{
					$sectionConnection->setSyncToken(null);
				}
			}
		}
		while (!$breakingFlag);

		$sectionConnection
//			->setVersionId($eventList->etag)
			->setLastSyncStatus(Dictionary::SYNC_SECTION_ACTION['success'])
			->setLastSyncDate(new Date())
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	private function processResponseAfterDelta(SectionConnection $sectionConnection, EventListResponse $response): bool
	{
		$sectionConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success']);

		$breakingFlag = true;

		if ($token = $response->getPageToken())
		{
			$sectionConnection->setPageToken($token);

			$breakingFlag = false;
		}
		elseif ($token = $response->getSyncToken())
		{
			$sectionConnection->setPageToken(null);
			$sectionConnection->setSyncToken($token);
		}
		else
		{
			$sectionConnection->setPageToken(null);
			$sectionConnection->setSyncToken(null);
		}

		return $breakingFlag;
	}

	/**
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	private function getMasterEventConnection(Event $event, Connection $connection): EventConnection
	{
		$masterEvent = $this->eventRepository->getMasterEvent($event);

		if (!$masterEvent)
		{
			throw new LogicException(sprintf('Master event for the event "%s" not found', $event->getId()));
		}

		$masterEventConnection = $this->getEventConnection($masterEvent->getId(), $connection->getId());

		if (!$masterEventConnection)
		{
			throw new SynchronizerException(
				sprintf('The master event "%s" has no connection with Office365', $masterEvent->getId()),
				code: self::MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION,
			);
		}

		return $masterEventConnection;
	}

	private function getUserGateway(Connection $connection): ?Office365EventGateway
	{
		try
		{
			return $this->gatewayProvider->getEventGateway($connection->getOwner()->getId());
		}
		catch (AuthorizationException)
		{
			$this->handleAuthorizationException($connection);
		}

		return null;
	}
}
