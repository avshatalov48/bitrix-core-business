<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Internal\Repository\EventRepository;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\LogicException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Messenger\MessageSendingException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
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
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\EventImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway\ICloudEventGateway;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class ICloudEventSynchronizer extends AbstractICloudSynchronizer implements EventSynchronizerInterface
{
	private const MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION = 1;

	use EventSynchronizerTrait;

	public function __construct(
		private readonly EventConnectionRepository $eventConnectionRepository,
		private readonly EventImportProcessor $importProcessor,
		private readonly EventRepository $eventRepository,
		private readonly EventSender $sender,
		ConnectionProvider $connectionProvider,
		SectionConnectionRepository $sectionConnectionRepository,
		ICloudGatewayProvider $gatewayProvider,
		ConnectionManager $connectionManager,
		RequestLogger $logger,
	)
	{
		parent::__construct(
			$connectionProvider,
			$sectionConnectionRepository,
			$gatewayProvider,
			$connectionManager,
			$logger,
		);
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
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
			if (!$sectionConnection->isActive() || !$sectionConnection->getSection())
			{
				continue;
			}

			try
			{
				$this->importSectionEvents($sectionConnection);
			}
			catch (NotAuthorizedException $e)
			{
				$this->handleUnauthorizedException($connection);

				throw new SynchronizerException(
					sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
					isRecoverable: false,
				);
			}
			catch (NotFoundException)
			{
				// If the section was deleted on the vendor side
				$sectionConnection
					->setActive(false)
					->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
					->setLastSyncDate(new Date())
				;

				$this->sectionConnectionRepository->save($sectionConnection);
			}
			catch (ApiException|DtoValidationException|Exception $e)
			{
				throw new SynchronizerException(
					sprintf('iCloud API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
				);
			}
		}
	}

	/**
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws UnexpectedException
	 * @throws AccessDeniedException
	 */
	public function importSectionEvents(SectionConnection $sectionConnection): void
	{
		$connection = $sectionConnection->getConnection();

		if (!$connection)
		{
			return;
		}

		$ownerId = (int)$connection->getOwner()?->getId();
		$sectionId = (int)$sectionConnection->getSection()?->getId();

		$this->configureLoggerContext($ownerId, $sectionId);

		$gateway = $this->gatewayProvider->getEventGateway($ownerId, $connection->getServer());

		$events = $gateway->getEvents($sectionConnection);

		try
		{
			$this->importProcessor->import($sectionConnection, $events);
		}
		catch (SystemException $e)
		{
			throw new SynchronizerException(
				sprintf('An import section events exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}

		$sectionConnection
			->setSyncToken($events->nextSyncToken)
			->setVersionId($events->etag)
			->setLastSyncStatus(Dictionary::SYNC_SECTION_ACTION['success'])
			->setLastSyncDate(new Date())
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	/**
	 * @throws ArgumentException
	 * @throws ConfigurationException
	 * @throws MessageSendingException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function exportEvents(int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$sectionIds = $this->getExportingSectionsIds($connection);

		if (empty($sectionIds))
		{
			return;
		}

		$this->configureLoggerContext($userId);

		/** @noinspection PhpUnhandledExceptionInspection */
		$events = $this->eventRepository->getExportingEventsBySectionIds($sectionIds, $userId, $connection->getId());

		foreach ($events as $event)
		{
			if ($event->isDeleted())
			{
				$this->sender->sendDeletedMessage($event);

				continue;
			}

			$this->sender->sendUpdatedMessageForVendor($event, self::VENDOR_CODE);
		}
	}

	private function getExportingSectionsIds(Connection $connection): array
	{
		$sectionsIds = [];

		$sectionsConnections = $this->sectionConnectionRepository->getByConnection((int)$connection->getId());

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionsConnections as $sectionConnection)
		{
			$section = $sectionConnection->getSection();

			if ($section?->getId() && $section?->isActive())
			{
				$sectionsIds[] = $section?->getId();
			}
		}

		return $sectionsIds;
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
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
				isRecoverable: false,
			);
		}

		$this->configureLoggerContext($ownerId, $event->getId());

		$gateway = $this->gatewayProvider->getEventGateway($ownerId, $connection->getServer());

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
	 * @throws SynchronizerException
	 */
	private function createEvent(
		Event $event,
		Connection $connection,
		SectionConnection $sectionConnection,
		ICloudEventGateway $gateway,
	): void
	{
		$eventConnection =
			(new EventConnection())
				->setEvent($event)
				->setConnection($connection)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['create'])
		;

		try
		{
			$eventResponse = $gateway->createEvent($event, $sectionConnection->getVendorSectionId());

			$eventConnection
				->setRetryCount()
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
				sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
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
				->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
				->setLastSyncDate(new Date())
			;

			$this->sectionConnectionRepository->save($sectionConnection);

			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
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
			$this->saveInstancesConnections($event, $connection, $eventResponse);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	private function updateEvent(
		Event $event,
		EventConnection $eventConnection,
		Connection $connection,
		SectionConnection $sectionConnection,
		ICloudEventGateway $gateway,
	): void
	{
		if ($eventConnection->getVersion() === $event->getVersion())
		{
			$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success']);

			$this->eventConnectionRepository->save($eventConnection);

			return;
		}

		$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		try
		{
			if ($eventConnection->getVendorEventId())
			{
				$eventResponse = $gateway->updateEvent(
					$event,
					$eventConnection,
					$sectionConnection->getVendorSectionId(),
				);
			}
			else
			{
				// EventConnection may have an empty value of vendorEventId because in legacy logic
				// it creates in case of deleted section on the vendor's side.
				// See ICloudEventSynchronizer::createEvent logic
				$eventResponse = $gateway->createEvent($event, $sectionConnection->getVendorSectionId());
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
				sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
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
				->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
				->setLastSyncDate(new Date())
			;

			$this->sectionConnectionRepository->save($sectionConnection);

			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
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
			$this->saveInstancesConnections($event, $connection, $eventResponse);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function deleteEvent(string $vendorEventId, string $vendorSectionId, int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$this->configureLoggerContext($userId, $vendorEventId);

		$gateway = $this->gatewayProvider->getEventGateway($userId, $connection->getServer());

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
				sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (NotFoundException $e)
		{
			// If the section was deleted on the vendor side
			$sectionConnection = $this->sectionConnectionRepository->getOneByVendorSectionId($vendorSectionId);

			if ($sectionConnection)
			{
				$sectionConnection
					->setActive(false)
					->setSyncToken(null)
					->setPageToken(null)
					->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
					->setLastSyncDate(new Date())
				;

				$this->sectionConnectionRepository->save($sectionConnection);
			}

			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	public function sendInstance(Event $event): void
	{
		// TODO: Code duplication
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

		$sectionConnection = $this->getSectionConnection(
			(int)$event->getSection()->getId(),
			(int)$connection->getId(),
		);

		if (!$sectionConnection)
		{
			throw new SynchronizerException(
				sprintf('Section connection for event "%s" not found', $event->getSection()->getId()),
				isRecoverable: false,
			);
		}

		$this->configureLoggerContext($ownerId, $event->getId());

		$gateway = $this->gatewayProvider->getEventGateway($ownerId, $connection->getServer());

		$eventConnection = $this->getEventConnection((int)$event->getId(), (int)$connection->getId());

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

			$this->updateInstance($event, $eventConnection, $connection, $sectionConnection, $gateway);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		if (!$sectionConnection->isActive())
		{
			return;
		}

		$this->createInstance($event, $connection, $sectionConnection, $gateway);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	private function createInstance(
		Event $event,
		Connection $connection,
		SectionConnection $sectionConnection,
		ICloudEventGateway $gateway,
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

		$masterEvent = $masterEventConnection->getEvent();

		$eventConnection =
			(new EventConnection())
				->setEvent($event)
				->setConnection($connection)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['create'])
		;

		try
		{
			$eventResponse = $gateway->updateEvent(
				$masterEvent,
				$masterEventConnection,
				$sectionConnection->getVendorSectionId(),
			);

			$masterEventConnection
				->setVendorEventId($eventResponse->id)
				->setEntityTag($eventResponse->etag)
				->setVendorVersionId($eventResponse->getVersion())
				->setVersion($masterEvent->getVersion())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			;

			$eventConnection
				->setRetryCount()
				->setVendorEventId($eventResponse->id)
				->setRecurrenceId($eventResponse->id)
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
				sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
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
				->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
				->setLastSyncDate(new Date())
			;

			$this->sectionConnectionRepository->save($sectionConnection);

			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($masterEventConnection);

			$this->eventConnectionRepository->save($eventConnection);
		}

		$this->saveInstancesConnections($masterEvent, $connection, $eventResponse);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	private function updateInstance(
		Event $event,
		EventConnection $eventConnection,
		Connection $connection,
		SectionConnection $sectionConnection,
		ICloudEventGateway $gateway,
	): void
	{
		$masterEventConnection = $this->getMasterEventConnection($event, $connection);
		$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		$masterEvent = $masterEventConnection->getEvent();

		$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		try
		{
			$eventResponse = $gateway->updateEvent(
				$masterEvent,
				$masterEventConnection,
				$sectionConnection->getVendorSectionId(),
			);

			$masterEventConnection
				->setVendorEventId($eventResponse->id)
				->setEntityTag($eventResponse->etag)
				->setVendorVersionId($eventResponse->getVersion())
				->setVersion($masterEventConnection->getEvent()->getVersion())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			;

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
				sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
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
				->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
				->setLastSyncDate(new Date())
			;

			$this->sectionConnectionRepository->save($sectionConnection);

			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($masterEventConnection);

			$this->eventConnectionRepository->save($eventConnection);
		}

		$this->saveInstancesConnections($masterEvent, $connection, $eventResponse);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function deleteInstance(Event $masterEvent): void
	{
		$ownerId = (int)$masterEvent->getOwner()?->getId();

		if ($ownerId === 0)
		{
			throw new ArgumentException('Event has no owner');
		}

		$connection = $this->getUserConnection($ownerId);

		if (!$connection)
		{
			return;
		}

		$masterSectionConnection = $this->sectionConnectionRepository->findOneBySectionAndConnectionId(
			$masterEvent->getSection()->getId(),
			$connection->getId(),
		);

		if (!$masterSectionConnection)
		{
			throw new SynchronizerException(
				sprintf('Section connection for event "%s" not found', $masterEvent->getSection()->getId()),
				isRecoverable: false,
			);
		}

		$masterEventConnection = $this->getEventConnection($masterEvent->getId(), $connection->getId());

		if (!$masterEventConnection)
		{
			throw new SynchronizerException(
				message: sprintf('Event connection for event "%s" not found', $masterEvent->getId()),
				isRecoverable: false,
			);
		}

		$this->configureLoggerContext($ownerId, $masterEvent->getId());

		$gateway = $this->gatewayProvider->getEventGateway($ownerId, $connection->getServer());

		$masterEventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['update']);

		try
		{
			$eventResponse = $gateway->updateEvent(
				$masterEvent,
				$masterEventConnection,
				$masterSectionConnection->getVendorSectionId(),
			);

			$masterEventConnection
				->setVendorEventId($eventResponse->id)
				->setEntityTag($eventResponse->etag)
				->setVendorVersionId($eventResponse->getVersion())
				->setVersion($masterEventConnection->getEvent()->getVersion())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			;

			$this->connectionManager->updateConnection($connection);
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('iCloud authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (NotFoundException $e)
		{
			// If the section was deleted on the vendor side
			$sectionConnection = $this->getSectionConnection($masterEvent->getSection()->getId(), $connection->getId());

			if ($sectionConnection)
			{
				$sectionConnection
					->setActive(false)
					->setSyncToken(null)
					->setPageToken(null)
					->setLastSyncStatus(Dictionary::SYNC_STATUS['deleted'])
					->setLastSyncDate(new Date())
				;

				$this->sectionConnectionRepository->save($sectionConnection);
			}

			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		catch (BadRequestException|AccessDeniedException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->eventConnectionRepository->save($masterEventConnection);
		}

		$this->saveInstancesConnections($masterEvent, $connection, $eventResponse);
	}

	/**
	 * @throws ArgumentException
	 * @throws SynchronizerException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	private function saveInstancesConnections(
		Event $masterEvent,
		Connection $connection,
		EventResponse $masterEventResponse,
	): void
	{
		try
		{
			$instances = $this->eventRepository->getEventInstances($masterEvent);
		}
		catch (RepositoryReadException $e)
		{
			$exceptionMessage = sprintf(
				'Unable to get event instances for event "%s": "%s"',
				$masterEvent->getId(),
				$e->getMessage(),
			);

			throw new SynchronizerException(
				$exceptionMessage,
				$e->getCode(),
				$e,
			);
		}

		if (empty($instances))
		{
			return;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$existingInstancesConnections = $this->eventConnectionRepository->getByRecurrenceAndConnectionId(
			$masterEventResponse->id,
			(int)$connection->getId(),
		);

		$instancesConnections = [];

		foreach ($existingInstancesConnections as $instanceConnection)
		{
			$eventId = (int)$instanceConnection->getEvent()->getId();

			$instancesConnections[$eventId] = $instanceConnection;
		}

		foreach ($instances as $instance)
		{
			$eventId = (int)$instance->getId();

			$instanceConnection =
				$instancesConnections[$eventId] ?? (new EventConnection())
					->setEvent($instance)
					->setConnection($connection)
					->setVendorEventId($masterEventResponse->id)
					->setRecurrenceId($masterEventResponse->id)
			;

			if ($instanceConnection->getVendorVersionId() === $masterEventResponse->getVersion())
			{
				unset($instancesConnections[$eventId]);

				continue;
			}

			$instanceConnection
				->setEntityTag($masterEventResponse->etag)
				->setVendorVersionId($masterEventResponse->getVersion())
				->setVersion($instance->getVersion())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			;

			$instancesConnections[$eventId] = $instanceConnection;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->eventConnectionRepository->saveAll($instancesConnections);
	}

	/**
	 * @throws SynchronizerException
	 * @throws LogicException
	 */
	private function getMasterEventConnection(Event $event, Connection $connection): EventConnection
	{
		try
		{
			$masterEvent = $this->eventRepository->getMasterEvent($event);
		}
		catch (RepositoryReadException $e)
		{
			throw new SynchronizerException(
				sprintf('Unable to get master event for event "%s": "%s"', $event->getId(), $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}

		if (!$masterEvent)
		{
			throw new LogicException(sprintf('Master event for the event "%s" not found', $event->getId()));
		}

		$masterEventConnection = $this->getEventConnection($masterEvent->getId(), $connection->getId());

		if (!$masterEventConnection)
		{
			throw new SynchronizerException(
				sprintf('The master event "%s" has no connection with iCloud', $masterEvent->getId()),
				code: self::MASTER_EVENT_NO_EVENT_CONNECTION_EXCEPTION,
			);
		}

		return $masterEventConnection;
	}
}
