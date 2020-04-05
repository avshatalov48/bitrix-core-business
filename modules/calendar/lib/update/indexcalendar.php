<?php

namespace Bitrix\Calendar\Update;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;


final class IndexCalendar extends Stepper
{
	const PORTION = 40;

	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		if (Loader::includeModule("calendar")
			&& Option::get('calendar', 'needEventIndex', 'Y') === 'N')
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
			'sectionFinished' => $status['sectionFinished'],
			'finished' => $status['finished']
		);

		// 1. Update sections
		if (!$status['sectionFinished'])
		{
			$sections = \CCalendarSect::GetList(array(
				'arFilter' => array(
					'>ID' => $status['sectionLastId']
				),
				'arOrder' => array('ID' => 'asc'),
				'checkPermissions' => false,
				'getPermissions' => false,
				'limit' => self::PORTION
			));

			foreach ($sections as $section)
			{
				// 1. Replace colors
				$color = self::getNewColor($section['COLOR']);
				if (strtolower($color) != strtolower($section['COLOR']))
				{
					\CCalendarSect::Edit(array(
						'arFields' => array(
							'ID' => $section['ID'],
							'COLOR' => $color
						)
					));
				}
				$newStatus['sectionLastId'] = $section['ID'];
				$newStatus['steps']++;
			}


			if (!empty($newStatus['sectionLastId']))
			{
				Option::set('calendar', 'eventindex', serialize($newStatus));
				$result = array(
					'title' => Loc::getMessage("CALENDAR_INDEX_TITLE"),
					'count' => $newStatus['count'],
					'steps' => $newStatus['steps']
				);


				return self::CONTINUE_EXECUTION;
			}

			$newStatus['sectionFinished'] = true;
			Option::set('calendar', 'eventindex', serialize($newStatus));
		}

		// 2. Update events
		$events = \CCalendarEvent::GetList(array(
				'arFilter' => array(
					'>ID' => $status['eventLastId'],
					'DELETED' => false
				),
				'arOrder' => array('ID' => 'asc'),
				'fetchAttendees' => true,
				'parseRecursion' => false,
				'checkPermissions' => false,
				//'getUserfields' => false,
				'fetchSection' => true,
				'limit' => self::PORTION
			)
		);

		foreach ($events as $event)
		{
			// 1. Replace colors
			$color = self::getNewColor($event['COLOR']);
			if (strtolower($color) != strtolower($event['COLOR']))
			{
				\CCalendarEvent::updateColor($event['ID'], $color);
			}

			// 2. Fill searchable content
			\CCalendarEvent::updateSearchIndex($event['ID'], array(
				'events' => array($event)
			));

			$newStatus['eventLastId'] = $event['ID'];
			$newStatus['steps']++;
		}

		if (!empty($newStatus['eventLastId']))
		{
			Option::set('calendar', 'eventindex', serialize($newStatus));
			$result = array(
				'title' => Loc::getMessage("CALENDAR_INDEX_TITLE"),
				'count' => $newStatus['count'],
				'steps' => $newStatus['steps']
			);
			return self::CONTINUE_EXECUTION;
		}

		Option::set('calendar', 'needEventIndex', 'N');
		Option::delete('calendar', array('name' => 'eventindex'));

		return self::FINISH_EXECUTION;
	}

	private function loadCurrentStatus()
	{
		$status = Option::get('calendar', 'eventindex', 'default');
		$status = ($status !== 'default' ? @unserialize($status) : array());
		$status = (is_array($status) ? $status : array());

		if (empty($status))
		{
			$status = array(
				'count' => self::getTotalCount(),
				'steps' => 0,

				'sectionLastId' => 0,
				'eventLastId' => 0,

				'sectionFinished' => false,
				'finished' => false
			);
		}

		return $status;
	}

	private function getTotalCount()
	{
		Loader::includeModule("calendar");
		return \CCalendarEvent::GetCount() + \CCalendarSect::GetCount();
	}

	public function getNewColor($color)
	{
		$color = strtolower($color);
		$colorTable = array(
			// Biege
			'#daa187' => '#af7e00',
			'#b47153' => '#af7e00',
			// blue
			'#78d4f1' => '#2fc6f6',
			'#2fc7f7' => '#2fc6f6',
			// gray
			'#c8cdd3' =>'#a8adb4',
			'#a7abb0' =>'#a8adb4',
			// turquoise
			'#43dad2' => '#47e4c2',
			'#04b4ab' => '#47e4c2',
			// orange
			'#eece8f' => '#ffa900',
			'#ffa801' => '#ffa900',
			// blue2
			'#5cd1df' => '#56d1e0',
			'#aee5ec' => '#56d1e0',
			// violet
			'#b6a5f6' => '#9985dd',
			'#6e54d1' => '#9985dd',
			// red
			'#f0b1a1'  => '#f87396',
			'#f73200'  => '#f87396',
			'#ee9b9a'  => '#f87396',
			'#fe5957' => '#f87396',
			// green
			'#82dc98' => '#9dcf00',
			'#29ad49' => '#9dcf00',
			'#cee669' => '#9dcf00'
		);
		if ($color && isset($colorTable[$color]))
			return $colorTable[$color];
		return $color;
	}
}