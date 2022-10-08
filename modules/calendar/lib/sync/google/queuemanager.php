<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Calendar\Sync\GoogleApiSync;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;
use CCalendar;
use CTimeZone;

class QueueManager
{
	public const PERMANENT_UPDATE_TIME = 5;
	public const REGULAR_CHECK_TIME = 3600;
	private const LIMIT_SECTIONS_FOR_CHECK = 5;

	/**
	 * @param int $lastHandledId
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function checkNotSendEvents(int $lastHandledId = 0): ?string
	{
		return true;

		if (!CCalendar::isGoogleApiEnabled())
		{
			return null;
		}

		$eventsDb = self::getEventsDb($lastHandledId);

		if ($eventsDb === false)
		{
			return "\\Bitrix\\Calendar\\Sync\\Google\\QueueManager::checkNotSendEvents();";
		}

		$lastHandledId = 0;
		while ($event = $eventsDb->Fetch())
		{
			$lastHandledId = $event['ID'];

			if (GoogleApiPush::isAuthError($event['LAST_RESULT']))
			{
				continue;
			}

			$event = self::prepareEvent($event);

			switch ($event['SYNC_STATUS'])
			{
				case Dictionary::SYNC_STATUS['create']:
					self::createEvent($event);
					break;
				case Dictionary::SYNC_STATUS['update']:
					self::updateEvent($event);
					break;
				case Dictionary::SYNC_STATUS['delete']:
					self::deleteEvent($event);
					break;
				case Dictionary::SYNC_STATUS['instance']:
					self::createInstance($event);
					break;
				case Dictionary::SYNC_STATUS['parent']:
					self::updateParents($event);
					break;
				case Dictionary::SYNC_STATUS['next']:
					self::updateNextEvents($event);
					break;
				case Dictionary::SYNC_STATUS['undefined']:
					self::detectEventType($event);
					break;
				default:
					self::detectEventType($event);
			}
		}

		return "\\Bitrix\\Calendar\\Sync\\Google\\QueueManager::checkNotSendEvents(" . $lastHandledId . ");";
	}

	public static function checkIncompleteSync()
	{
		return true;

		if (!CCalendar::isGoogleApiEnabled())
		{
			return null;
		}

		$sections = self::getNotIncompleteSections();
		if ($sections)
		{
			foreach ($sections as $section)
			{
				$tokens = \CCalendarSync::syncCalendarEvents($section);
				if ($tokens)
				{
					\CCalendarSect::Edit([
						'arFields' => [
							'ID' => $section['ID'],
							'SYNC_TOKEN' => $tokens['nextSyncToken'],
							'PAGE_TOKEN' => $tokens['nextPageToken'],
						]
					]);
				}
			}

			self::setIntervalForAgent(self::PERMANENT_UPDATE_TIME, self::PERMANENT_UPDATE_TIME);

			return "\\Bitrix\\Calendar\\Sync\\Google\\QueueManager::checkIncompleteSync();";
		}

		self::setIntervalForAgent();

		return "\\Bitrix\\Calendar\\Sync\\Google\\QueueManager::checkIncompleteSync();";
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function createEvent(array $event): void
	{
		$google = new GoogleApiSync($event['OWNER_ID']);
		$fields = $google->saveEvent($event, $event['GAPI_CALENDAR_ID']);

		if ($fields !== null)
		{
			\CCalendarEvent::updateEventFields($event, [
				'DAV_XML_ID' => $fields['DAV_XML_ID'],
				'CAL_DAV_LABEL' => $fields['CAL_DAV_LABEL'],
				'G_EVENT_ID' => $fields['G_EVENT_ID'],
				'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
			]);
		}
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function updateEvent(array $event): void
	{
		$google = new GoogleApiSync($event['OWNER_ID']);
		$fields = $google->saveEvent($event, $event['GAPI_CALENDAR_ID'], []);

		if ($errors = $google->getTransportErrors())
		{
			$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
			if (is_array($errors))
			{
				foreach ($errors as $error)
				{
					if (
						$googleHelper->isDeletedResource($error['message'])
						|| $googleHelper->isNotFoundError($error['message'])
					)
					{
						\CCalendarEvent::updateEventFields($event, [
							'G_EVENT_ID' => '',
							'DAV_XML_ID' => '',
							'CAL_DAV_LABEL' => '',
							'SYNC_STATUS' => Dictionary::SYNC_STATUS['create'],
						]);
						self::createEvent($event);
						return;
					}
				}
			}
		}
		else if ($fields !== null)
		{
			\CCalendarEvent::updateEventFields($event, [
				'CAL_DAV_LABEL' => $fields['CAL_DAV_LABEL'],
				'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
			]);
		}
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	private static function deleteEvent(array $event): void
	{
		$google = new GoogleApiSync($event['OWNER_ID']);
		$google->deleteEvent($event['G_EVENT_ID'], $event['GAPI_CALENDAR_ID']);

		if ($errors = $google->getTransportErrors())
		{
			$isUpdatedResult = false;
			/** @var Helper $googleHelper */
			$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
			if (is_array($errors))
			{
				foreach ($errors as $error)
				{
					if (
						$googleHelper->isDeletedResource($error['message'])
						|| $googleHelper->isNotFoundError($error['message'])
					)
					{
						$isUpdatedResult = true;
						\CCalendarEvent::updateSyncStatus((int)$event['ID'], Dictionary::SYNC_STATUS['success']);
						break;
					}
				}
			}

