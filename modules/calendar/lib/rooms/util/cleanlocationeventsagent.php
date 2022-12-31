<?php

namespace Bitrix\Calendar\Rooms\Util;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;

final class CleanLocationEventsAgent
{
	public const DO_CLEAR_DELETED_USER_LOCATION_EVENTS = false;
	private const DAY_LENGTH = 86400;

	/**
	 * @return string
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public static function cleanAgent(): string
	{
		(new self())->cleanLocationEvents();

		return "\\Bitrix\\Calendar\\Rooms\\Util\\CleanLocationEventsAgent::cleanAgent();";
	}

	/**
	 * @return void
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	private function cleanLocationEvents(): void
	{
		if (!Loader::includeModule('calendar'))
		{
			return;
		}

		$toCleanLocationEvents = $this->getLocationEventsNeededToClean();

		if ($toCleanLocationEvents)
		{
			$this->cleanTables($toCleanLocationEvents);
			\CCalendar::ClearCache(['event_list']);
		}
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws SystemException
	 */
	private function getLocationEventsNeededToClean(): array
	{
		$toCleanLocationEvents = $this->getEmptyLocationEvents();
		//for now, we don't do that because of needing to re-save child events after removing booking
		//doing that may cause performance problems
		if (self::DO_CLEAR_DELETED_USER_LOCATION_EVENTS)
		{
			$toCleanLocationEvents = array_unique(
				array_merge($toCleanLocationEvents, $this->getDeletedUsersLocationEvents())
			);
		}

		return array_map(static function($toCleanLocationEvent){
			return (int)$toCleanLocationEvent['ID'];
		}, $toCleanLocationEvents);
	}

	/**
	 * @param $ids
	 * @return void
	 */
	private function cleanTables($ids)
	{
		global $DB;
		$ids = implode(',', $ids);

		$DB->Query("
			DELETE FROM b_calendar_event
			WHERE ID IN ( " . $ids . ");"
		);
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getEmptyLocationEvents(): array
	{
		return EventTable::query()
			->setSelect(['ID'])
			->registerRuntimeField(
				new ReferenceField(
					'PARENT',
					EventTable::class,
					['=this.PARENT_ID' => 'ref.ID'],
					['join_type' => 'LEFT']
				)
			)
			->where('CAL_TYPE', 'location')
			->where('DELETED', 'N')
			->where(
				Query::filter()
					->logic('or')
					->where('PARENT.DELETED', 'Y')
					->whereNull('PARENT.ID')
			)
			->where('DATE_TO_TS_UTC', '>', $this->getTimeForQuery())
			->exec()->fetchAll()
			;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws SystemException
	 */
	private function getDeletedUsersLocationEvents(): array
	{
		return EventTable::query()
			->setSelect(['ID'])
			->registerRuntimeField(
				new ReferenceField(
					'USER',
					UserTable::class,
					['=this.CREATED_BY' => 'ref.ID'],
					['join_type' => 'INNER']
				)
			)
			->where('USER.ACTIVE', 'N')
			->where('CAL_TYPE', 'location')
			->where('DELETED', 'N')
			->where('DATE_TO_TS_UTC', '>', $this->getTimeForQuery())
			->exec()->fetchAll()
			;
	}

	/**
	 * To make sampling more accurate, we subtract day length form current server time
	 * otherwise sampling may be insufficient
	 *
	 * @return int
	 */
	private function getTimeForQuery(): int
	{
		return time() - self::DAY_LENGTH;
	}
}