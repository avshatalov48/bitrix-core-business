<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UserTable;

final class CleanConnectionAgent
{
	private const WEEK_TIME_SLICE = 650000;
	private const SYNC_CONNECTIONS = [
		'icloud',
		'office365',
		'google_api_oauth',
		'caldav',
		'yandex',
		'caldav_google_oauth'
	];

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function cleanAgent(): string
	{
		(new self())->cleanConnections();

		return "\\Bitrix\\Calendar\\Sync\\Util\\CleanConnectionAgent::cleanAgent();";
	}

	/**
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	private function cleanConnections(): void
	{
		if (!Loader::includeModule('dav') || !Loader::includeModule('calendar'))
		{
			return;
		}

		$deletedUsersConnections = $this->getDeletedUsersConnection();

		$deletedUsersConnectionsIds = array_map(static function($deletedConnection){
			return (int)$deletedConnection['ID'];
		}, $deletedUsersConnections);

		if ($deletedUsersConnectionsIds)
		{
			$this->cleanTables($deletedUsersConnectionsIds);
		}
	}

	/**
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getDeletedConnections(): array
	{
		return DavConnectionTable::query()
			->setSelect(['ID', 'ACCOUNT_TYPE'])
			->where('IS_DELETED', 'Y')
			->where('SYNCHRONIZED', '<', new Date(\CCalendar::Date(time() - self::WEEK_TIME_SLICE)))
			->whereIn('ACCOUNT_TYPE', self::SYNC_CONNECTIONS)
			->exec()->fetchAll()
		;
	}

	/**
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getDeletedUsersConnection(): array
	{
		return DavConnectionTable::query()
			->setSelect(['ID', 'ACCOUNT_TYPE'])
			->registerRuntimeField(
				new ReferenceField(
					'USER',
					UserTable::class,
					['=this.ENTITY_ID' => 'ref.ID'],
					['join_type' => 'INNER']
				)
			)
			->where('USER.ACTIVE', 'N')
			->whereIn('ACCOUNT_TYPE', self::SYNC_CONNECTIONS)
			->exec()->fetchAll()
		;
	}

	/**
	 * @param array $id
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function cleanTables(array $id): void
	{
		global $DB;
		$id = implode(',', $id);
		$DB->Query("
			DELETE FROM b_calendar_event_connection
			WHERE CONNECTION_ID IN ( " . $id . ");"
		);

		$DB->Query("
			DELETE FROM b_calendar_section_connection
			WHERE CONNECTION_ID IN (" . $id . ");"
		);

		$DB->Query("
			DELETE FROM b_dav_connections 
			WHERE ID IN (" . $id . ");"
		);
	}
}

