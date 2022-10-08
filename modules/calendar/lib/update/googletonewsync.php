<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Internals\EO_Event_Collection;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Update\Stepper;
use CCalendar;
use Exception;

final class GoogleToNewSync extends Stepper
{
	const PORTION = 100;
	const STATUS_SUCCESS = 'success';
	const OPTION_CONVERTED = 'googleToNewSyncConverted';
	const OPTION_STATUS = 'googleToNewSyncStatus';

	protected static $moduleId = 'calendar';

	public static function className(): string
	{
		return __CLASS__;
	}

	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function execute(array &$option): bool
	{
		if (!Loader::includeModule(self::$moduleId) || !Loader::includeModule('dav'))
		{
			return self::FINISH_EXECUTION;
		}
		if (Option::get(self::$moduleId, self::OPTION_CONVERTED, 'N') === 'Y')
		{
			CCalendar::ClearCache();

			return self::FINISH_EXECUTION;
		}

		$status = $this->loadCurrentStatus();
		$events = $this->getGoogleEvents();
		$eventIds = [];
		if ($events->count())
		{
			foreach ($events as $event)
			{
				$status['lastEventId'] = $event->getId();
				/** @var EO_SectionConnection $connection */
				$connection = $event->get('SECTION_CONNECTION');
				if ($connection && $connectionId = $connection->getConnectionId())
				{
					$eventIds[] = $this->createEventConnection($event, $connectionId);
				}
				else
				{
					$eventIds[] = $event->getId();
				}
			}

			if ($eventIds)
			{
				$this->cleanExtraEventInfo($eventIds);
				$status['steps'] += $events->count();
			}

				Option::set(self::$moduleId, self::OPTION_STATUS, serialize($status));
				$option = [
					'count' => $status['count'],
					'steps' => $status['steps'],
					'lastEventId' => $status['lastEventId'],
				];

				return self::CONTINUE_EXECUTION;
		}

		$this->deDuplicate();

		Option::set(self::$moduleId, self::OPTION_CONVERTED, 'Y');
		Option::delete(self::$moduleId, ['name' => self::OPTION_STATUS]);
		$this->unblockAllPushChannels();
		CCalendar::ClearCache();

		return self::FINISH_EXECUTION;
	}

	private function loadCurrentStatus(): array
	{
		$status = Option::get(self::$moduleId, self::OPTION_STATUS, 'default');
		$status = $status !== 'default' ? @unserialize($status, ['allowed_classes' => false]) : [];
		$status = is_array($status) ? $status : [];

		if (empty($status))
		{
			$status = [
				'steps' => 0,
				'count' => $this->getTotalCountEvents(),
				'lastEventId' => 0,
			];
		}

		return $status;
	}

	/**
	 * @return int
	 */
	private function getTotalCountEvents(): int
	{
		global $DB;
		$count = 0;
		$result = $DB->Query("
			SELECT COUNT(*) AS cnt 
			FROM b_calendar_event
			WHERE G_EVENT_ID IS NOT NULL
			AND DELETED = 'N'
		");
		if ($res = $result->Fetch())
		{
			$count = (int)$res['cnt'];
		}

		return $count;
	}

	/**
	 * @return EO_Event_Collection
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getGoogleEvents(): EO_Event_Collection
	{
		return EventTable::query()
			->setSelect([
				'ID',
				'ORIGINAL_DATE_FROM',
				'RECURRENCE_ID',
				'DAV_XML_ID',
				'G_EVENT_ID',
				'CAL_DAV_LABEL',
				'VERSION',
				'SYNC_STATUS',
				'SECTION_CONNECTION.CONNECTION_ID',
			])
			->whereNotNull('G_EVENT_ID')
			->where('DELETED', 'N')
			->setLimit(self::PORTION)
			->setOrder(['ID' => 'DESC'])
			->registerRuntimeField(
				'SECTION_CONNECTION',
				new ReferenceField(
					'LINK',
					SectionConnectionTable::getEntity(),
					Join::on('ref.SECTION_ID', 'this.SECTION_ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->exec()->fetchCollection()
		;
	}

	/**
	 * @param array $eventIds
	 *
	 * @return void
	 */
	private function cleanExtraEventInfo(array $eventIds): void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_event 
			SET G_EVENT_ID = null,
		    CAL_DAV_LABEL = null,
		    SYNC_STATUS = null
			WHERE ID IN (" . implode(',', $eventIds) . ");
		");
	}

