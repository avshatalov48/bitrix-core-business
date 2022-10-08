<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromArray;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Internals\PushTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Util;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Dav\Internals\EO_DavConnection_Collection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialservices\UserTable;
use CDavConnection;
use Exception;

class ConnectionManager
{
	public const INIT_STATUS = [
		'existed' => 'existed',
		'created' => 'created',
		'activated' => 'activated',
	];

	/** @var Factory */
	private $mapperFactory;

	public function __construct()
	{
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @param Role $owner
	 * @param string $accountType
	 * @param array $optionalFilter
	 *
	 * @return Connection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getConnection(Role $owner, string $accountType, array $optionalFilter = []): ?Connection
	{
		$connection = null;
		$connectionData = $this->getConnectionsData($owner, [$accountType], $optionalFilter);
		foreach ($connectionData as $con)
		{
			$connection = $con;
			break;
		}

		return $connection
			? (new BuilderConnectionFromDM($connection))->build()
			: null
		;
	}

	/**
	 * @param Role $owner
	 * @param array $type
	 * @param array $optionalFilter
	 *
	 * @return EO_DavConnection_Collection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getConnectionsData(
		Role $owner,
		array $type,
		array $optionalFilter = []
	): ?EO_DavConnection_Collection
	{
		$statement = DavConnectionTable::query()
			->setSelect(['*'])
			->addFilter('=ENTITY_TYPE', $owner->getType())
			->addFilter('=ENTITY_ID', $owner->getId())
			->addFilter('=ACCOUNT_TYPE', $type)
		;

		if (!empty($optionalFilter))
		{
			foreach ($optionalFilter as $key => $value)
			{
				$statement->addFilter($key, $value);
			}
		}

		return $statement->fetchCollection() ?: null;
	}

	/**
	 * @param Connection $connection
	 *
	 * @return Result
	 */
	public function update(Connection $connection): Result
	{
		try
		{
			$lastModified = new DateTime();
			$fields = [
				'ENTITY_TYPE' => $connection->getOwner()->getType(),
				'ENTITY_ID' => $connection->getOwner()->getId(),
				'ACCOUNT_TYPE' => $connection->getVendor()->getCode(),
				'SYNC_TOKEN' => $connection->getToken(),
				'NAME' => $connection->getName(),
				'SERVER_SCHEME' => $connection->getServer()->getScheme(),
				'SERVER_HOST' => $connection->getServer()->getHost(),
				'SERVER_PORT' => $connection->getServer()->getPort(),
				'SERVER_USERNAME' => $connection->getServer()->getUserName(),
				'SERVER_PASSWORD' => $connection->getServer()->getPassword(),
				'SERVER_PATH' => $connection->getServer()->getBasePath(),
				'MODIFIED' => $lastModified,
				'SYNCHRONIZED' => $lastModified,
				'LAST_RESULT' => $connection->getStatus(),
				'IS_DELETED' => $connection->isDeleted() ? 'Y' : 'N'
			];
			$data = DavConnectionTable::update($connection->getId(), $fields)->getData();
			$data['ID'] = $connection->getId();

			return (new Result())->setData($data);
		}
		catch (Exception $e)
		{
			return (new Result())->addError(new Error($e->getMessage()));
		}
	}

	/**
	 * Smart logic. If the connection already exists, activate it and return. Otherwise, create a new connection.
	 *
	 * @param Role $owner
	 * @param string $accountType
	 * @param string $server
	 *
	 * @return Result
	 */
	public function initConnection(Role $owner, string $accountType, string $server): Result
	{
		$result = new Result();
		$resultData = [];
		try {
			if (!Loader::includeModule('dav'))
			{
				throw new LoaderException('Module dav is required');
			}
			$accountName = $this->getSocialUserLogin($owner, $accountType);
			if ($connection = $this->getConnection($owner, $accountType, ['=NAME' => $accountName]))
			{
				if ($connection->isDeleted())
				{
					$connection->setDeleted(false);
					$this->update($connection);
					$resultData['status'] = self::INIT_STATUS['activated'];
				}
				else
				{
					$resultData['status'] = self::INIT_STATUS['existed'];
				}
			}
			else
			{
				$connection = $this->createConnection(
					$owner,
					$accountType,
					$accountName,
					$server,
				);
				$resultData['status'] = self::INIT_STATUS['created'];
			}
			$resultData['connection'] = $connection;
		}
		catch (Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		$result->setData($resultData);

		return $result;
	}

	/**
	 * @param Role $owner
	 * @param string $type
	 * @param string $name
	 * @param string $serverPath
	 *
	 * @return Connection
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function createConnection(
		Role $owner,
		string $type,
		string $name,
		string $serverPath
	): Connection
	{
		$fields = [
			'ENTITY_TYPE' => $owner->getType(),
			'ENTITY_ID' => $owner->getId(),
			'ACCOUNT_TYPE' => $type,
			'NAME' => $name,
			'SERVER' => $serverPath,
		];
		if ($connectionId = CDavConnection::Add($fields))
		{
			return $this->mapperFactory->getConnection()->getById($connectionId);
		}

		throw new BaseException('Error of create new Dav connection');
	}

	/**
	 * @param Role $owner
	 * @param string $accountType
	 *
	 * @return string
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getSocialUserLogin(Role $owner, string $accountType): string
	{
		$user = UserTable::query()
			->addFilter('=USER_ID', $owner->getId())
			->addFilter('=EXTERNAL_AUTH_ID', $accountType)
			->setSelect(['LOGIN'])
			->fetch();

		return $user['LOGIN'] ?? '';
	}

	/**
	 * @param Connection $connection
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function deactivateConnection(Connection $connection): Result
	{
		$result = new Result();

		if (!Loader::includeModule('dav'))
		{
			$result->addError(new Error('Module dav required'));
		}

		$updateResult = DavConnectionTable::update($connection->getId(), [
			'IS_DELETED' => 'Y',
			'SYNC_TOKEN' => null,
		]);
		if ($updateResult->isSuccess())
		{
			$this->unsubscribeConnection($connection);

			Util::addPullEvent(
				'delete_sync_connection',
				$connection->getOwner()->getId(),
				[
					'connectionId' => $connection->getId()
				]
			);
		}
		else
		{
			$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Connection $connection
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 *
	 * @TODO: move it into PushManager
	 */
	public function unsubscribeConnection(Connection $connection)
	{
		$links = SectionConnectionTable::query()
			->addFilter('CONNECTION_ID', $connection->getId())
			->setSelect(['ID'])
			->exec()
		;

		while ($link = $links->fetchObject())
		{
			SectionConnectionTable::update($link->getId(), [
				'SYNC_TOKEN' => '',
				'PAGE_TOKEN' => '',
			]);
			PushTable::delete([
				'ENTITY_TYPE' => 'SECTION_CONNECTION',
				'ENTITY_ID' => $link->getId(),
			]);
		}
	}

	public function disableConnection(Connection $connection)
	{
		global $DB;
		$id = $connection->getId();
		$DB->Query(
			"UPDATE `b_dav_connections` as con SET con.IS_DELETED ='Y' WHERE con.ID = $id;",
			true
		);
		$DB->Query(
			"DELETE FROM `b_calendar_section_connection` WHERE CONNECTION_ID = $id;",
			true
		);
		$DB->Query(
			"DELETE FROM b_calendar_event_connection WHERE CONNECTION_ID = $id;",
			true
		);

		$DB->Query("DELETE sect FROM b_calendar_section sect
			LEFT JOIN b_calendar_section_connection link ON sect.ID = link.SECTION_ID 
			WHERE link.ID IS NULL 
			  AND sect.EXTERNAL_TYPE = '{$connection->getVendor()->getCode()}'
			  AND sect.OWNER_ID = '{$connection->getOwner()->getId()}'
			  ;",
			true
		);
		$DB->Query("DELETE event FROM `b_calendar_event` event 
			LEFT JOIN b_calendar_section sec ON event.SECTION_ID = sec.ID 
			WHERE sec.ID IS NULL
				AND event.OWNER_ID = '{$connection->getOwner()->getId()}'
				;",
			true
		);
		$DB->Query("DELETE push FROM b_calendar_push push
			LEFT JOIN b_calendar_section_connection as sc on push.ENTITY_ID=sc.ID and push.ENTITY_TYPE='SECTION_CONNECTION'
			WHERE sc.ID IS NULL;",
			true
		);
	}
}
