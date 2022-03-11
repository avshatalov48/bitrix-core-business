<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Google\Dictionary;
use Bitrix\Calendar\Sync\GoogleApiBatch;
use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Calendar\Sync\GoogleApiSync;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Type;

class CorrectEventInGoogle extends Stepper
{
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	/**
	 * @param array $result
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function execute(array &$result): bool
	{
		if (!Loader::includeModule("calendar") || !Loader::includeModule("dav"))
		{
			return self::FINISH_EXECUTION;
		}


		if ($events = $this->getLocalEventWronglySent())
		{
			foreach ($events as $event)
			{
				GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, (int)$event['SECTION_ID']);

				$google = new GoogleApiSync((int)$event['OWNER_ID'], (int)$event['CONNECTION_ID']);
				$google->deleteEvent($event['G_EVENT_ID'], $event['GAPI_CALENDAR_ID']);
				\CCalendarEvent::updateSyncStatus((int)$event['ID'], Dictionary::SYNC_STATUS['deleted']);

				GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, (int)$event['SECTION_ID']);
			}

			return self::CONTINUE_EXECUTION;
		}

		if ($recurrentEvents = $this->getRecurrentEvents())
		{
			foreach ($recurrentEvents as $event)
			{
				GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, (int)$event['SECTION_ID']);

				$google = new GoogleApiSync((int)$event['OWNER_ID']);
				$google->saveEvent($event, $event['GAPI_CALENDAR_ID'], $event['CONNECTION_ID']);
				\CCalendarEvent::updateSyncStatus((int)$event['ID'], Dictionary::SYNC_STATUS['exdated']);

				GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, (int)$event['SECTION_ID']);
			}

			return self::CONTINUE_EXECUTION;
		}

		return self::FINISH_EXECUTION;
	}

	/**
	 * @return array
	 */
	private function getLocalEventWronglySent(): array
	{
		global $DB;

		$events = [];
		$strSql = "SELECT"
				. " e.ID as ID,"
				. " e.G_EVENT_ID as G_EVENT_ID,"
				. " e.OWNER_ID as OWNER_ID,"
				. " s.GAPI_CALENDAR_ID as GAPI_CALENDAR_ID,"
				. " c.ID as CONNECTION_ID,"
				. " s.ID as SECTION_ID"
			. " FROM b_calendar_event e"
			. " INNER JOIN b_calendar_section s"
				. " ON e.SECTION_ID = s.ID"
			. " INNER JOIN b_dav_connections c"
				. " ON s.CAL_DAV_CON = c.ID"
			. " WHERE"
				. " e.MEETING_STATUS = 'N'"
				. " AND e.G_EVENT_ID IS NOT NULL"
				. " AND e.DATE_TO_TS_UTC >= " . $this->getSyncTimestamp()
				. " AND e.SYNC_STATUS != '" . Dictionary::SYNC_STATUS['deleted'] . "'"
				. " AND s.GAPI_CALENDAR_ID IS NOT NULL"
				. " AND s.CAL_DAV_CON IS NOT NULL"
				. " AND s.EXTERNAL_TYPE = 'local'"
			. " LIMIT 10"
			. ";"
		;

		$eventsDb = $DB->Query($strSql);
		while ($event = $eventsDb->Fetch())
		{
			$events[] = $event;
		}

		return $events;
	}


	/**
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function getRecurrentEvents(): array
	{
		global $DB;

		$events = [];
		$strSql = "SELECT DISTINCT "
				. "p.*"
				. ", " . $DB->DateToCharFunction('p.DATE_FROM') . " as DATE_FROM"
				. ", " . $DB->DateToCharFunction('p.DATE_TO') . " as DATE_TO"
				. ", " . $DB->DateToCharFunction('p.DATE_CREATE') . " as DATE_CREATE"
				. ", " . $DB->DateToCharFunction('p.TIMESTAMP_X') . " as TIMESTAMP_X"
				. ", s.CAL_DAV_CON as CONNECTION_ID"
				. ", s.GAPI_CALENDAR_ID as GAPI_CALENDAR_ID"
				. ", GROUP_CONCAT(". $DB->DateToCharFunction('i.DATE_FROM')." SEPARATOR ';') as EXDATE"
			. " FROM b_calendar_event p"
			. " INNER JOIN b_calendar_event i"
				. " ON i.RECURRENCE_ID = p.PARENT_ID"
				. " AND p.OWNER_ID = i.OWNER_ID"
			. " INNER JOIN b_calendar_section s"
				. " ON s.ID = p.SECTION_ID"
			. " WHERE"
				. " p.RRULE IS NOT NULL"
				. " AND p.DATE_TO_TS_UTC >= " . $this->getSyncTimestamp()
				. " AND p.G_EVENT_ID IS NOT NULL"
				. " AND p.SYNC_STATUS != '" . Dictionary::SYNC_STATUS['exdated'] . "'"
				. " AND p.SYNC_STATUS != '" . Dictionary::SYNC_STATUS['delete'] . "'"
				. " AND p.SYNC_STATUS != '" . Dictionary::SYNC_STATUS['deleted'] . "'"
				. " AND i.MEETING_STATUS = 'N'"
				. " AND s.EXTERNAL_TYPE = 'local'"
				. " AND s.CAL_DAV_CON IS NOT NULL"
				. " AND s.GAPI_CALENDAR_ID IS NOT NULL"
			. " GROUP BY p.ID"
			. " LIMIT 10"
			. ";"
		;
		$eventsDb = $DB->Query($strSql);

		while ($event = $eventsDb->Fetch())
		{
			if (isset($event['REMIND']) && $event['REMIND'] !== "")
			{
				$event['REMIND'] = unserialize($event['REMIND'], ['allowed_classes' => false]);
			}

			$event['RRULE'] = \CCalendarEvent::ParseRRULE($event['RRULE']);

			$events[] = $event;
		}

		return $events;
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function getSyncTimestamp(): int
	{
		return (new Type\Date())->add('-2 months')->getTimestamp();
	}
}