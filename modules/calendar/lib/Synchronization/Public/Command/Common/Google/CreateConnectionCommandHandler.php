<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Result;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Integration\Dav\Service\ConnectionManager as DavConnectionManager;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Google\Factory;
use Bitrix\Calendar\Sync\Google\Helper;
use Bitrix\Calendar\Sync\Handlers\MasterPushHandler;
use Bitrix\Calendar\Sync\Managers\NotificationManager;
use Bitrix\Calendar\Sync\Vendor\Vendor;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push\SubscribeToPushMessage;
use Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Queue;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\PushStorageManager;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleEventSynchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\GoogleSectionSynchronizer;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Messenger\Entity\ProcessingParam\ItemIdParam;
use Bitrix\Main\Messenger\Internals\Exception\Broker\SendFailedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class CreateConnectionCommandHandler
{
	/**
	 * @var Factory
	 */
	private mixed $mapperFactory;
	/**
	 * @var ConnectionProvider
	 */
	private mixed $connectionProvider;

	/**
	 * @var DavConnectionManager
	 */
	private mixed $davConnectionManager;
	/**
	 * @var GoogleSectionSynchronizer
	 */
	private mixed $sectionSynchronizer;
	/**
	 * @var GoogleEventSynchronizer
	 */
	private mixed $eventSynchronizer;

	private PushStorageManager $pushStorageManager;

	public function __construct(
	)
	{
		$locator = ServiceLocator::getInstance();

		$this->connectionProvider = $locator->get(ConnectionProvider::class);
		$this->davConnectionManager = $locator->get(DavConnectionManager::class);
		$this->sectionSynchronizer = $locator->get(GoogleSectionSynchronizer::class);
		$this->eventSynchronizer = $locator->get(GoogleEventSynchronizer::class);
		$this->pushStorageManager = $locator->get(PushStorageManager::class);

		// @todo Remove
		$this->mapperFactory = $locator->get('calendar.service.mappers.factory');
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ConfigurationException
	 * @throws Exception
	 * @throws LoaderException
	 * @throws NotAuthorizedException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SendFailedException
	 * @throws SqlQueryException
	 * @throws SystemException
	 */
	public function __invoke(CreateConnectionCommand $command): ?Connection
	{
		$userId = $command->userId;

		$owner = \Bitrix\Calendar\Core\Role\Helper::getRole($userId, User::TYPE);

		// Stage 1
		$connection = $this->createConnection($this->mapperFactory->getConnection(), $userId);
		$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[0]);

		$lockedPush = $this->pushStorageManager->blockConnectionPush($connection->getId());

		try
		{
			// Stage 2
			$importedVendorIds = $this->sectionSynchronizer->importSections($userId);
			$this->sectionSynchronizer->exportSections($userId, $connection, $importedVendorIds);

			$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[1]);

			// Stage 3
			$this->eventSynchronizer->importEvents($userId);
			$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[2]);

			$this->eventSynchronizer->exportEvents($userId);
			$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[3]);
		}
		finally
		{
			$this->pushStorageManager->setUnblockPush($lockedPush);
		}

		\CCalendar::ClearCache();

		NotificationManager::addFinishedSyncNotificationAgent($owner->getId(), 'google');

		$this->initSubscription($connection);

		return $connection;
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws Exception
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws SqlQueryException
	 * @throws ObjectException
	 */
	private function createConnection(\Bitrix\Calendar\Core\Mappers\Connection $mapper, int $userId): Connection
	{
		$owner = \Bitrix\Calendar\Core\Role\Helper::getUserRole($userId);

		$this->deactivateCurrentConnections($owner);

		$connection = new Connection();

		$connection
			->setVendor(
				new Vendor(
					[
						'SERVER_SCHEME' => Helper::HTTP_SCHEME_DEFAULT,
						'SERVER_HOST' => Helper::GOOGLE_API_URL,
						'SERVER_PORT' => Helper::DEFAULT_HTTPS_PORT,
						'SERVER_PATH' => Helper::GOOGLE_API_V3_URI,
						'SERVER_USERNAME' => null,
						'SERVER_PASSWORD' => null,
						'ACCOUNT_TYPE' => Factory::SERVICE_NAME,
					]
				)
			)
			->setOwner($owner)
		;

		$factory = new Factory($connection);
		/** @var Result $nameResult */
		$nameResult = $factory->getImportManager()->requestConnectionId();

		if (!$nameResult->isSuccess() || empty($nameResult->getData()['id']))
		{
			throw new Exception('Can not connect to Google');
		}

		$name = $nameResult->getData()['id'];
		$connectionMap = $mapper->getMap([
			'%=NAME' => '%' . $name . '%',
			'=ENTITY_ID' => $owner->getId(),
			'=ACCOUNT_TYPE' => Factory::SERVICE_NAME,
		], null, ['ID' => 'ASC']);

		$currentConnection = $connectionMap->fetch();

		if ($currentConnection && $duplicatedConnection = $connectionMap->fetch())
		{
			$this->deleteConnectionData($duplicatedConnection->getId());
		}

		$connection->setName($name);

		if ($currentConnection)
		{
			$currentConnection
				->setDeleted(false)
				->setName($name)
			;
			$mapper->update($currentConnection);

			return $currentConnection;
		}

		return $mapper->create($connection);
	}

	/**
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function deactivateCurrentConnections(Role $owner): void
	{
		$connections = $this->connectionProvider->getActiveConnections(
			$owner->getId(),
			$owner->getType(),
			[Factory::SERVICE_NAME]
		);

		foreach ($connections as $connection)
		{
			$this->davConnectionManager->deactivateConnection($connection);
		}
	}

	/**
	 * @throws SqlQueryException
	 */
	private function deleteConnectionData(int $connectionId): void
	{
		global $DB;

		// @todo Use repository
		$DB->Query(sprintf('DELETE FROM b_calendar_event_connection WHERE CONNECTION_ID = %d;', $connectionId));
		$DB->Query(sprintf('DELETE FROM b_calendar_section_connection WHERE CONNECTION_ID = %d;', $connectionId));
		$DB->Query(sprintf('DELETE FROM b_dav_connections WHERE ID = %d;', $connectionId));
	}

	private function addPullEvent(Connection $connection, string $stage): void
	{
		Util::addPullEvent(
			PushCommand::ProcessSyncConnection,
			$connection->getOwner()->getId(),
			[
				'vendorName'  => 'google',
				'accountName' => $connection->getName(),
				'stage'       => $stage,
			]
		);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return void
	 *
	 * @throws ConfigurationException
	 * @throws SendFailedException
	 */
	private function initSubscription(Connection $connection): void
	{
		$message = new SubscribeToPushMessage($connection->getId());

		$itemIdParam = new ItemIdParam($connection->getId());

		$message->send(Queue::GooglePush->value, [$itemIdParam]);
	}
}
