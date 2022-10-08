<?php

namespace Bitrix\Calendar\Update;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;


final class SectionStructureUpdate extends Stepper
{
	const PORTION = 40;

	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		global $DB;
		$BATCH_SIZE = 1000;
		if (Loader::includeModule("calendar")
			&& Option::get('calendar', 'sectionStructureConverted', 'N') === 'Y')
		{
			return self::FINISH_EXECUTION;
		}

		$status = $this->loadCurrentStatus();
		if ($status['finished'])
		{
			return self::FINISH_EXECUTION;
		}

		$newStatus = array(
			'count' => $status['count'],
			'steps' => $status['steps'],
			'lastEventId' => null,
			'finished' => $status['finished']
		);

		if (!$status['finished'])
		{
			$r = $DB->Query('select ID from b_calendar_event where SECTION_ID is null order by id asc limit 1;', false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($entry = $r->Fetch())
			{
				$newStatus['lastEventId'] = $entry['ID'];
				if ((int)$status['lastEventId'] === (int)$newStatus['lastEventId'])
				{
					return self::FINISH_EXECUTION;
				}
				else
				{
					$DB->Query('UPDATE b_calendar_event CE
						INNER JOIN b_calendar_event_sect CES ON CE.ID = CES.EVENT_ID
						SET CE.SECTION_ID = CES.SECT_ID
						WHERE CE.SECTION_ID is null and CE.ID < '.(intval($entry['ID']) + $BATCH_SIZE),
						false,
						"File: ".__FILE__."<br>Line: ".__LINE__
					);
				}

				$newStatus['steps'] = $newStatus['count'] - self::getTotalCount();

				Option::set('calendar', 'sectionStructureUpdaterStatus', serialize($newStatus));
				$result = array(
					'title' => Loc::getMessage("CALENDAR_UPDATE_STRUCTURE_TITLE"),
					'count' => $newStatus['count'],
					'steps' => $newStatus['steps']
				);
				return self::CONTINUE_EXECUTION;
			}

			Option::set('calendar', 'sectionStructureUpdaterStatus', serialize($newStatus));
		}

		Option::set('calendar', 'sectionStructureConverted', 'Y');
		Option::delete('calendar', ['name' => 'sectionStructureUpdaterStatus']);

		return self::FINISH_EXECUTION;
	}

	private function loadCurrentStatus()
	{
		$status = Option::get('calendar', 'sectionStructureUpdaterStatus', 'default');
		$status = ($status !== 'default' ? @unserialize($status, ['allowed_classes' => false]) : []);
		$status = (is_array($status) ? $status : []);

		if (empty($status))
		{
			$status = [
				'count' => self::getTotalCount(),
				'steps' => 0,
				'lastEventId' => 0,
				'finished' => false
			];
		}

		return $status;
	}

	private function getTotalCount()
	{
		global $DB;
		$count = 0;
		$res = $DB->Query('select count(*) as c  from b_calendar_event where SECTION_ID is null', false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res = $res->Fetch())
		{
			$count = intval($res['c']);
		}

		return $count;
	}
}