	/**
	 * @param EO_Event $event
	 * @param int $connectionId
	 *
	 * @return int
	 * @throws Exception
	 */
	private function createEventConnection(EO_Event $event, int $connectionId): int
	{
		$recId = ($event->getOriginalDateFrom() && $event->getRecurrenceId())
			? $event->getDavXmlId()
			: null
		;
		EventConnectionTable::add([
			'EVENT_ID' => $event->getId(),
			'CONNECTION_ID' => $connectionId,
			'VENDOR_EVENT_ID' => $event->getGEventId(),
			'SYNC_STATUS' => self::STATUS_SUCCESS,
			'ENTITY_TAG' => $event->getCalDavLabel(),
			'VERSION' => $event->getVersion(),
			'VENDOR_VERSION_ID' => $event->getVersion(),
			'RECURRENCE_ID' => $recId,
		]);

		return $event->getId();
	}

	/**
	 * @return void
	 */
	private function unblockAllPushChannels(): void
	{
		global $DB;
		$DB->Query("
			UPDATE b_calendar_push
			SET NOT_PROCESSED = 'N'
			WHERE 1;
		");
	}

	/**
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function deDuplicate()
	{
		$this->deDuplicateEvents();
		$this->deDuplicateSections();
	}

	/**
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function deDuplicateEvents()
	{
		global $DB;
		$sql = "SELECT GROUP_CONCAT(link.ID) as ids
			FROM b_calendar_event_connection link
			GROUP BY link.CONNECTION_ID , link.EVENT_ID
			HAVING count(*) > 1
			;";
		$duplicateGroups = $DB->Query($sql);
		while ($row = $duplicateGroups->Fetch())
		{
			$ids = explode(',', $row['ids']);
			$this->processEventDubles($ids);
		}
	}

	/**
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function deDuplicateSections()
	{
		global $DB;
		$sql = "SELECT GROUP_CONCAT(link.ID) as ids
			FROM b_calendar_section_connection link
			GROUP BY link.CONNECTION_ID , link.SECTION_ID
			HAVING count(*) > 1
			;";
		$duplicateGroups = $DB->Query($sql);
		while ($row = $duplicateGroups->Fetch())
		{
			$ids = explode(',', $row['ids']);
			$this->processSectionDubles($ids);
		}
	}

	/**
	 * @param array $linkIds
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function processEventDubles(array $linkIds)
	{
		$this->processDubles(
			$linkIds,
			new EventConnectionTable(),
			'VENDOR_EVENT_ID'
		);
	}

	/**
	 * @param array $linkIds
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function processSectionDubles(array $linkIds)
	{
		$this->processDubles(
			$linkIds,
			new SectionConnectionTable(),
			'VENDOR_SECTION_ID'
		);
	}

	/**
	 * @param array $linkIds
	 * @param DataManager $table
	 * @param string $validFieldName
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	private function processDubles(array $linkIds, DataManager $table,  string $validFieldName)
	{
		$validRow = $table->query()
			->setSelect(['ID'])
			->addFilter("!$validFieldName", false)
			->whereIn('ID', $linkIds)
			->fetch();

		if (!empty($validRow))
		{
			foreach ($linkIds as $index => $linkId)
			{
				if ($linkId == $validRow['ID'])
				{
					unset($linkIds[$index]);
					break;
				}
			}
		}

		foreach ($linkIds as $linkId)
		{
			$table->delete($linkId);
		}
	}
}