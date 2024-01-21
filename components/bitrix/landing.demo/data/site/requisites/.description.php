<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);


return [
	'code' => 'requisites',
	'name' => Loc::getMessage("LANDING_DEMO_SITE_REQUISITES_NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_SITE_REQUISITES_DESCRIPTION"),
	'active' => Manager::isB24(),
	'show_in_list' => 'N',
	'type' => 'page',
	'sort' => 10,
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_SITE_REQUISITES_NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_SITE_REQUISITES_DESCRIPTION'),
			'VIEW_USE' => 'Y',
			'VIEW_TYPE' => 'mobile',
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_POSITION' => 'center',
			'THEMEFONTS_USE' => 'Y',
			'THEMEFONTS_CODE_H' => 'Montserrat',
			'THEMEFONTS_CODE' => 'Montserrat',
			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_COLOR' => '#99999',
			'THEMEFONTS_COLOR_H' => '#111111',
			'THEMEFONTS_LINE_HEIGHT' => '1.6',
			'THEMEFONTS_FONT_WEIGHT' => '400',
			'THEMEFONTS_FONT_WEIGHT_H' => '400',
			'THEME_CODE' => 'gym',
			'BACKGROUND_PICTURE' => 'https://cdn.bitrix24.site/bitrix/images/landing/business-card-website.jpg',
		]
	],
	'layout' => [],
	'syspages' => [],
	'folders' => [],
	'items' => [
		0 => 'requisites/main',
	],
];