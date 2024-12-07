<?

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$context = \Bitrix\Main\Context::getCurrent();
$locale = $context->getLanguageObject()?->getCode();
if (empty($locale))
{
	$locale = defined('LANGUAGE_ID') ? LANGUAGE_ID : 'en';
}

$firstWeekDay = $context->getCulture()->getWeekStart();
$weekends = [];
$holidays = [];
$workdays = [];

if (Loader::includeModule('calendar'))
{
	$calendarSettings = \CCalendar::GetSettings(['getDefaultForEmpty' => false]);
	$weekHolidays =
		isset($calendarSettings['week_holidays']) && is_array($calendarSettings['week_holidays'])
			? $calendarSettings['week_holidays']
			: []
	;

	foreach (['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'] as $index => $abbr)
	{
		if (in_array($abbr, $weekHolidays))
		{
			$weekends[] = $index;
		}
	}

	if (isset($calendarSettings['year_holidays']))
	{
		foreach (explode(',', $calendarSettings['year_holidays']) as $holiday)
		{
			$date = explode('.', trim($holiday));
			if (count($date) === 2 && !empty($date[0]) && !empty($date[1]))
			{
				$holidays[] = [(int)$date[0], (int)$date[1] - 1];
			}
		}
	}

	if (isset($calendarSettings['year_workdays']))
	{
		foreach (explode(',', $calendarSettings['year_workdays']) as $workday)
		{
			$date = explode('.', trim($workday));
			if (count($date) === 2 && !empty($date[0]) && !empty($date[1]))
			{
				$workdays[] = [(int)$date[0], (int)$date[1] - 1];
			}
		}
	}
}

return [
	'js' => 'dist/date-picker.bundle.js',
	'css' => 'dist/date-picker.bundle.css',
	'rel' => [
		'main.popup',
		'main.core.events',
		'main.date',
		'main.core',
		'main.core.cache',
		'ui.icon-set.actions',
	],
	'skip_core' => false,
	'settings' => [
		'locale' => $locale,
		'firstWeekDay' => $firstWeekDay,
		'weekends' => $weekends,
		'holidays' => $holidays,
		'workdays' => $workdays,
	],
];
