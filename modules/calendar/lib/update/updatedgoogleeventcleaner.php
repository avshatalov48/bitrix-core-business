<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

final class UpdatedGoogleEventCleaner extends Stepper
{
	private const PORTION = 200;
	protected static $moduleId = "calendar";

	public static function className(): string
	{
		return __CLASS__;
	}

	/**
	 * @param array $option
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return self::FINISH_EXECUTION;
		}

		$linksInfo = $this->getDuplicatedLinkInfo();

		if ($linksInfo->SelectedRowsCount())
		{
			while ($linkInfo = $linksInfo->Fetch())
			{
				$duplicatedLinkId = (int)$linkInfo['FIRST_LINK_ID'];
				$originalLinkId = (int)$linkInfo['LAST_LINK_ID'];

				$events = $this->getEventList([$duplicatedLinkId, $originalLinkId]);
				$duplicatedEvent = $events[$duplicatedLinkId];
				$originalEvent = $events[$originalLinkId];

				if (!$duplicatedEvent)
				{
					$this->deleteDuplicatedLink($duplicatedLinkId);
				}
				if (!$originalEvent)
				{
					$this->deleteDuplicatedLink($originalLinkId);
				}
				if (!$duplicatedEvent || !$originalEvent)
				{
					continue;
				}

				if ($duplicatedEvent['DELETED'] === 'Y')
				{
					$this->deleteDuplicatedLink($duplicatedLinkId);
					continue;
				}

				$this->markEventAsDeleted((int)$duplicatedEvent['ID']);
				$this->deleteDuplicatedLink($duplicatedLinkId);

				if ($originalEvent['DELETED'] === 'Y')
				{
					$this->restoreEvent((int)$originalEvent['PARENT_ID']);
				}
			}

			return self::CONTINUE_EXECUTION;
		}

		\CCalendar::ClearCache();
		return self::FINISH_EXECUTION;
	}

	/**
	 * @return \CDBResult|false|void
	 */
	private function getDuplicatedLinkInfo()
	{
		global $DB;

		return $DB->Query("
			SELECT
	            MIN(ID) as FIRST_LINK_ID, 
	            MAX(ID) as LAST_LINK_ID,
	            VENDOR_EVENT_ID,
	            COUNT(1) as CNT
			FROM b_calendar_event_connection
			WHERE VENDOR_EVENT_ID <> '' AND ENTITY_TAG <> ''
			GROUP BY VENDOR_EVENT_ID, CONNECTION_ID
			HAVING CNT = 2
			LIMIT " . self::PORTION . ";
		");
	}

	/**
	 * @param array $linkList
	 *
	 * @return array|null
	 */
	private function getEventList(array $linkList): ?array
	{
		$result = [];
		global $DB;

		$query = $DB->Query("
			SELECT EV.ID, EV.DELETED, EV.PARENT_ID, CON.ID as LINK_ID
			FROM b_calendar_event EV
			INNER JOIN b_calendar_event_connection CON ON EV.ID = CON.EVENT_ID
			WHERE CON.ID IN ( " . implode(',', $linkList) . ");
		");

		while ($event = $query->Fetch())
		{
			$result[$event['LINK_ID']] = $event;
		}

		return $result;
	}

	/**
	 * @param int $eventId
	 *
	 * @return void
	 */
	private function markEventAsDeleted(int $eventId): void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event 
			SET DELETED = 'Y'
			WHERE ID = " . $eventId . ";
		");
	}

	/**
	 * @param int $linkId
	 *
	 * @return void
	 */
	private function deleteDuplicatedLink(int $linkId): void
	{
		global $DB;
		$DB->Query("
			DELETE FROM b_calendar_event_connection
			WHERE ID = " . $linkId . ";
		");
	}

	/**
	 * @param int $parentEventId
	 *
	 * @return void
	 */
	private function restoreEvent(int $parentEventId): void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event
			SET DELETED = 'N'
			WHERE PARENT_ID = " . $parentEventId . "
		");
	}
}