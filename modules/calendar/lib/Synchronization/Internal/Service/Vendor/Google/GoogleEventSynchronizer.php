<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Internal\Repository\EventRepository;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Google\Factory;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\NoLogSynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\SyncTokenNotValidException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Sender\EventSender;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\EventSynchronizerInterface;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\EventSynchronizerTrait;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway\GoogleEventGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Processor\EventImportProcessor;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class GoogleEventSynchronizer extends AbstractGoogleSynchronizer implements EventSynchronizerInterface
{
	private const MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION = 1;

	use EventSynchronizerTrait;

	public function __construct(
		private readonly EventRepository $eventRepository,
		private readonly EventConnectionRepository $eventConnectionRepository,
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly GoogleGatewayProvider $getawayProvider,
		private readonly EventImportProcessor $importProcessor,
		private readonly EventSender $eventSender,
		ConnectionProvider $connectionProvider,
		ConnectionManager $connectionManager,
		RequestLogger $logger,
	)
	{
		parent::__construct($connectionProvider, $connectionManager, $logger);
	}

	/**
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 * @throws ArgumentException
	 */
	public function sendEvent(Event $event): void
	{
		$userId = (int)$event->getOwner()?->getId();

		if ($userId === 0)
		{
			throw new ArgumentException('Event has no owner');
		}

		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$sectionConnection = $this->sectionConnectionRepository->findOneBySectionAndConnectionId(
			$event->getSection()->getId(),
			$connection->getId()
		);

		if (!$sectionConnection)
		{
			// Calendar was removed
			return;
		}

		$this->configureLoggerContext($userId, $event->getId());

		$this->prepareEventExDates($event);

		$gateway = $this->getEventGateway($userId, $connection);

		$eventConnection = $this->getEventConnection($event->getId(), $connection->getId());

		if ($eventConnection)
		{
			$this->updateEvent($event, $eventConnection, $connection, $sectionConnection, $gateway);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		$this->createEvent($event, $connection, $sectionConnection, $gateway);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws SynchronizerException
	 */
	private function prepareEventExDates(Event $event): void
	{
		if ($event->getExcludedDateCollection()->count() === 0)
		{
			return;
		}

		try
		{
			$instances = $this->eventRepository->getEventInstances($event);
		}
		catch (RepositoryReadException $e)
		{
			throw new SynchronizerException(
				sprintf('Unable to get event instances: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		foreach ($instances as $instance)
		{
			if ($originalDate = $instance->getOriginalDateFrom())
			{
				$event->getExcludedDateCollection()->removeDateFromCollection($originalDate);
			}
		}
	}

	/**
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 */
	private function createEvent(
		Event $event,
		Connection $connection,
		SectionConnection $sectionConnection,
		GoogleEventGateway $gateway
	): void
	{
		try
		{
			if ($sectionConnection->isActive())
			{
				$eventResponse = $this->safeCreateEvent($event, $sectionConnection, $gateway);

				$eventConnection = new EventConnection();

				$eventConnection
					->setEvent($event)
					->setConnection($connection)
					->setVendorEventId($eventResponse->id)
					->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
					->setRetryCount()
					->setEntityTag($eventResponse->etag)
					->setVersion($event->getVersion())
					->setVendorVersionId($eventResponse->getVersion()) // sequence ???
					->setRecurrenceId($eventResponse->recurringEventId)
					->setData([]
					) // @todo \Bitrix\Calendar\Sync\Google\Builders\BuilderSyncEventFromExternalData::getEventConnectionData
				;

				$this->eventConnectionRepository->save($eventConnection);
			}
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Google authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (RateLimitExceededException $e)
		{
			throw new SynchronizerException(
				sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
		catch (BadRequestException|AccessDeniedException|NotFoundException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception on create event: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception on create event: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
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
		GoogleEventGateway $gateway
	): void
	{
		if ($sectionConnection->isActive())
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
					$eventResponse = $gateway->updateEvent(
						$event,
						$eventConnection,
						$sectionConnection->getVendorSectionId()
					);
				}
				else
				{
					// EventConnection may have an empty value of vendorEventId because in legacy logic
					// it creates in case of deleted section on the vendor's side.
					// See GoogleEventSynchronizer::createEvent logic
					$eventResponse = $this->safeCreateEvent($event, $sectionConnection, $gateway);
				}

				$eventConnection
					->setVendorEventId($eventResponse->id)
					->setEntityTag($eventResponse->etag)
					->setVendorVersionId($eventResponse->getVersion())
					->setVersion($event->getVersion())
					->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				;
			}
			catch (NotAuthorizedException $e)
			{
				$this->handleUnauthorizedException($connection);

				throw new SynchronizerException(
					sprintf('Google authorization exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (RateLimitExceededException $e)
			{
				throw new SynchronizerException(
					sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
					$e->getCode(),
					$e
				);
			}
			catch (NotFoundException $e)
			{
				throw new SynchronizerException(
					'EventConnection not found',
					previous: $e,
					isRecoverable: false
				);
			}
			catch (BadRequestException|AccessDeniedException $e)
			{
				throw new SynchronizerException(
					sprintf('Google API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (ApiException|DtoValidationException $e)
			{
				throw new SynchronizerException(
					sprintf('Google API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e
				);
			}
			finally
			{
				$this->eventConnectionRepository->save($eventConnection);
			}
		}
		else
		{
			$eventConnection
				->setVersion($event->getVersion())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['update'])
			;
		}

		$this->eventConnectionRepository->save($eventConnection);
	}

	/**
	 * @throws SynchronizerException
	 * @throws RepositoryReadException
	 * @throws PersistenceException
	 */
	public function deleteEvent(string $vendorEventId, string $vendorSectionId, int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$gateway = $this->getEventGateway($userId, $connection);

		$this->configureLoggerContext($userId, $vendorEventId);

		if ($connection->getId())
		{
			try
			{
				$gateway->deleteEvent($vendorSectionId, $vendorEventId);

				$this->eventConnectionRepository->deleteByVendorId($vendorEventId);

				$this->connectionManager->updateConnection($connection);
			}
			catch (NotAuthorizedException $e)
			{
				$this->handleUnauthorizedException($connection);

				throw new SynchronizerException(
					sprintf('Google authorization exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (RateLimitExceededException $e)
			{
				throw new SynchronizerException(
					sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
					$e->getCode(),
					$e
				);
			}
			catch (BadRequestException|AccessDeniedException $e)
			{
				throw new SynchronizerException(
					sprintf('Google API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (ApiException $e)
			{
				throw new SynchronizerException(
					sprintf('Google API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e
				);
			}
		}
	}

	/**
	 * @throws LogicException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 */
	public function sendInstance(Event $event): void
	{
		// @todo Code duplication
		$userId = $event->getOwner()->getId();

		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$sectionConnection = $this->sectionConnectionRepository->findOneBySectionAndConnectionId(
			$event->getSection()->getId(),
			$connection->getId()
		);

		if (!$sectionConnection)
		{
			return;
		}

		$gateway = $this->getEventGateway($userId, $connection);

		$this->configureLoggerContext($userId, $event->getId());

		$eventConnection = $this->getEventConnection($event->getId(), $connection->getId());

		if ($eventConnection)
		{
			$this->updateInstance($event, $eventConnection, $connection, $sectionConnection, $gateway);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		$this->createInstance($event, $connection, $sectionConnection, $gateway);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws LogicException
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 */
	private function createInstance(
		Event $event,
		Connection $connection,
		SectionConnection $sectionConnection,
		GoogleEventGateway $gateway
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

		if ($masterEventConnection->getEvent()->getVersion() > $event->getVersion())
		{
			// @todo Side effect?
			$event->setVersion($masterEventConnection->getEvent()->getVersion());
		}

		// Replace to?
//		$version = max($masterEventConnection->getEvent()->getVersion(), $event->getVersion());

		try
		{
			$eventResponse = $gateway->createInstance(
				$event,
				$masterEventConnection->getVendorEventId(),
				$sectionConnection->getVendorSectionId()
			);

			$eventConnection = new EventConnection();

			$eventConnection
				->setEvent($event)
				->setConnection($connection)
				->setVendorEventId($eventResponse->id)
				->setEntityTag($eventResponse->etag)
				->setVendorVersionId($eventResponse->getVersion())
				->setRecurrenceId($eventResponse->recurringEventId)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVersion($event->getVersion())//				->setVersion($version)
			;

			$this->eventConnectionRepository->save($eventConnection);
		}
		catch (NotAuthorizedException $e)
		{
			$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
			$this->eventConnectionRepository->save($masterEventConnection);

			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Google authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (RateLimitExceededException $e)
		{
			throw new SynchronizerException(
				sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
		catch (NotFoundException)
		{
			$this->eventConnectionRepository->save($masterEventConnection);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
			$this->eventConnectionRepository->save($masterEventConnection);

			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
	}

	/**
	 * @throws LogicException
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 */
	private function updateInstance(
		Event $event,
		EventConnection $eventConnection,
		Connection $connection,
		SectionConnection $sectionConnection,
		GoogleEventGateway $gateway
	): void
	{
		$masterEventConnection = $this->getMasterEventConnection($event, $connection);

		try
		{
			$response = $gateway->updateEvent($event, $eventConnection, $sectionConnection->getVendorSectionId());

			$eventConnection
				->setVendorEventId($response->id) // @todo Can be nullable?
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVersion($event->getVersion())
				->setVendorVersionId($response->getVersion())
				->setEntityTag($response->etag)
			;

			$this->eventConnectionRepository->save($eventConnection);
		}
		catch (NotAuthorizedException $e)
		{
			$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
			$this->eventConnectionRepository->save($masterEventConnection);

			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Google authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (RateLimitExceededException $e)
		{
			throw new SynchronizerException(
				sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
		catch (BadRequestException|AccessDeniedException|NotFoundException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);
			$this->eventConnectionRepository->save($masterEventConnection);

			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
	}

	/**
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 */
	public function deleteInstance(Event $event, Date $excludeDate, ?Date $originalDate = null): void
	{
		$userId = $event->getOwner()->getId();

		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$sectionConnection = $this->sectionConnectionRepository->findOneBySectionAndConnectionId(
			$event->getSection()->getId(),
			$connection->getId()
		);

		if (!$sectionConnection)
		{
			return;
		}

		$this->configureLoggerContext($userId, $event->getId());

		$gateway = $this->getEventGateway($userId, $connection);

		$eventConnection = $this->getEventConnection($event->getId(), $connection->getId());

		if (!$eventConnection)
		{
			throw new SynchronizerException(
				message: sprintf('Event connection for event "%s" not found', $event->getId()),
				isRecoverable: false
			);
		}

		if ($event->isInstance())
		{
			$this->deleteEvent($eventConnection->getVendorEventId(), $sectionConnection->getVendorSectionId(), $userId);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		try
		{
			$response = $gateway->deleteInstance(
				$event,
				$eventConnection->getVendorEventId(),
				$sectionConnection->getVendorSectionId(),
				$excludeDate,
				$originalDate
			);

			$eventConnection
				->setEntityTag($response->etag)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVersion($eventConnection->getEvent()->getVersion())
			;

			$this->connectionManager->updateConnection($connection);
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Google authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (RateLimitExceededException $e)
		{
			throw new SynchronizerException(
				sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
		catch (BadRequestException|AccessDeniedException|NotFoundException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($eventConnection);
		}
	}

	/**
	 * @throws SynchronizerException
	 */
	private function getEventGateway(int $userId, Connection $connection): GoogleEventGateway
	{
		try
		{
			$gateway = $this->getawayProvider->getEventGateway($userId);
		}
		catch (NotAuthorizedException $e)
		{
			throw new SynchronizerException(
				sprintf('Unable to get gateway: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}

		if (!$gateway)
		{
			$this->deactivateConnection($connection);

			throw new SynchronizerException(
				'Unable to get gateway: "User should be authorized in Google"',
				previous: new NotAuthorizedException('User should be authorized in Google'),
				isRecoverable: false,
			);
		}

		return $gateway;
	}

	/**
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
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
			catch (NotAuthorizedException $e)
			{
				$this->handleUnauthorizedException($connection);

				throw new SynchronizerException(
					sprintf('Google authorization exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (RateLimitExceededException $e)
			{
				throw new SynchronizerException(
					sprintf('Google rate limit exceeded: "%s"', $e->getMessage()),
					$e->getCode(),
					$e
				);
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
			catch (ApiException|DtoValidationException $e)
			{
				throw new SynchronizerException(
					sprintf('Google API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e
				);
			}
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function exportEvents(int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$sectionIds = $this->getExportingSectionIds($connection);

		if (empty($sectionIds))
		{
			return;
		}

		$this->configureLoggerContext($userId);

		$events = $this->eventRepository->getExportingEventsBySectionIds($sectionIds, $userId, $connection->getId());

		foreach ($events as $event)
		{
			if ($event->isDeleted())
			{
				$this->eventSender->sendDeletedMessage($event);

				continue;
			}

			$this->eventSender->sendUpdatedMessageForVendor($event, Factory::SERVICE_NAME);
		}
	}

	/**
	 * @param Connection $connection
	 *
	 * @return int[]
	 */
	private function getExportingSectionIds(Connection $connection): array
	{
		$sectionIds = [];

		$sectionConnections = $this->sectionConnectionRepository->getByConnection($connection->getId());

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionConnections as $sectionConnection)
		{
			$section = $sectionConnection->getSection();

			if ($section && $section->isActive())
			{
				$sectionIds[] = $sectionConnection->getSection()->getId();
			}
		}

		return $sectionIds;
	}

	/**
	 * @throws LogicException
	 * @throws SynchronizerException
	 */
	private function getMasterEventConnection(Event $event, Connection $connection): EventConnection
	{
		/** @var Event $masterEvent */
		try
		{
			$masterEvent = $this->eventRepository->getMasterEvent($event);
		}
		catch (RepositoryReadException $e)
		{
			throw new SynchronizerException(
				sprintf('Unable to get master event for event "%s": "%s"', $event->getId(), $e->getMessage()),
				previous: $e
			);
		}

		if (!$masterEvent)
		{
			throw new LogicException(sprintf('Master event for the event "%s" not found', $event->getId()));
		}

		$masterEventConnection = $this->getEventConnection($masterEvent->getId(), $connection->getId());

		if (!$masterEventConnection)
		{
			$modifiedTime = $masterEvent->getDateModified()?->getTimestamp();

			throw new NoLogSynchronizerException(
				sprintf('The master event "%s" has no connection with Google', $masterEvent->getId()),
				code: self::MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION,
				isRecoverable: $modifiedTime && $modifiedTime + 86400 > time(),
			);
		}

		return $masterEventConnection;
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PersistenceException
	 * @throws RateLimitExceededException
	 * @throws SynchronizerException
	 * @throws UnexpectedException
	 */
	public function importSectionEvents(SectionConnection $sectionConnection): void
	{
		$connection = $sectionConnection->getConnection();

		$this->configureLoggerContext($connection->getOwner()->getId(), $sectionConnection->getSection()?->getId());

		$gateway = $this->getEventGateway($connection->getOwner()->getId(), $connection);

		try
		{
			$eventList = $gateway->getEvents($sectionConnection);
		}
		catch (SyncTokenNotValidException)
		{
			$sectionConnection->setSyncToken(null);

			$this->importSectionEvents($sectionConnection);

			return;
		}

		try
		{
			$this->importProcessor->import($sectionConnection, $eventList);
		}
		catch (SystemException $e)
		{
			throw new SynchronizerException(
				sprintf('An import section events exception: "%s"', $e->getMessage()),
				previous: $e
			);
		}

		$sectionConnection
			->setSyncToken($eventList->nextSyncToken)
			->setPageToken($eventList->nextPageToken)
			->setVersionId($eventList->etag)
			->setLastSyncStatus(Dictionary::SYNC_SECTION_ACTION['success'])
			->setLastSyncDate(new Date())
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	/**
	 * @throws LogicException
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 */
	public function reCreateRecurrence(Event $masterEvent): void
	{
		$userId = $masterEvent->getOwner()->getId();

		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$eventConnection = $this->getEventConnection($masterEvent->getId(), $connection->getId());

		if (!$eventConnection)
		{
			return;
		}

		$sectionConnection = $this->sectionConnectionRepository->findOneBySectionAndConnectionId(
			$masterEvent->getSection()->getId(),
			$connection->getId()
		);

		if (!$sectionConnection)
		{
			return;
		}

		if (!$sectionConnection->isActive())
		{
			$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['delete']);

			$this->eventConnectionRepository->save($eventConnection);

			return;
		}

		$this->configureLoggerContext($userId, $masterEvent->getId());

		$this->deleteEvent($eventConnection->getVendorEventId(), $sectionConnection->getVendorSectionId(), $userId);

		// Delete old EventConnections
		$instances = $this->eventRepository->getEventInstances($masterEvent);

		foreach ($instances as $instance)
		{
			if ($instanceConnection = $this->getEventConnection($instance->getId(), $connection->getId()))
			{
				$this->eventConnectionRepository->delete($instanceConnection->getId());
			}
		}

		$this->eventConnectionRepository->delete($eventConnection->getId());

		// Create full recurrence event
		$this->sendRecurrence($masterEvent, $sectionConnection);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws LogicException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws PersistenceException
	 */
	private function sendRecurrence(Event $masterEvent, SectionConnection $sectionConnection): void
	{
		$connection = $sectionConnection->getConnection();

		$instances = $this->eventRepository->getEventInstances($masterEvent);
		$instanceConnections = [];

		foreach ($instances as $instance)
		{
			$masterEvent->getExcludedDateCollection()->removeDateFromCollection($instance->getOriginalDateFrom());

			if ($instanceConnection = $this->getEventConnection($instance->getId(), $connection->getId()))
			{
				$instanceConnections[$instance->getId()] = $instanceConnection;
			}
		}

		$eventConnection = $this->getEventConnection($masterEvent->getId(), $connection->getId());

		// From old sync
		$masterEvent->setUid($eventConnection?->getVendorEventId());

		if ($eventConnection)
		{
			$this->updateRecurrenceEntity($masterEvent, $eventConnection, $sectionConnection);
		}
		else
		{
			$this->createRecurrenceEntity($masterEvent, $sectionConnection);
		}

		foreach ($instances as $instance)
		{
			if (isset($instanceConnections[$instance->getId()]))
			{
				$this->updateRecurrenceEntity($instance, $instanceConnections[$instance->getId()], $sectionConnection);
			}
			else
			{
				$this->createRecurrenceEntity($instance, $sectionConnection);
			}
		}
	}

	/**
	 * @throws LogicException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 */
	private function createRecurrenceEntity(Event $event, SectionConnection $sectionConnection): void
	{
		$connection = $sectionConnection->getConnection();

		$gateway = $this->getEventGateway($event->getOwner()->getId(), $connection);

		if ($event->isInstance())
		{
			$this->createInstance($event, $connection, $sectionConnection, $gateway);
		}
		else
		{
			$this->createEvent($event, $connection, $sectionConnection, $gateway);
		}
	}

	/**
	 * @throws LogicException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 */
	private function updateRecurrenceEntity(
		Event $event,
		EventConnection $eventConnection,
		SectionConnection $sectionConnection
	): void
	{
		$connection = $sectionConnection->getConnection();

		$gateway = $this->getEventGateway($event->getOwner()->getId(), $connection);

		if ($event->isInstance())
		{
			$this->updateInstance($event, $eventConnection, $connection, $sectionConnection, $gateway);
		}
		else
		{
			$this->updateEvent($event, $eventConnection, $connection, $sectionConnection, $gateway);
		}
	}

	/**
	 * @throws AccessDeniedException
	 * @throws BadRequestException
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws RateLimitExceededException
	 * @throws UnexpectedException
	 */
	private function safeCreateEvent(
		Event $event,
		SectionConnection $sectionConnection,
		GoogleEventGateway $gateway,
	): Dto\EventResponse
	{
		try
		{
			return $gateway->createEvent($event, $sectionConnection->getVendorSectionId());
		}
		catch (ConflictException $e)
		{
			if ($event->getUid())
			{
				$event->setUid(null);

				return $this->safeCreateEvent($event, $sectionConnection, $gateway);
			}

			throw $e;
		}
	}
}
