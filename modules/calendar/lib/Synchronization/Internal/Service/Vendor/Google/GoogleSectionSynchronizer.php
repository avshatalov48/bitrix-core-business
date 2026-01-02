<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AccessDeniedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\BadRequestException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\RateLimitExceededException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\ConnectionManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushStorageManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\CreateConflictedSectionTrait;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\Google\SyncTokenNotValidException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\CalendarListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\CalendarResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Gateway\GoogleSectionGateway;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Processor\SectionImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\SectionSynchronizerInterface;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;
use Psr\Container\NotFoundExceptionInterface;

class GoogleSectionSynchronizer extends AbstractGoogleSynchronizer implements SectionSynchronizerInterface
{
	use CreateConflictedSectionTrait;

	private ?Push $lockedPush = null;

	public function __construct(
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly EventConnectionRepository $eventConnectionRepository,
		private readonly PushStorageManager $pushStorageManager,
		private readonly  GoogleGatewayProvider $getawayProvider,
		ConnectionProvider $connectionProvider,
		ConnectionManager $connectionManager,
		RequestLogger $logger,
	)
	{
		parent::__construct($connectionProvider, $connectionManager, $logger);
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws PushException
	 */
	public function sendSection(Section $section): void
	{
		$connection = $this->getUserConnection($section->getOwner()->getId());

		if (!$connection)
		{
			return;
		}

		$gateway = $this->getUserGateway($section->getOwner()->getId(), $connection);

		$this->lockPush($connection->getId());

		try
		{
			$sectionConnection = $this->getSectionLink($section, $connection->getId());

			if ($sectionConnection)
			{
				$calendarResponse = $gateway->updateSection($section, $sectionConnection->getVendorSectionId());

				$sectionConnection
					->setVendorSectionId($calendarResponse->getId())
					->setVersionId($calendarResponse->getEtag())
					->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
					->setLastSyncDate(new Date())
				;

				$this->sectionConnectionRepository->save($sectionConnection);

				$this->connectionManager->updateConnection($connection);

				return;
			}

			/** @var CalendarResponse $calendarResponse */
			$calendarResponse = $this->createSection($section, $gateway);

			$this->sectionConnectionRepository->save(
				$this->createSectionConnection($section, $connection, $calendarResponse)
			);

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
			$this->unlockPush();
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws SynchronizerException
	 * @throws SystemException
	 */
	private function exportSection(Section $section, Connection $connection, array $excludedVendorIds = []): void
	{
		$gateway = $this->getUserGateway($section->getOwner()->getId(), $connection);

		$sectionConnection = $this->getSectionLink($section, $connection->getId());

		// If the section on the Google side was deleted or hidden
		if ($sectionConnection && !in_array($sectionConnection->getVendorSectionId(), $excludedVendorIds, true))
		{
			$this->eventConnectionRepository->deleteBrokenEventConnections($section->getId(), $connection->getId());
			$this->sectionConnectionRepository->delete($sectionConnection->getId());

			$sectionConnection = null;
		}

		try
		{
			if ($sectionConnection)
			{
				$gateway->updateSection($section, $sectionConnection->getVendorSectionId());

				return;
			}
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
		catch (NotFoundException)
		{
			$this->eventConnectionRepository->deleteBrokenEventConnections($section->getId(), $connection->getId());
			$this->sectionConnectionRepository->delete($sectionConnection->getId());
		}
		catch (ApiException|DtoValidationException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		try
		{
			/** @var CalendarResponse $calendarResponse */
			$calendarResponse = $this->createSection($section, $gateway);
		}
		catch (DtoValidationException|NotAuthorizedException|UnexpectedException $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		$this->sectionConnectionRepository->save(
			$this->createSectionConnection($section, $connection, $calendarResponse)
		);
	}

	private function createSectionConnection(
		Section $section,
		Connection $connection,
		CalendarResponse $calendarResponse
	): SectionConnection
	{
		return (new SectionConnection())
			->setSection($section)
			->setConnection($connection)
			->setVendorSectionId($calendarResponse->getId())
			->setVersionId($calendarResponse->getEtag())
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			->setLastSyncDate(new Date())
		;
	}

	/**
	 * @throws SynchronizerException
	 * @throws PushException
	 */
	public function deleteSection(string $vendorSectionId, int $userId): void
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return;
		}

		$gateway = $this->getUserGateway($userId, $connection);

		$this->lockPush($connection->getId());

		try
		{
			$gateway->deleteSection($vendorSectionId);

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
		finally
		{
			$this->unlockPush();
		}
	}

	private function getSectionLink(Section $section, int $connectionId): ?SectionConnection
	{
		return $this->sectionConnectionRepository->findOneBySectionAndConnectionId($section->getId(), $connectionId);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 * @throws RepositoryReadException
	 * @throws SynchronizerException
	 * @throws ObjectNotFoundException
	 * @throws NotFoundExceptionInterface
	 */
	public function importSections(int $userId, ?string $token = null): array
	{
		$connection = $this->getUserConnection($userId);

		if (!$connection)
		{
			return [];
		}

		$gateway = $this->getUserGateway($userId, $connection);

		try
		{
			$calendarList = $this->getCalendarList($gateway, $token);
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
		catch (ApiException|DtoValidationException|ArgumentException|Exception $e)
		{
			throw new SynchronizerException(
				sprintf('Google API exception: "%s"', $e->getMessage()),
				$e->getCode(),
				$e
			);
		}

		$processor = ServiceLocator::getInstance()->get(SectionImportProcessor::class);

		if ($token)
		{
			$processor->partialImport($connection, $calendarList);
		}
		else
		{
			$processor->fullImport($connection, $calendarList);
		}

		$connection
			->setToken($calendarList->nextSyncToken)
			->setStatus('[200] OK')
		;

		$this->connectionManager->updateConnection($connection);

		return $calendarList->getItemsIds();
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function exportSections(int $userId, Connection $connection, array $excludedVendorIds = []): void
	{
		// @todo No repository yet...
		/** @see \Bitrix\Calendar\Internal\Repository\SectionRepository::getExportingSections */
		$ormSections = SectionTable::query()
			->setSelect([
				'ID',
				'NAME',
				'XML_ID',
				'ACTIVE',
				'DESCRIPTION',
				'COLOR',
				'CAL_TYPE',
				'OWNER_ID',
				'EXTERNAL_TYPE',
			])
			->where('OWNER_ID', $userId)
			->where('EXTERNAL_TYPE', \Bitrix\Calendar\Core\Mappers\Section::SECTION_TYPE_LOCAL)
			->where('CAL_TYPE', User::TYPE)
			// @todo Why select not active sections?
			->exec()
		;

		while ($ormSection = $ormSections->fetchObject())
		{
			$section = (new SectionBuilderFromDataManager($ormSection))->build();

			$this->exportSection($section, $connection, $excludedVendorIds);
		}
	}

	/**
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws Exception
	 * @throws NotAuthorizedException
	 */
	private function getCalendarList(GoogleSectionGateway $gateway, ?string $token = null): CalendarListResponse
	{
		try
		{
			return $gateway->getSections($token);
		}
		catch (SyncTokenNotValidException $e)
		{
			if ($token)
			{
				// @todo Сайд эффект
				return $this->getCalendarList($gateway);
			}

			throw $e;
		}
	}

	/**
	 * @throws SynchronizerException
	 */
	private function getUserGateway(int $userId, Connection $connection): GoogleSectionGateway
	{
		try
		{
			$gateway = $this->getawayProvider->getSectionGateway($userId);
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
	 */
	private function lockPush(int $connectionId): bool
	{
		try
		{
			$push = $this->pushStorageManager->getConnectionPush($connectionId);

			if (!$push)
			{
				return true;
			}

			if ($this->pushStorageManager->setBlockPush($push))
			{
				$this->lockedPush = $push;

				return true;
			}
		}
		catch (PushException $e)
		{
			throw new SynchronizerException(
				'Unable to lock push for connection ' . $connectionId,
				previous: $e
			);
		}

		throw new SynchronizerException('Unable to lock push for connection ' . $connectionId);
	}

	/**
	 * @throws PushException
	 */
	private function unlockPush(): void
	{
		$this->pushStorageManager->setUnblockPush($this->lockedPush);

		$this->lockedPush = null;
	}
}