			if (!$isUpdatedResult)
			{
				\CCalendarEvent::updateSyncStatus((int)$event['ID'], Dictionary::SYNC_STATUS['delete']);
			}
		}
		else
		{
			\CCalendarEvent::updateSyncStatus((int)$event['ID'], Dictionary::SYNC_STATUS['success']);
		}
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function createInstance(array $event): void
	{
		$parentEvent = Util::getEventById($event['PARENT_ID']);
		$fields = (new GoogleApiSync($event['OWNER_ID']))->saveEvent($event, $event['GAPI_CALENDAR_ID'], [
			'editInstance' => true,
			'originalDavXmlId' => $parentEvent['DAV_XML_ID'],
			'instanceTz' => $event['TZ_FROM'],
		]);

		if ($fields !== null)
		{
			\CCalendarEvent::updateEventFields($event, [
				'DAV_XML_ID' => $fields['DAV_XML_ID'],
				'G_EVENT_ID' => $fields['G_EVENT_ID'],
				'CAL_DAV_LABEL' => $fields['CAL_DAV_LABEL'],
				'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
			]);
		}
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function updateParents(array $event): void
	{
		$fields = (new GoogleApiSync($event['OWNER_ID']))->saveEvent($event, $event['GAPI_CALENDAR_ID'], [
			'editParents' => true,
			'editNextEvents' => false
		]);

		if ($fields !== null)
		{
			\CCalendarEvent::updateEventFields($event, [
				'DAV_XML_ID' => $fields['DAV_XML_ID'],
				'CAL_DAV_LABEL' => $fields['CAL_DAV_LABEL'],
				'G_EVENT_ID' => $fields['G_EVENT_ID'],
				'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
			]);
		}
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function updateNextEvents(array $event): void
	{
		$fields = (new GoogleApiSync($event['OWNER_ID']))->saveEvent($event, $event['GAPI_CALENDAR_ID'], [
			'editNextEvents' => true,
		]);

		if ($fields !== null)
		{
			\CCalendarEvent::updateEventFields($event, [
				'DAV_XML_ID' => $fields['DAV_XML_ID'],
				'CAL_DAV_LABEL' => $fields['CAL_DAV_LABEL'],
				'G_EVENT_ID' => $fields['G_EVENT_ID'],
				'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
			]);
		}
	}

	/**
	 * @param array $event
	 * @return array
	 */
	private static function prepareEvent(array $event): array
	{
		if (!empty($event['RRULE']))
		{
			$event['RRULE'] = \CCalendarEvent::ParseRRULE($event['RRULE']);
		}

		if (!empty($event['REMIND']) && is_string($event['REMIND']))
		{
			$event['REMIND'] = unserialize($event['REMIND'], ['allowed_classes' => false]);
		}

		return $event;
	}

	/**
	 * @param int $lastHandledId
	 * @return \CDBResult|false|void
	 */
	private static function getEventsDb(int $lastHandledId = 0)
	{
		global $DB;

		return $DB->Query(
			"SELECT e.*, c.LAST_RESULT, s.GAPI_CALENDAR_ID, "
			. $DB->DateToCharFunction('e.DATE_FROM') . " as DATE_FROM, "
			. $DB->DateToCharFunction('e.DATE_TO') . " as DATE_TO, "
			. $DB->DateToCharFunction('e.ORIGINAL_DATE_FROM'). " as ORIGINAL_DATE_FROM, "
			. $DB->DateToCharFunction('e.DATE_CREATE'). " as DATE_CREATE, "
			. $DB->DateToCharFunction('e.TIMESTAMP_X'). " as TIMESTAMP_X"
			. " FROM b_calendar_event e"
			. " INNER JOIN b_calendar_section s ON e.SECTION_ID = s.ID"
			. " INNER JOIN b_dav_connections c ON c.ID = s.CAL_DAV_CON"
			. " WHERE e.SYNC_STATUS <> 'success'"
				. " AND e.ID > ".$lastHandledId
				. " AND s.EXTERNAL_TYPE IN ('local', 'google')"
			. " ORDER BY e.ID ASC"
			. " LIMIT 10"
			. ";"
		);
	}

	/**
	 * @param array $event
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function detectEventType(array $event): void
	{
		if (
			$event['SYNC_STATUS'] === Dictionary::SYNC_STATUS['exdated']
			|| $event['SYNC_STATUS'] === Dictionary::SYNC_STATUS['deleted']
		)
		{
			return;
		}

		if (!empty($event['RECURRENCE_ID']))
		{
			self::createInstance($event);
			return;
		}

		if ($event['DELETED'] === 'Y' && !empty($event['G_EVENT_ID']))
		{
			self::deleteEvent($event);
		}
		elseif (!empty($event['RRULE']) && empty($event['G_EVENT_ID']))
		{
			self::updateNextEvents($event);
		}
		elseif (!empty($event['RRULE']) && !empty($event['G_EVENT_ID']))
		{
			self::updateParents($event);
		}
		elseif (!empty($event['G_EVENT_ID']))
		{
			self::updateEvent($event);
		}
		else
		{
			self::createEvent($event);
		}
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getNotIncompleteSections(): array
	{
		return SectionTable::query()
			->whereNotNull('PAGE_TOKEN')
			->setSelect(['*'])
			->setLimit(self::LIMIT_SECTIONS_FOR_CHECK)
			->exec()
			->fetchAll()
		;
	}

	/**
	 * @param int $agentInterval
	 * @param int $delay
	 *
	 * @return void
	 */
	public static function setIntervalForAgent(int $agentInterval = self::REGULAR_CHECK_TIME, int $delay = self::REGULAR_CHECK_TIME): void
	{
		$agent = \CAgent::getList(
			[],
			[
				'MODULE_ID' => 'calendar',
				'=NAME' => '\\Bitrix\\Calendar\\Sync\\Google\\QueueManager::checkIncompleteSync();'
			]
		)->fetch();

		if (is_array($agent) &&  $agent['ID'])
		{
			if ((int)$agent['AGENT_INTERVAL'] !== $agentInterval)
			{
				\CAgent::Update(
					$agent['ID'],
					[
						'AGENT_INTERVAL' => $agentInterval,
						'NEXT_EXEC' => ConvertTimeStamp(time() + CTimeZone::GetOffset() + $delay, "FULL"),
					]
				);
			}
		}
		else
		{
			\CAgent::AddAgent(
				"\\Bitrix\\Calendar\\Sync\\Google\\QueueManager::checkIncompleteSync();",
				"calendar",
				"N",
				$agentInterval
			);
		}
	}
}
