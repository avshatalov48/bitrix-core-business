<?php

namespace Bitrix\Calendar\Core\Event\Tools;

use Bitrix\Calendar\Core\Event\Properties\Remind;

class Dictionary
{
	public const REMIND_UNIT = [
		'min' => Remind::UNIT_MINUTES,
		'minutes' => Remind::UNIT_MINUTES,
		'date' => Remind::UNIT_DATES,
		'day' => Remind::UNIT_DAYS,
		'days' => Remind::UNIT_DAYS,
		'daybefore' => Remind::UNIT_DAY_BEFORE,
		'hour' => Remind::UNIT_HOURS,
		'hours' => Remind::UNIT_HOURS
	];

	public const INTERVAL_FORMAT = [
		Remind::UNIT_MINUTES => '%i',
		Remind::UNIT_SECONDS => '%s',
		Remind::UNIT_HOURS => '%h',
		Remind::UNIT_MONTHS => '%m',
		Remind::UNIT_DAYS => '%d',
		Remind::UNIT_YEARS => '%y',
	];

	public const EVENT_TYPE = [
		'shared' => '#shared#',
		'shared_crm' => '#shared_crm#',
		'resource_booking' => '#resourcebooking#'
	];

	public const MEETING_STATUS = [
		'Yes' => 'Y',
		'No' => 'N',
		'Question' => 'Q',
		'Host' => 'H'
	];

	public const CALENDAR_TYPE = [
		'user' => 'user',
		'group' => 'group',
		'company' => 'company_calendar',
		'location' => 'location',
		'resource' => 'resource',
	];
}
