<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\GoogleApiBatch;
use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Type;

class SyncLocalDataSection extends Stepper
{
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		if (!Loader::includeModule("calendar"))
		{
			return self::FINISH_EXECUTION;
		}

		$section = SectionTable::getList([
			'filter' => [
				'ID' => (int)$this->getOuterParams()[0],
			],
		])->fetch();

		if (is_array($section))
		{
			if (empty($section['GAPI_CALENDAR_ID']))
			{
				return self::FINISH_EXECUTION;
			}

			if ($events = \CCalendarEvent::getLocalBatchEvent((int)$section['OWNER_ID'], (int)$section['ID'], $this->getSyncTimestamp()))
			{
				GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']);

				$syncedEvents = (new GoogleApiBatch())->syncLocalEvents($events, (int)$section['OWNER_ID'], $section['GAPI_CALENDAR_ID']);
				$this->updateEventsBatch($syncedEvents);

				GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']);

				return self::CONTINUE_EXECUTION;
			}

			if ($recurrentEvents = \CCalendarEvent::getLocalBatchRecurrentEvent((int)$section['OWNER_ID'], (int)$section['ID'], $this->getSyncTimestamp()))
			{
				GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']);

				$syncedEvents = (new GoogleApiBatch())->syncLocalEvents($recurrentEvents, (int)$section['OWNER_ID'], $section['GAPI_CALENDAR_ID']);
				$this->updateEventsBatch($syncedEvents);

				GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']);

				return self::CONTINUE_EXECUTION;
			}

			if ($instances = \CCalendarEvent::getLocalBatchInstances((int)$section['OWNER_ID'], (int)$section['ID'], $this->getSyncTimestamp()))
			{
				GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']);

				$syncedInstances = (new GoogleApiBatch())->syncLocalInstances($instances, (int)$section['OWNER_ID'], $section['GAPI_CALENDAR_ID']);
				$this->updateEventsBatch($syncedInstances);

				GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']);

				return self::CONTINUE_EXECUTION;
			}

			$pushOptionEnabled = \COption::GetOptionString('calendar', 'sync_by_push', false);
			if ($pushOptionEnabled || \CCalendar::IsBitrix24())
			{
				GoogleApiPush::deletePushChannel(['ENTITY_TYPE' => 'SECTION', 'ENTITY_ID' => $section['ID']]);
				GoogleApiPush::checkSectionsPush([$section], (int)$section['OWNER_ID'], (int)$section['CAL_DAV_CON']);
			}
		}

		return self::FINISH_EXECUTION;
	}

	/**
	 * @param array $eventsBatch
	 */
	private function updateEventsBatch(array $eventsBatch): void
	{
		\CCalendarEvent::updateBatchEventFields($eventsBatch, ['DAV_XML_ID', 'G_EVENT_ID', 'CAL_DAV_LABEL', 'ORIGINAL_DATE_FROM', 'SYNC_STATUS']);
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