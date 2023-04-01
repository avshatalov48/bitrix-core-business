<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Internals\HandleStatusTrait;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Exceptions\SyncException;
use Bitrix\Calendar\Sync\Factories\SyncSectionFactory;
use Bitrix\Calendar\Sync\Managers\ConnectionManager;
use Bitrix\Calendar\Sync\Managers\NotificationManager;
use Bitrix\Calendar\Sync\Managers\OutgoingManager;
use Bitrix\Calendar\Sync\Managers\StartSynchronization;
use Bitrix\Calendar\Sync\Managers\VendorDataExchangeManager;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CCalendar;
use COption;
use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class StartSyncController implements StartSynchronization
{
	use HandleStatusTrait;

	private const STATUSES = [
		'connection_created' => 'connection_created',
		'connection_renamed' => 'connection_renamed',
		'sections_sync_finished' => 'sections_sync_finished',
		'events_sync_finished' => 'events_sync_finished',
		'subscribe_finished' => 'subscribe_finished',
		'all_finished' => 'all_finished',
	];

	/**
	 * @var string
	 */
	private string $accountName = '';
	/**
	 * @var Role
	 */
	private Role $owner;

	/**
	 * @param Role $owner
	 */
	public function __construct(Role $owner)
	{
		$this->owner = $owner;
	}

	/**
	 * @return ?Connection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 * @throws Throwable
	 */
	public function start(): ?Connection
	{
		/** @var Connection $connection */
		if ($connection = $this->initConnection())
		{
			// TODO: change it to disabling update sections agent
			$this->muteConnection($connection, true);
			$status = self::STATUSES['connection_created'];
			try
			{
				$connection = $this->fixUglyAccountName($connection);
				$this->sendResult($status);
				// $status = self::STATUSES['connection_renamed']; // this status is virtual. We can't roll back it.

				$factory = new Factory($connection);

				$exchangeManager = new VendorDataExchangeManager(
					$factory,
					(new SyncSectionFactory())->getSyncSectionMapByFactory($factory)
				);

				$exchangeManager
					->addStatusHandlerList($this->getStatusHandlerList())
					->exchange();

				$status = self::STATUSES['events_sync_finished'];
				$this->sendResult($status);

				if ($this->isPushEnabled())
				{
					$this->initSubscription($connection);
					$status = self::STATUSES['subscribe_finished'];
					$this->sendResult($status);
				}

				$this->setConnectionStatus($connection, Sync\Dictionary::SYNC_STATUS['success']);

				$this->muteConnection($connection, false);
				return $connection;
			}
			catch (SyncException|Throwable $e)
			{
				// TODO: remove Throwable after finish of testing
				$this->rollBack($connection);
				throw $e;
			}
		}

		throw new SyncException('Error of create connection');
	}

	/**
	 * @param Connection $connection
	 * @param bool $state
	 *
	 * @return void
	 */
	private function muteConnection(Connection $connection, bool $state)
	{
		$original = $connection->isDeleted();
		$connection->setDeleted($state);
		(new Core\Mappers\Connection())->update($connection);
		$connection->setDeleted($original);
	}

	/**
	 * @return Connection
	 */
	private function initConnection(): ?Connection
	{
		$connectionManager = new ConnectionManager();
		$result = $connectionManager->initConnection(
			$this->owner,
			Helper::ACCOUNT_TYPE,
			Helper::SERVER_PATH,
		);
		if ($result->isSuccess())
		{
			return $result->getData()['connection'];
		}

		return null;
	}

	/**
	 * @param string $stage
	 *
	 * @return void
	 */
	private function sendResult(string $stage)
	{
		$this->sendStatus([
			'vendorName'  => Helper::ACCOUNT_TYPE,
			'accountName' => $this->getAccountName(),
			'stage'       => $stage,
		]);
	}

	/**
	 * @return string
	 */
	private function getAccountName(): string
	{
		return $this->accountName ?? '';
	}

	private array $outgoingManagersCache = [];
	/**
	 * @param Connection $connection
	 *
	 * @return OutgoingManager|mixed
	 *
	 * @throws ObjectNotFoundException
	 */
	private function getOutgoingManager(Connection $connection)
	{

		if (empty($this->outgoingManagersCache[$connection->getId()]))
		{
			$this->outgoingManagersCache[$connection->getId()] = new OutgoingManager($connection);
		}

		return $this->outgoingManagersCache[$connection->getId()];
	}

	/**
	 * @param Connection $connection
	 *
	 * @return Result
	 */
	private function initSubscription(Connection $connection): Result
	{
		$result = new Result();
		try
		{
			$links = (new Core\Mappers\SectionConnection())->getMap([
				'=CONNECTION_ID' => $connection->getId(),
				'=ACTIVE' => 'Y'
			]);
			$manager = $this->getOutgoingManager($connection);
			foreach ($links as $link)
			{
				try
				{
					$manager->subscribeSection($link);
				}
				catch (Exception $e)
				{
					$result->addError(new Error($e->getMessage(), $e->getCode()));
				}
			}
		}
		catch (Exception $e)
		{
			$result->addError(new Error($e->getMessage(), $e->getCode()));
		}

		return $result;
	}

	/**
	 * @param Connection $connection
	 * @param string $status
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function setConnectionStatus(Connection $connection, string $status)
	{
		DavConnectionTable::update($connection->getId(), [
			'LAST_RESULT' => $status,
		]);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return Connection
	 *
	 * @throws NotFoundExceptionInterface
	 */
	private function fixUglyAccountName(Connection $connection): Connection
	{
		if (substr($connection->getName(), 0,9) === 'Office365')
		{
			$currentName = $connection->getName();
			try {
				$context = Office365Context::getConnectionContext($connection);
				$userData = $context->getApiClient()->get('me');
				if (!empty($userData['userPrincipalName']))
				{
					if ($oldConnection = $this->getConnection(
						$connection->getOwner(),
						Helper::ACCOUNT_TYPE,
						$userData['userPrincipalName']
					))
					{
						$oldConnection->setDeleted(false);
						(new Core\Mappers\Connection())->delete($connection, ['softDelete' => false]);
						$connection = $oldConnection;
					}
					else
					{
						$connection->setName($userData['userPrincipalName']);
						$result = (new ConnectionManager())->update($connection);
						if (!$result->isSuccess())
						{
							$connection->setName($currentName);
						}
					}
				}
			} catch (Exception $e) {
				$connection->setName($currentName);
			}
		}
		$this->accountName = $connection->getName();

		return $connection;
	}

	/**
	 * @param Role $owner
	 * @param string $serviceName
	 * @param string $name
	 *
	 * @return Connection|null
	 */
	private function getConnection(Role $owner, string $serviceName, string $name): ?Connection
	{
		try
		{
			return (new Core\Mappers\Connection())->getMap([
				'=ENTITY_TYPE' => $owner->getType(),
				'=ENTITY_ID' => $owner->getId(),
				'=ACCOUNT_TYPE' => $serviceName,
				'=NAME' => $name,
			])->fetch();
		}
		catch (BaseException|ArgumentException|SystemException $e)
		{
			return null;
		}
	}

	/**
	 * @param Connection $connection
	 *
	 * @return void
	 *
	 * @throws LoaderException
	 */
	private function rollBack(Connection $connection)
	{
		(new ConnectionManager())->disableConnection($connection);

		NotificationManager::sendRollbackSyncNotification(
			$connection->getOwner()->getId(),
			$connection->getVendor()->getCode()
		);
	}

	/**
	 * @return bool
	 */
	private function isPushEnabled(): bool
	{
		return CCalendar::IsBitrix24() || COption::GetOptionString('calendar', 'sync_by_push', false);
	}
}
