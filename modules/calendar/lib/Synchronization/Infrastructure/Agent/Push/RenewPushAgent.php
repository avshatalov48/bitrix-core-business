<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Infrastructure\Agent\Push;

use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\EntityType;
use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Model\PushTable;
use Bitrix\Calendar\Synchronization\Internal\Model\SectionConnectionTable;
use Bitrix\Calendar\Synchronization\Internal\Repository\PushRepository;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushManagerInterface;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushStorageManager;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Exception;

class RenewPushAgent
{
	private const RENEW_LIMIT = 5;
	private const FIX_LIMIT = 5;

	private const RESULT_STATUS = [
		'done' => 'done', // nothing left to process
		'next' => 'next', // something left to process
	];

	private PushRepository $pushRepository;
	private PushStorageManager $pushStorageManager;
	private SectionConnectionRepository $sectionConnectionRepository;
	private ConnectionProvider $connectionProvider;

	public function __construct()
	{
		$locator = ServiceLocator::getInstance();

		$this->pushRepository = $locator->get(PushRepository::class);
		$this->pushStorageManager = $locator->get(PushStorageManager::class);
		$this->sectionConnectionRepository = $locator->get(SectionConnectionRepository::class);
		$this->connectionProvider = $locator->get(ConnectionProvider::class);
	}

	public static function runAgent(): string
	{
//		$agentName = __METHOD__ . '();';

//		if (!Loader::includeModule('dav') || !Loader::includeModule('calendar'))
//		{
//			return $agentName;
//		}

		$agentInstance = new self();

		$status = $agentInstance->renewUpdateNeededPushes();

		$agentInstance->fixWatchSectionChannels();
		$agentInstance->fixConnectionPushes();

		return $status;
//		return $agentName;
	}

	private function renewUpdateNeededPushes(): string
	{
		$pushes = $this->pushRepository->getUpdateNeededCollection(
			[EntityType::Connection->value, EntityType::SectionConnection->value],
			self::RENEW_LIMIT
		);

		/** @var Push $push */
		foreach ($pushes as $push)
		{
			if ($push->getEntityType() === EntityType::SectionConnection->value)
			{
				$this->renewSectionPush($push);
			}
			elseif ($push->getEntityType() === EntityType::Connection->value)
			{
				$this->renewConnectionPush($push);
			}
		}

		if ($pushes->count() < self::RENEW_LIMIT)
		{
			return self::RESULT_STATUS['done'];
		}

		return self::RESULT_STATUS['next'];
	}

	private function renewSectionPush(Push $push): void
	{
		$sectionConnection = $this->sectionConnectionRepository->getById($push->getEntityId());

		if ($this->isSectionConnectionActive($sectionConnection))
		{
			if ($vendorPushManager = $this->getVendorPushManager($sectionConnection->getConnection()))
			{
				try
				{
					$vendorPushManager->subscribeSection($sectionConnection);

					return;
				}
				catch (NotAuthorizedException)
				{
					return;
				}
				catch (Exception)
				{
				}
			}
		}

		$this->pushStorageManager->deletePush($push);
	}

	private function isSectionConnectionActive(?SectionConnection $sectionConnection): bool
	{
		if (!$sectionConnection || !$sectionConnection->isActive())
		{
			return false;
		}

		$connection = $sectionConnection->getConnection();

		return $connection !== null && !$connection->isDeleted() && $connection->getOwner() !== null;
	}

	private function renewConnectionPush(Push $push): void
	{
		$connection = $this->connectionProvider->getById($push->getEntityId());

		if ($connection !== null && !$connection->isDeleted() && $connection->getOwner() !== null)
		{
			if ($vendorPushManager = $this->getVendorPushManager($connection))
			{
				try
				{
					$vendorPushManager->subscribeConnection($connection);
				}
				catch (ApiException)
				{
				}
			}
		}

		$this->pushStorageManager->deletePush($push);
	}

