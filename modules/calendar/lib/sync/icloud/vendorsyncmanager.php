<?php
	
namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromArray;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Managers;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Calendar\Sync\Managers\NotificationManager;

class VendorSyncManager
{
	private const STATUS_ERROR = 'error';
	private const STATUS_SUCCESS = 'success';
	/** @var Helper $helper */
	protected Helper $helper;
	/** @var ?string $error */
	protected ?string $error = null;
	/** @var ?Context $context */
	protected ?Context $context = null;
	/** @var ?VendorSyncService $syncService */
	protected ?VendorSyncService $syncService = null;
	/** @var Core\Mappers\Factory */
	private Core\Mappers\Factory $mapperFactory;
	
	public function __construct()
	{
		$this->helper = new Helper();
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @param int $connectionId
	 *
	 * @return string[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function syncIcloudConnection(int $connectionId): array
	{
		$userId = \CCalendar::GetUserId();
		$connection = $this->mapperFactory->getConnection()->getById($connectionId);

		if (!$connection)
		{
			return [
				'status' => self::STATUS_ERROR,
				'message' => 'Connection not found',
			];
		}

		if ($connection->getOwner()->getId() !== $userId)
		{
			return [
				'status' => self::STATUS_ERROR,
				'message' => 'Access Denied',
			];
		}

		$result = Managers\DataSyncManager::createInstance()->dataSync($userId);
		if (!$result)
		{
			return [
				'status' => self::STATUS_ERROR,
				'message' => 'Error while trying to import events',
			];
		}

		Util::addPullEvent(
			'process_sync_connection',
			$userId,
			[
				'vendorName' => $this->helper::ACCOUNT_TYPE,
				'stage' => 'import_finished',
				'accountName' => $connection->getServer()->getUserName(),
			]
		);

		$result = (new Managers\OutgoingManager($connection))->export();
		if (!$result->isSuccess())
		{
			return [
				'status' => self::STATUS_ERROR,
				'message' => 'Error while trying to export events',
			];
		}

		Util::addPullEvent(
			'process_sync_connection',
			$userId,
			[
				'vendorName' => $this->helper::ACCOUNT_TYPE,
				'stage' => 'export_finished',
				'accountName' => $connection->getServer()->getUserName(),
			]
		);

		NotificationManager::addFinishedSyncNotificationAgent(
			$userId,
			$this->helper::ACCOUNT_TYPE
		);

		return [
			'status' => self::STATUS_SUCCESS
		];
	}

	/**
	 * @param array $connectionRaw
	 *
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function initConnection(array $connectionRaw): ?int
	{
		$connection = null;
		$calendarPath = $this->getSyncService()->getCalendarServerPath($connectionRaw);
		if (!$calendarPath)
		{
			$this->error = 'Error while trying to get calendars path';

			return null;
		}
		$owner = Core\Role\Helper::getRole(\CCalendar::GetUserId(), Core\Role\User::TYPE);
		$connectionManager = new Managers\ConnectionManager();
		$connections = $connectionManager->getConnectionsData($owner, [Helper::ACCOUNT_TYPE]);
		foreach ($connections as $con)
		{
			$existPath = $con->getServerScheme()
				. '://'
				. $con->getServerHost()
				. ':'
				. $con->getServerPort()
				. $con->getServerPath()
			;
			if ($existPath === $calendarPath)
			{
				$connection = (new BuilderConnectionFromDM($con))->build();
				break;
			}
		}

		if ($connection)
		{
			if ($connection->isDeleted())
			{
				$connection->setDeleted(false);
				$connection->getServer()->setPassword($connectionRaw['SERVER_PASSWORD']);
			}

			$connectionManager->update($connection);
			return $connection->getId();
		}

		return $this->addConnection($connectionRaw, $calendarPath);
	}

	/**
	 * @param array $connection
	 * @param string $calendarPath
	 *
	 * @return int|null
	 */
	public function addConnection(array $connection, string $calendarPath): ?int
	{
		$connection['SERVER_HOST'] = $calendarPath;
		$fields = [
			'ENTITY_TYPE' => $connection['ENTITY_TYPE'],
			'ENTITY_ID' => $connection['ENTITY_ID'],
			'ACCOUNT_TYPE' => $this->helper::ACCOUNT_TYPE,
			'NAME' => $connection['NAME'],
			'SERVER' => $connection['SERVER_HOST'],
			'SERVER_USERNAME' => $connection['SERVER_USERNAME'],
			'SERVER_PASSWORD' => $connection['SERVER_PASSWORD']
		];

		$connectionId = \CDavConnection::Add($fields);

		if ($connectionId)
		{
			return $connectionId;
		}

		$this->error = 'Error while trying to save connection';

		return null;
	}

	private function getSyncService(): VendorSyncService
	{
		if (!$this->syncService)
		{
			$this->syncService = new VendorSyncService();
		}

		return $this->syncService;
	}
	
	public function getError(): string
	{
		return $this->error;
	}
}
