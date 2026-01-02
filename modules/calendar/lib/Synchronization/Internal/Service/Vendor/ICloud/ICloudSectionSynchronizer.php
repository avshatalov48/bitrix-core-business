<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Internal\Repository\SectionRepository;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Gateway\ICloudSectionGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\SectionImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\SectionSynchronizerInterface;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class ICloudSectionSynchronizer extends AbstractICloudSynchronizer implements SectionSynchronizerInterface
{
	public function __construct(
		private readonly EventConnectionRepository $eventConnectionRepository,
		private readonly SectionImportProcessor $importProcessor,
		private readonly SectionRepository $sectionRepository,
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
	 * @throws ArgumentException
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

		$this->configureLoggerContext($userId, $connection->getId());

		$gateway = $this->gatewayProvider->getSectionGateway($userId, $connection->getServer());

		try
		{
			$calendars = $gateway->getSections();
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
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}

		if (empty($calendars->getItems()))
		{
			$connection
				->setStatus('[204] No Content')
				->setLastSyncTime(new Date())
			;

			$this->connectionManager->updateConnection($connection);

			return [];
		}

		$this->importProcessor->import($connection, $calendars);

		$connection
			->setStatus('[200] OK')
			->setLastSyncTime(new Date())
		;

		$this->connectionManager->updateConnection($connection);

		return [];
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function exportSections(int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$sections = $this->sectionRepository->getExportingSections($connection);

		foreach ($sections as $section)
		{
			$this->exportSection($section, $connection);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	private function exportSection(
		Section $section,
		Connection $connection,
	): void
	{
		$ownerId = (int)$section->getOwner()?->getId();

		if ($ownerId === 0)
		{
			throw new ArgumentException('Section has no owner');
		}

		$this->configureLoggerContext($ownerId, $section->getId());

		$gateway = $this->gatewayProvider->getSectionGateway($ownerId, $connection->getServer());

		$sectionConnection = $this->getSectionConnection($section->getId(), $connection->getId());

		if ($sectionConnection)
		{
			$sectionConnection
				->setLastSyncStatus(Dictionary::SYNC_STATUS['update'])
				->setLastSyncDate(new Date())
			;

			try
			{
				$calendarResponse = $gateway->updateSection($section, $sectionConnection->getVendorSectionId());

				$sectionConnection
					->setVendorSectionId($calendarResponse->id)
					->setVersionId($calendarResponse->etag)
					->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
					->setLastSyncDate(new Date())
				;

				$this->sectionConnectionRepository->save($sectionConnection);

				return;
			}
			catch (NotFoundException)
			{
				$this->eventConnectionRepository->deleteBrokenEventConnections($section->getId(), $connection->getId());

				$this->sectionConnectionRepository->delete($sectionConnection->getId());
			}
			catch (ApiException|DtoValidationException $e)
			{
				$this->sectionConnectionRepository->save($sectionConnection);

				throw new SynchronizerException(
					sprintf('iCloud API exception: "%s"', $e->getMessage()),
					$e->getCode(),
					$e,
				);
			}
		}

		$this->createSection($section, $connection, $gateway);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	public function sendSection(Section $section): void
	{
		$ownerId = (int)$section->getOwner()?->getId();

		if ($ownerId === 0)
		{
			throw new ArgumentException('Section has no owner');
		}

		$connection = $this->getUserConnection($ownerId);

		if (!$connection)
		{
			return;
		}

		$this->configureLoggerContext($ownerId, $section->getId());

		$gateway = $this->gatewayProvider->getSectionGateway($ownerId, $connection->getServer());

		$sectionConnection = $this->getSectionConnection($section->getId(), $connection->getId());

		if ($sectionConnection)
		{
			$this->updateSection($section, $sectionConnection, $connection, $gateway);

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
	private function createSection(
		Section $section,
		Connection $connection,
		ICloudSectionGateway $gateway,
	): void
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
			$calendarResponse = $gateway->createSection($section);

			$sectionConnection
				->setVendorSectionId($calendarResponse->id)
				->setVersionId($calendarResponse->etag)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setLastSyncDate(new Date())
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
			$this->sectionConnectionRepository->save($sectionConnection);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 */
	private function updateSection(
		Section $section,
		SectionConnection $sectionConnection,
		Connection $connection,
		ICloudSectionGateway $gateway,
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
			$calendarResponse = $gateway->updateSection($section, $sectionConnection->getVendorSectionId());

			$sectionConnection
				->setVendorSectionId($calendarResponse->id)
				->setVersionId($calendarResponse->etag)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setLastSyncDate(new Date())
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
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
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

		$this->configureLoggerContext($userId, $vendorSectionId);

		$gateway = $this->gatewayProvider->getSectionGateway($userId, $connection->getServer());

		try
		{
			$gateway->deleteSection($vendorSectionId);

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
		catch (ApiException $e)
		{
			throw new SynchronizerException(
				sprintf('iCloud API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e,
			);
		}
	}
}
