<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$culture = \Bitrix\Main\Application::getInstance()->getContext()->getCulture();

return [
	'js' => '/bitrix/js/main/date/main.date.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
	'lang' => '/bitrix/modules/main/date_format.php',
	'lang_additional' => [
		'AMPM_MODE' => IsAmPmMode(true),
	],
	'settings' => [
		// all formats are based on current culture
		// examples in the comments below are from EN (USA), they will be different in different cultures
		'formats' => [
			// default date format, like '12/31/2019'
			'FORMAT_DATE' => $culture->get('FORMAT_DATE'),
			// default datetime format, like '12/31/2019 11:36:49 pm'
			'FORMAT_DATETIME' => $culture->get('FORMAT_DATETIME'),

			// like '12/31/2019'
			'SHORT_DATE_FORMAT' => $culture->get('SHORT_DATE_FORMAT'),
			// like 'Dec 31, 2019'
			'MEDIUM_DATE_FORMAT' => $culture->get('MEDIUM_DATE_FORMAT'),
			// like 'December 31, 2019'
			'LONG_DATE_FORMAT' => $culture->get('LONG_DATE_FORMAT'),
			// like 'December 31'
			'DAY_MONTH_FORMAT' => $culture->get('DAY_MONTH_FORMAT'),
			// like 'Dec 31'
			'DAY_SHORT_MONTH_FORMAT' => $culture->get('DAY_SHORT_MONTH_FORMAT'),
			// like 'Tue, December 31'
			'SHORT_DAY_OF_WEEK_MONTH_FORMAT' => $culture->get('SHORT_DAY_OF_WEEK_MONTH_FORMAT'),
			// like 'Tue, Dec 31'
			'SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT' => $culture->get('SHORT_DAY_OF_WEEK_SHORT_MONTH_FORMAT'),
			// like 'Tuesday, December 31'
			'DAY_OF_WEEK_MONTH_FORMAT' => $culture->get('DAY_OF_WEEK_MONTH_FORMAT'),
			// like 'Tuesday, December 31, 2019'
			'FULL_DATE_FORMAT' => $culture->get('FULL_DATE_FORMAT'),

			// like '2:05 pm'
			'SHORT_TIME_FORMAT' => $culture->get('SHORT_TIME_FORMAT'),
			// like '2:05:15 pm'
			'LONG_TIME_FORMAT' => $culture->get('LONG_TIME_FORMAT'),
		],
	],
];
