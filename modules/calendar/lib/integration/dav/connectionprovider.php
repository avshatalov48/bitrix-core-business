<?php

namespace Bitrix\Calendar\Integration\Dav;

use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class ConnectionProvider
{
	private const SELECT_FIELDS = [
		'ID',
		'ENTITY_TYPE',
		'ENTITY_ID',
		'ACCOUNT_TYPE',
		'SYNC_TOKEN',
		'NAME',
		'SERVER_SCHEME',
		'SERVER_HOST',
		'SERVER_PORT',
		'SERVER_USERNAME',
		'SERVER_PASSWORD',
		'SERVER_PATH',
		'SYNCHRONIZED',
		'LAST_RESULT',
		'IS_DELETED',
		'NEXT_SYNC_TRY',
	];

	/**
	 * @return Connection[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getConnections(int $userId, string $type, array $providers): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('dav'))
		{
			return [];
		}

		$connections = [];

		$query =
			DavConnectionTable::query()
				->setSelect(self::SELECT_FIELDS)
				->whereIn('ACCOUNT_TYPE', $providers)
				->where('ENTITY_TYPE', $type)
				->where('ENTITY_ID', $userId)
				->setOrder(['SYNCHRONIZED' => 'ASC'])
		;

		foreach ($query->exec()->fetchCollection() as $ormConnection)
		{
			$connections[] = (new BuilderConnectionFromDM($ormConnection))->build();
		}

		return $connections;
	}

	/**
	 * @return Connection[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getActiveConnections(int $userId, string $type, array $providers): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('dav'))
		{
			return [];
		}

		$connections = [];

		$query = DavConnectionTable::query()
			->setSelect(self::SELECT_FIELDS)
			->whereIn('ACCOUNT_TYPE', $providers)
			->where('ENTITY_TYPE', $type)
			->where('ENTITY_ID', $userId)
			->where('IS_DELETED', 'N')
			->setOrder(['SYNCHRONIZED' => 'ASC'])
		;

		foreach ($query->exec()->fetchCollection() as $ormConnection)
		{
			$connections[] = (new BuilderConnectionFromDM($ormConnection))->build();
		}

		return $connections;
	}

	/**
	 * @return Connection[]
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getActiveByProvider(string $provider, int $limit = 10): array
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('dav'))
		{
			return [];
		}

		try
		{
			$ormCollection =
				DavConnectionTable::query()
					->setSelect(self::SELECT_FIELDS)
					->addFilter('=ACCOUNT_TYPE', [$provider])
					->addFilter('=IS_DELETED', 'N')
					->setOrder(['SYNCHRONIZED' => 'ASC'])
					->setLimit($limit > 0 ? $limit : 10)
					->exec()
					->fetchCollection()
			;
		}
		catch (SystemException)
		{
			return [];
		}

		$items = [];

		foreach ($ormCollection as $ormConnection)
		{
			$items[] = (new BuilderConnectionFromDM($ormConnection))->build();
		}

		return $items;
	}

	public function getById(int $connectionId): ?Connection
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		try
		{
			$ormConnection =
				DavConnectionTable::query()
					->setSelect(self::SELECT_FIELDS)
					->where('ID', $connectionId)
					->exec()
					->fetchObject()
			;
		}
		catch (SystemException)
		{
			return null;
		}

		return $ormConnection ? (new BuilderConnectionFromDM($ormConnection))->build() : null;
	}

	public function getActiveUserConnection(int $userId, string $provider): ?Connection
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		try
		{
			$ormConnection = DavConnectionTable::query()
				->setSelect(self::SELECT_FIELDS)
				->where('ACCOUNT_TYPE', $provider)
				->where('ENTITY_TYPE', User::TYPE)
				->where('ENTITY_ID', $userId)
				->where('IS_DELETED', 'N')
				->exec()
				->fetchObject()
			;
		}
		catch (SystemException)
		{
			return null;
		}

		if ($ormConnection)
		{
			return (new BuilderConnectionFromDM($ormConnection))->build();
		}

		return null;
	}
}
