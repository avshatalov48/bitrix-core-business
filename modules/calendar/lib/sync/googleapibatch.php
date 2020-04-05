<?php

namespace Bitrix\Calendar\Sync;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type;
use Bitrix\Calendar\Internals;
use CCalendarEvent;

class GoogleApiBatch
{
	const SIZE = 50;
	const FINISH = true;
	const NEXT = false;

	private $sectionId,
			$dateSync;

	private $userId = 0,
		$gApiCalendarId = '';

	/**
	 * GoogleApiBatch constructor.
	 * @param $sectionId
	 */
	public function __construct($sectionId)
	{
		$this->sectionId = $sectionId;
		$this->dateSync = $this->getDateSync();
	}

	/**
	 * @return bool
	 */
	public function syncStepLocalEvents()
	{
		if (empty($this->sectionId))
		{
			return self::FINISH;
		}

		$section = $this->getSectionById($this->sectionId);

		if (!empty($section))
		{
			$this->gApiCalendarId = $section['GAPI_CALENDAR_ID'];
			$this->userId = $section['OWNER_ID'];
			$events = $this->getEvents();

			if (!empty($events))
			{
				$res = $this->syncEvents($events);
			}
			else
			{
				$res = $this->syncInstances();
			}
		}
		else
		{
			AddMessage2Log('Can\'t find section: '.$this->sectionId, 'calendar');
			$res = self::FINISH;
		}

		return $res;
	}

