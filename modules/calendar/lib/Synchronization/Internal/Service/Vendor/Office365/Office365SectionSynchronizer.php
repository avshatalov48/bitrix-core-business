<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\CreateConflictedSectionTrait;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AuthorizationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\CalendarResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway\Office365SectionGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor\SectionImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\SectionSynchronizerInterface;
use Bitrix\Main\Repository\Exception\PersistenceException;

class Office365SectionSynchronizer extends AbstractOffice365Synchronizer implements SectionSynchronizerInterface
{
	use CreateConflictedSectionTrait {
		createSection as private createSectionWithConflictResolution;
	}

	public function __construct(
		private readonly Office365GatewayProvider $gatewayProvider,
		private readonly SectionImportProcessor $importProcessor,
		ConnectionProvider $connectionProvider,
		ConnectionManager $connectionManager,
		SectionConnectionRepository $sectionConnectionRepository,
		RequestLogger $logger,
	)
	{
		parent::__construct($connectionProvider, $connectionManager, $sectionConnectionRepository, $logger);
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function sendSection(Section $section): void
	{
		$connection = $this->getUserConnection($section->getOwner()->getId());

		if (!$connection)
		{
			return;
		}

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		$sectionConnection = $this->getSectionConnection($section->getId(), $connection->getId());

		if ($sectionConnection)
		{
			$this->updateSection($sectionConnection, $connection, $gateway);

			$this->connectionManager->updateConnection($connection);

			return;
		}

		$this->createSection($section, $connection, $gateway);

		$this->connectionManager->updateConnection($connection);
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	private function createSection(Section $section, Connection $connection, Office365SectionGateway $gateway): void
	{
		$sectionConnection =
			(new SectionConnection())
				->setSection($section)
				->setConnection($connection)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['create'])
				->setLastSyncDate(new Date())
		;

		try
		{
			/** @var CalendarResponse $calendarResponse */
			$calendarResponse = $this->createSectionWithConflictResolution($section, $gateway);

			$sectionConnection
				->setVendorSectionId($calendarResponse->id)
				->setVersionId($calendarResponse->changeKey)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setLastSyncDate(new Date())
			;
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office 365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Office 365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->sectionConnectionRepository->save($sectionConnection);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	private function updateSection(
		SectionConnection $sectionConnection,
		Connection $connection,
		Office365SectionGateway $gateway,
	): void
	{
		if (!$sectionConnection->isActive())
		{
			return;
		}

		$sectionConnection
			->setLastSyncStatus(Dictionary::SYNC_STATUS['update'])
			->setLastSyncDate(new Date())
		;

		try
		{
			$calendarResponse = $gateway->updateSection($sectionConnection);

			$sectionConnection
				->setVendorSectionId($calendarResponse->id)
				->setVersionId($calendarResponse->changeKey)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setLastSyncDate(new Date())
			;
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office 365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (NotFoundException)
		{
			$sectionConnection
				->setActive(false)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['inactive'])
				->setLastSyncDate(new Date())
			;
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Office 365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
		finally
		{
			$this->sectionConnectionRepository->save($sectionConnection);
		}
	}

	/**
	 * @throws SynchronizerException
	 */
	public function deleteSection(string $vendorSectionId, int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return;
		}

		try
		{
			$gateway->deleteSection($vendorSectionId);

			$this->connectionManager->updateConnection($connection);
		}
		catch (NotAuthorizedException $e)
		{
			$this->handleUnauthorizedException($connection);

			throw new SynchronizerException(
				sprintf('Office 365 authorization exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
				isRecoverable: false,
			);
		}
		catch (GoneException|NotFoundException)
		{
			return;
		}
		catch (ApiException $e)
		{
			throw new SynchronizerException(
				sprintf('Office 365 API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function importSections(int $userId, ?string $token = null): array
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return [];
		}

		$gateway = $this->getUserGateway($connection);

		if (!$gateway)
		{
			return [];
		}

		try
		{
			$calendars = $gateway->getCalendarList();
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Unable to get calendars from Office 365: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}

		$this->importProcessor->import($connection, $calendars);

		$connection->setStatus('[200] OK');

		$this->connectionManager->updateConnection($connection);

		return [];
	}

	private function getUserGateway(Connection $connection): ?Office365SectionGateway
	{
		try
		{
			return $this->gatewayProvider->getSectionGateway($connection->getOwner()->getId());
		}
		catch (AuthorizationException)
		{
			$this->handleAuthorizationException($connection);
		}

		return null;
	}
}
