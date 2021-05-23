<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return [
	'name' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_DESCRIPTION'),
	//'timestamp' => 0,
	'designed_by' => [
		'NAME' => Loc::getMessage('LANDING_DEMO_KRAYT_BUSINESS_KRAYT'),
		'URL' => (LANGUAGE_ID == 'ru')
				? 'https://krayt.site/?utm_source=app'
				: 'https://krayt.site/en/?utm_source=app'
	],
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEMEFONTS_CODE' => 'g-font-open-sans',
'THEMEFONTS_CODE_H' => 'g-font-open-sans',
'THEMEFONTS_SIZE' => '1.14286',
'THEMEFONTS_USE' => 'Y',
			'UP_SHOW' => 'Y',
		],
	],
	'items' => [
		0 => 'krayt-business',
	],
];