	private function getVendorPushManager(Connection $connection): ?PushManagerInterface
	{
		$locator = ServiceLocator::getInstance();

		if ($connection->getAccountType() === Sync\Google\Factory::SERVICE_NAME)
		{
			return $locator->get(Service\Vendor\Google\Push\PushManager::class);
		}
		elseif ($connection->getAccountType() === Sync\Office365\Factory::SERVICE_NAME)
		{
			return $locator->get(Service\Vendor\Office365\Push\PushManager::class);
		}

		return null;
	}

	private function fixWatchSectionChannels(): void
	{
		try
		{
			$query = SectionConnectionTable::query()
				->setSelect([
					'ID',
					'CONNECTION_ID',
					'SECTION_ID',
					'ACTIVE',
					'LAST_SYNC_STATUS',
					'CONNECTION.IS_DELETED',
					'CONNECTION.ACCOUNT_TYPE',
					'PUSH.ENTITY_TYPE'
				])
				->registerRuntimeField(
					'PUSH',
					new ReferenceField(
						'PUSH',
						PushTable::getEntity(),
						[
							'=this.ID' => 'ref.ENTITY_ID',
							'ref.ENTITY_TYPE' => ['?', EntityType::SectionConnection->value]
						],
						['join_type' => Join::TYPE_LEFT]
					)
				)
				->where('ACTIVE', 'Y')
				->where('LAST_SYNC_STATUS', 'success')
				->where('CONNECTION.IS_DELETED', 'N')
				->whereIn(
					'CONNECTION.ACCOUNT_TYPE', [Sync\Google\Factory::SERVICE_NAME, Sync\Office365\Factory::SERVICE_NAME]
				)
				->whereNull('PUSH.ENTITY_TYPE')
				->setLimit(self::FIX_LIMIT)
				->exec()
			;
		}
		catch (SystemException)
		{
			return;
		}

		while ($row = $query->fetch())
		{
			$connection = $this->connectionProvider->getById((int)$row['CONNECTION_ID']);

			if ($connection === null || $connection->getOwner()?->getId() === null)
			{
				$this->sectionConnectionRepository->delete((int)$row['ID']);

				continue;
			}

			$sectionConnection = $this->sectionConnectionRepository->getById((int)$row['ID']);

			try
			{
				if ($sectionConnection !== null)
				{
					$this->getVendorPushManager($connection)->subscribeSection($sectionConnection);
				}
			}
			catch (Exception)
			{
				$sectionConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['failed']);

				$this->sectionConnectionRepository->save($sectionConnection);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private function fixConnectionPushes(): void
	{
		try
		{
			$query = DavConnectionTable::query()
				->setSelect([
					'ID',
					'IS_DELETED',
					'ACCOUNT_TYPE',
					'LAST_RESULT',
					'PUSH.ENTITY_TYPE',
				])
				->registerRuntimeField(
					'PUSH',
					new ReferenceField(
						'PUSH',
						PushTable::getEntity(),
						[
							'=this.ID' => 'ref.ENTITY_ID',
							'ref.ENTITY_TYPE' => ['?', EntityType::Connection->value]
						],
						['join_type' => Join::TYPE_LEFT]
					)
				)
				->where('IS_DELETED', 'N')
				->where('ACCOUNT_TYPE', Sync\Google\Factory::SERVICE_NAME)
				->whereIn('LAST_RESULT', ['success', '[200] OK'])
				->whereNull('PUSH.ENTITY_TYPE')
				->setLimit(self::FIX_LIMIT)
				->exec()
			;
		}
		catch (SystemException)
		{
			return;
		}

		while ($row = $query->fetch())
		{
			try
			{
				$connection = $this->connectionProvider->getById((int)$row['ID']);

				if ($connection === null || $connection->getOwner()?->getId() === null)
				{
					return;
				}

				$this->getVendorPushManager($connection)->subscribeConnection($connection);
			}
			catch (Exception $e)
			{
				DavConnectionTable::update($row['ID'], ['LAST_RESULT' => '[' . $e->getCode() . '] ERR']);
			}
		}
	}
}
