<?php


namespace Bitrix\Calendar\Update;


use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionTable;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Update\Stepper;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;

final class ConverterObjectColor extends Stepper
{
	const PORTION = 100;

	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)

	{
		if (Loader::includeModule("calendar")
			&& Option::get('calendar', 'needChangeColor', 'Y') === 'N')
		{
			return self::FINISH_EXECUTION;
		}

		$status = $this->loadCurrentStatus();

		if ($status['finished'])
		{
			\CCalendar::ClearCache(['section_list', 'event_list']);

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
			$sections = SectionTable::getList([
				'filter' => [
					'>ID' => $status['sectionLastId'],
				],
				'limit' => self::PORTION,
				'order' => [
					'ID' => 'ASC',
				]
			])->fetchAll();

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
				Option::set('calendar', 'changecolor', serialize($newStatus));

				return self::CONTINUE_EXECUTION;
			}

			$newStatus['sectionFinished'] = true;
			Option::set('calendar', 'eventindex', serialize($newStatus));
		}

		// 2. Update events
		$events = EventTable::getList([
			'filter' => [
				'>ID' =>$status['eventLastId'] ?? null,
				'DELETED' => 'N',
			],
			'order' => [
				'ID' => 'ASC',
			],
			'limit' => self::PORTION,

		])->fetchAll();

		foreach ($events as $event)
		{
			// 1. Replace colors
			$color = self::getNewColor($event['COLOR']);
			if (strtolower($color) != strtolower($event['COLOR']))
			{
				\CCalendarEvent::updateColor($event['ID'], $color);
			}

			$newStatus['eventLastId'] = $event['ID'];
			$newStatus['steps']++;
		}

		if (!empty($newStatus['eventLastId']))
		{
			Option::set('calendar', 'changecolor', serialize($newStatus));

			return self::CONTINUE_EXECUTION;
		}

		Option::set('calendar', 'needChangeColor', 'N');
		Option::delete('calendar', array('name' => 'changecolor'));

		return self::FINISH_EXECUTION;
	}

	private function loadCurrentStatus()
	{
		$status = Option::get('calendar', 'changecolor', 'default');
		$status = ($status !== 'default' ? @unserialize($status, ['allowed_classes' => false]) : array());
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
			'#af7e00' => '#c3612c',
			// blue
			'#2fc6f6' => '#0092cc',
			// gray
			'#a8adb4' => '#838fa0',
			// turquoise
			'#47e4c2' => '#00b38c',
			// orange
			'#ffa900' => '#ffa900',
			// blue2
			'#56d1e0' => '#e89b06',
			// violet
			'#9985dd' => '#bd7ac9',
			// red
			'#f87396' => '#de2b24',
			// green
			'#9dcf00' => '#86b100',
		);
		if ($color && isset($colorTable[$color]))
			return $colorTable[$color];
		return $color;
	}
}