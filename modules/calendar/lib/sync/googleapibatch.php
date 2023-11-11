<?php

namespace Bitrix\Calendar\Sync;

use Bitrix\Calendar\Sync\Google\Dictionary;
use Bitrix\Calendar\Util;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type;
use Bitrix\Calendar\Internals;
use Bitrix\Main\Web\HttpClient;
use CCalendarEvent;

/**
 * @deprecated
 */
class GoogleApiBatch
{
	protected const FINISH = true;
	protected const NEXT = false;

	/**
	 * @return GoogleApiBatch
	 */
	public static function createInstance(): GoogleApiBatch
	{
		return new self();
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function __construct()
	{
	}

	public function syncLocalEvents(array $events, int $userId, string $gApiCalendarId): array
	{
		return $this->syncEvents($events, $userId, $gApiCalendarId);
	}


	private function syncEvents($events, $userId, $gApiCalendarId): array
	{
		return $this->mergeLocalEventWithExternalData(
			$events, $this->getEventsExternalFields($userId, $events, $gApiCalendarId));
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function syncLocalInstances(array $instances, int $userId, string $gApiCalendarId): array
	{
		return $this->mergeLocalEventWithExternalData(
			$instances,
			$this->getInstancesExternalFields($userId, $instances, $gApiCalendarId)
		);
	}

	/**
	 * @param int $eventId
	 * @param string $exDates
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function calculateExDate(int $eventId, string $exDates): string
	{
		return implode(';', array_diff(
			explode(';', $exDates), $this->getExDatesByInstances($eventId)
		));
	}

	/**
	 * @param $eventId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getInstanceByRecurrenceId(int $eventId): array
	{
		$instancesDb = Internals\EventTable::getList([
			'filter' => Query::filter()->where('RECURRENCE_ID', $eventId),
		]);

		$instances =[];
		while ($instance = $instancesDb->fetch())
		{
			$instances[] = $instance;
		}

		return $instances;
	}


	/**
	 * @param array $events
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function preparedEventToBatch(array $events): array
	{
		return $events;
	}

	/**
	 * @param $originalEventId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getExDatesByInstances(int $originalEventId): array
	{
		$instances = $this->getInstanceByRecurrenceId($originalEventId);

		$excludeDates = [];
		foreach ($instances as $instance)
		{
			$excludeDates[] = \CCalendar::Date(\CCalendar::Timestamp($instance['DATE_FROM']), false);
		}

		return $excludeDates;
	}

	/**
	 * @param array $instances
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareInstanceToBatch(array $instances): array
	{
		$batch = [];
		foreach ($instances as $instance)
		{
			$currentInstance = $instance;
			if (empty($instance['ORIGINAL_DATE_FROM']))
			{
				$instanceDate = \CCalendar::GetOriginalDate(
					$instance['PARENT_DATE_FROM'],
					$instance['DATE_FROM'],
					$instance['PARENT_TZ_FROM']
				);
			}
			else
			{
				$instanceDate = $instance['ORIGINAL_DATE_FROM'];
			}
			/** @var Type\DateTime $eventOriginalStart */
			$eventOriginalStart = Util::getDateObject($instanceDate, false, $instance['PARENT_TZ_FROM']);
			$currentInstance['ORIGINAL_DATE_FROM'] =
				$eventOriginalStart->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME));
			$currentInstance['DAV_XML_ID'] = $instance['PARENT_DAV_XML_ID'];
			$currentInstance['gEventId'] = $instance['PARENT_G_EVENT_ID'] . '_'
				. $eventOriginalStart->setTimeZone(Util::prepareTimezone())->format('Ymd\THis\Z');
			$currentInstance['G_EVENT_ID'] = $currentInstance['gEventId'];
			$batch[] = $currentInstance;
		}

		return $batch;
	}

	/**
	 * @param array $events
	 * @param array $externalFields
	 * @return array
	 */
	private function mergeLocalEventWithExternalData(array $events, array $externalFields): array
	{
		$resultEvents = [];

		foreach ($events as $event)
		{
			if (isset($externalFields[$event['ID']]))
			{
				$event['SYNC_STATUS'] = Dictionary::SYNC_STATUS['success'];
				$resultEvents[] = array_merge($event, $externalFields[$event['ID']]);
			}
			else
			{
				\CCalendarEvent::updateSyncStatus($event['ID'], Dictionary::SYNC_STATUS['undefined']);
			}
		}

		return $resultEvents;
	}

	/**
	 * @param int $userId
	 * @param array $instances
	 * @param string $gApiCalendarId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function getInstancesExternalFields(int $userId, array $instances, string $gApiCalendarId): array
	{
		return (new GoogleApiSync($userId))
			->saveBatchEvents($this->prepareInstanceToBatch($instances), $gApiCalendarId, ['method' => HttpClient::HTTP_PUT])
		;
	}

	/**
	 * @param int $userId
	 * @param array $events
	 * @param string $gApiCalendarId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getEventsExternalFields(int $userId, array $events, string $gApiCalendarId): array
	{
		return (new GoogleApiSync($userId))
			->saveBatchEvents($this->preparedEventToBatch($events), $gApiCalendarId, ['method' => HttpClient::HTTP_POST])
		;
	}
}
