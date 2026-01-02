<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service\ICloud;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use CDavICalendar;

class ICloudCalendar
{
	private CDavICalendar $calendar;
	private CalendarDataBuilder $calendarDataBuilder;

	public function __construct(string|array $content)
	{
		if (!self::isAvailable())
		{
			throw new SystemException('Module dav is not installed');
		}

		$this->calendar = new CDavICalendar($content);
		$this->calendarDataBuilder = new CalendarDataBuilder($this->calendar);
	}

	public function render(): string
	{
		return $this->calendar->render();
	}

	public function getCalendarData(): array
	{
		$data = [];

		if (!$this->calendar->getComponent())
		{
			return $data;
		}

		$events = $this->calendar->getComponents('VTIMEZONE', false);

		if (empty($events))
		{
			return $data;
		}

		$data['calendar-data'] = $this->calendarDataBuilder->build($events[0]);

		$eventsCount = count($events);

		if ($eventsCount > 1)
		{
			$data['excluded-calendar-data'] = [];

			for ($i = 1; $i < $eventsCount; $i++)
			{
				$data['excluded-calendar-data'][] = $this->calendarDataBuilder->build($events[$i]);
			}
		}

		return $data;
	}

	private static function isAvailable(): bool
	{
		return Loader::includeModule('dav');
	}
}
