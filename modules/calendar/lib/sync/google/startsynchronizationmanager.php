<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Result;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Internals\HandleStatusTrait;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Factories\SyncSectionFactory;
use Bitrix\Calendar\Sync\Handlers\MasterPushHandler;
use Bitrix\Calendar\Sync\Managers\NotificationManager;
use Bitrix\Calendar\Sync\Managers\OutgoingManager;
use Bitrix\Calendar\Sync\Managers\StartSynchronization;
use Bitrix\Calendar\Sync\Managers\VendorDataExchangeManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Psr\Container\NotFoundExceptionInterface;

class StartSynchronizationManager implements StartSynchronization
{
	use HandleStatusTrait;

	private Role $user;
	/**
	 * @var mixed
	 */
	private Mappers\Factory $mapperFactory;
	private Connection $connection;
	private static array $outgoingManagersCache = [];

	/**
	 * @param $userId
	 *
	 * @throws ArgumentException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws NotFoundExceptionInterface
	 */
	public function __construct($userId)
	{
		$this->user = \Bitrix\Calendar\Core\Role\Helper::getUserRole($userId);
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @return Connection|null
	 * @throws Exception
	 * @throws BaseException
	 */
	public function start(): ?Connection
	{
		$this->connection = $connection = $this->createConnection($this->mapperFactory->getConnection());
		$this->sendResult(MasterPushHandler::MASTER_STAGE[0]);

		$factory = new Factory($this->connection);
		$exchangeManager = new VendorDataExchangeManager(
			$factory,
			(new SyncSectionFactory())->getSyncSectionMapByFactory($factory)
		);
		$exchangeManager
			->addStatusHandlerList($this->getStatusHandlerList())
			->exchange();

		// TODO: this results must be sent from $exchangeManager,
		// but it looks like it's not happening
		$this->sendResult(MasterPushHandler::MASTER_STAGE[2]);
		$this->sendResult(MasterPushHandler::MASTER_STAGE[3]);

		$this->initSubscription($connection);

		return $connection;
	}

	/**
	 * @param Mappers\Connection $mapper
	 * @return Connection
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function createConnection(Mappers\Connection $mapper): Connection
	{
		$connection = (new Builders\BuilderConnectionFromExternalData($this->user))->build();
		$factory = new Factory($connection);
		/** @var Result $nameResult */
		$nameResult = $factory->getImportManager()->requestConnectionId();

		if (!$nameResult->isSuccess() || empty($nameResult->getData()['id']))
		{
			throw new BaseException('Can not connect with google');
		}

		$name = $nameResult->getData()['id'];
		$connectionMap = $mapper->getMap([
			'%=NAME' => '%'. $name .'%',
			'=ENTITY_ID' => $this->user->getId(),
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
	 * @param Connection $connection
	 *
	 * @return void
	 */
	public function sendPushNotification(Connection $connection): void
	{
		(new MasterPushHandler($this->user, 'google', $connection->getName()))(MasterPushHandler::MASTER_STAGE[0]);
	}

	/**
	 * @param string $stage
	 *
	 * @return void
	 */
	private function sendResult(string $stage): void
	{
		$this->sendStatus([
			'vendorName'  => 'google',
			'accountName' => $this->connection->getName(),
			'stage'       => $stage,
		]);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function initSubscription(Connection $connection): void
	{
		$links = $this->mapperFactory->getSectionConnection()->getMap([
			'=CONNECTION_ID' => $connection->getId(),
			'=ACTIVE' => 'Y'
		]);

		$manager = $this->getOutgoingManager($connection);
		foreach ($links as $link)
		{
			$manager->subscribeSection($link);
		}

		$manager->subscribeConnection();
	}

	/**
	 * @param Connection $connection
	 *
	 * @return OutgoingManager|mixed
	 *
	 * @throws ObjectNotFoundException
	 */
	private function getOutgoingManager(Connection $connection)
	{
		if (empty(static::$outgoingManagersCache[$connection->getId()]))
		{
			static::$outgoingManagersCache[$connection->getId()] = new OutgoingManager($connection);
		}

		return static::$outgoingManagersCache[$connection->getId()];
	}

	/**
	 * @param int $connectionId
	 *
	 * @return void
	 */
	private function deleteConnectionData(int $connectionId): void
	{
		global $DB;
		$DB->Query("
			DELETE FROM b_calendar_event_connection
			WHERE CONNECTION_ID = " . $connectionId . ";"
		);

		$DB->Query("
			DELETE FROM b_calendar_section_connection
			WHERE CONNECTION_ID = " . $connectionId . ";"
		);

		$DB->Query("
			DELETE FROM b_dav_connections 
			WHERE ID = " . $connectionId . ";"
		);
	}
}