	/**
	 * @param $events
	 * @return bool
	 */
	private function syncEvents($events)
	{
		$params['method'] = 'POST';
		foreach ($events as &$event)
		{
			if (!empty($event['RRULE']) && !empty($event['EXDATE']))
			{
				$baseExdate[$event['ID']]['EXDATE'] = $event['EXDATE'];
				$event['EXDATE'] = $this->getExDate($event);
			}
		}

		unset($event);

		$googleApiConnection = new GoogleApiSync($this->userId);
		$externalFields = $googleApiConnection->saveBatchEvents($events, $this->gApiCalendarId, $params);

		if (!empty($externalFields))
		{
			foreach ($events as $event)
			{
				$resultEvents[] = array_merge($event, $externalFields[$event['ID']], $baseExdate[$event['ID']]);
			}
		}
		else
		{
			AddMessage2Log('Failed to sync events of section: '.$this->sectionId, 'calendar');
		}

		$this->saveEvents($resultEvents);

		return self::NEXT;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function syncInstances()
	{
		$batch = [];
		$resultEvents =[];
		$params['method'] = 'PUT';
		$instances = $this->getInstances();
		$size = count($instances);

		if (!empty($instances) && $size > 0)
		{
			foreach ($instances as $instance)
			{
				$recurrenceIds[] = $instance['RECURRENCE_ID'];
			}

			$eventIds = array_unique($recurrenceIds);
			$events = $this->getEventsById($eventIds);

			foreach ($events as $event)
			{
				$instanceData[$event['ID']]['originalTz'] = $event['TZ_FROM'];
				$instanceData[$event['ID']]['dateFrom'] = $event['DATE_FROM'];
				$instanceData[$event['ID']]['DAV_XML_ID'] = $event['G_EVENT_ID'];
			}

			foreach ($instances as $instance)
			{
				$currentInstance = $instance;
				$instanceDate = \CCalendar::GetOriginalDate($instanceData[$instance['RECURRENCE_ID']]['dateFrom'], $instance['DATE_FROM'], $instanceData[$instance['RECURRENCE_ID']]['originalTz']);
				$instanceOriginalTz = $this->prepareTimezone($instanceData[$instance['RECURRENCE_ID']]['originalTz']);
				$eventOriginalStart = new Type\DateTime($instanceDate, Type\Date::convertFormatToPhp(FORMAT_DATETIME), $instanceOriginalTz);
				$currentInstance['ORIGINAL_DATE_FROM'] = $eventOriginalStart;
				$currentInstance['DAV_XML_ID'] = $instanceData[$instance['RECURRENCE_ID']]['DAV_XML_ID'];
				$utcTz = $this->prepareTimezone();
				$suffixIdInstance = $eventOriginalStart->setTimeZone($utcTz)->format('Ymd\THis\Z');
				$currentInstance['gEventId'] = $instanceData[$instance['RECURRENCE_ID']]['DAV_XML_ID'].'_'.$suffixIdInstance;
				$batch[] = $currentInstance;
			}

			$googleApiConnection = new GoogleApiSync($this->userId);
			$externalFields = $googleApiConnection->saveBatchEvents($batch, $this->gApiCalendarId, $params);

			if (!empty($externalFields))
			{
				foreach ($instances as $instance)
				{
					$resultEvents[] = array_merge($instance, $externalFields[$instance['ID']]);
				}

				$this->saveEvents($resultEvents);
			}
			else
			{
				AddMessage2Log('Failed to sync instances of section: '.$this->sectionId, 'calendar');
			}

			if ($size < self::SIZE)
			{
				$res = self::FINISH;
			}
			else
			{
				$res = self::NEXT;
			}
		}
		else
		{
			$res = self::FINISH;
		}

		return $res;
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function getDateSync()
	{
		$now = new Type\Date();
		return $now->add('-2 months')->getTimestamp();
	}

	/**
	 * @return array
	 */
	public function getEvents()
	{
		$parameters = [
				'filter' => [
					'=RECURRENCE_ID' => null,
					'=CAL_TYPE' => 'user',
					'=OWNER_ID' => $this->userId,
					'=DELETED' => 'N',
					'>=DATE_TO_TS_UTC' => $this->dateSync,
					'=SECTION_ID' => $this->sectionId,
					'=G_EVENT_ID' => null,
				],
				'order' => [
					'ID',
				],
				'limit' => self::SIZE,
		];

		return $this->getDbEvents($parameters);
	}

	/**
	 * @return array
	 */
	private function getInstances()
	{
		$parameters = [
			'filter' => [
				'!=RECURRENCE_ID' => null,
				'=CAL_TYPE' => 'user',
				'=OWNER_ID' => $this->userId,
				'=DELETED' => 'N',
				'>=DATE_TO_TS_UTC' => $this->dateSync,
				'=SECTION_ID' => $this->sectionId,
				'=G_EVENT_ID' => null,
			],
			'order' => [
				'ID',
			],
			'limit' => self::SIZE,
		];

		return $this->getDbEvents($parameters);
	}

	/**
	 * @param $parameters
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getDbEvents($parameters)
	{
		$result = [];
		$events = Internals\EventTable::getList($parameters)->fetchAll();

		foreach ($events as $event)
		{
			$result[] = $this->updateEventFields($event);
		}

		return $result;
	}

	/**
	 * @param $events
	 * @return bool
	 */
	private function saveEvents($events)
	{
		foreach ($events as $event)
		{
			$this->prepareEvent($event);
			\CCalendarEvent::Edit([
				'arFields' => $event,
				'currentEvent' => $event,
			]);
		}

		return true;
	}

	/**
	 * @param $eventIds
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getEventsById($eventIds)
	{
		$parameters = [
			'filter' => [
				'=ID' => $eventIds,
			],
		];

		return $this->getDbEvents($parameters);
	}

	/**
	 * @param bool $timeZone
	 * @return \DateTimeZone
	 */
	private function prepareTimezone ($timeZone = false)
	{
		return !empty($timeZone) && $timeZone !== false ? new \DateTimeZone($timeZone) : new \DateTimeZone("UTC");
	}

	/**
	 * @param $sectionId
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getSectionById($sectionId)
	{
		return Internals\SectionTable::getById($sectionId)->fetch();
	}

	/**
	 * @param $event
	 * @return string
	 */
	private function getExDate($event)
	{
		$excludeDates = [];
		$instances = $this->getInstanceByRecurrenceId($event['ID']);

		foreach ($instances as $instance)
		{
			$excludeDates[] = \CCalendar::Date(\CCalendar::Timestamp($instance['DATE_FROM']), false);
		}

		$eventExDate = explode(';', $event['EXDATE']);
		$result = array_diff($eventExDate, $excludeDates);

		return implode(';', $result);
	}

	/**
	 * @param $eventId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getInstanceByRecurrenceId($eventId)
	{
		$instances =[];
		$filter = Query::filter()
			->where('RECURRENCE_ID', $eventId);

		$instancesList = Internals\EventTable::getList(
			array(
				'filter' => $filter,
			)
		);

		while ($instance = $instancesList->fetch())
		{
			$instances[] = $instance;
		}

		return $instances;
	}

	/**
	 * @param $event
	 */
	private function prepareEvent(&$event)
	{
		$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);
	}

	/**
	 * @param $event
	 * @return mixed
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function updateEventFields($event)
	{
		\CTimeZone::Disable();
		$tzFrom = $this->prepareTimezone($event['TZ_FROM']);
		$event['DATE_FROM'] = (new Type\DateTime($event['DATE_FROM'], Type\Date::convertFormatToPhp(FORMAT_DATETIME), $tzFrom))->toString();
		$tzTo = $this->prepareTimezone($event['TZ_TO']);
		$event['DATE_TO'] = (new Type\DateTime($event['DATE_TO'], Type\Date::convertFormatToPhp(FORMAT_DATETIME), $tzTo))->toString();
		$event['ORIGINAL_DATE_FROM'] = (new Type\DateTime($event['ORIGINAL_DATE_FROM'], Type\Date::convertFormatToPhp(FORMAT_DATETIME), $tzFrom))->toString();
		\CTimeZone::Enable();
		return $event;
	}
}