<?php

namespace Bitrix\Calendar\ControllerHelper;

use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\ORM\Query\Query;

class SharingAjaxHelper
{
	public static function getDeletedSharedEvent(int $entryId): ?array
	{
		$result = EventTable::query()
			->setSelect(['*'])
			->where('DELETED', 'Y')
			->where(Query::filter()
				->logic('or')
				->where([
					['ID', $entryId],
					['PARENT_ID', $entryId],
				])
			)
			->where('OWNER_ID', \CCalendar::GetCurUserId())
			->where('EVENT_TYPE', '#shared#')
			->exec()
		;
		$event = $result->fetch() ?: null;

		if ($event)
		{
			$host = Sharing\Helper::getOwnerInfo((int)$event["MEETING_HOST"]);
			$event['HOST_NAME'] = $host['name'];

			$event['timestampFromUTC'] = Sharing\Helper::getEventTimestampUTC($event['DATE_FROM'], $event['TZ_FROM']);
			$event['timestampToUTC'] = Sharing\Helper::getEventTimestampUTC($event['DATE_TO'], $event['TZ_TO']);
		}

		return $event;
	}

	public static function getUserTimezoneName(): string
	{
		$userId = \CCalendar::GetCurUserId();
		return \CCalendar::getUserTimezoneName($userId);
	}
}
