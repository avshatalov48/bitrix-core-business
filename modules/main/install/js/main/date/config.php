<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => '/bitrix/js/main/date/main.date.js',
	'lang' => '/bitrix/modules/main/date_format.php',
	'lang_additional' => array(
		'AMPM_MODE' => IsAmPmMode(true),
	),
];