<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return [
	'code' => 'empty-multipage',
	'name' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'mobile',
			'ROBOTS_USE' => 'N',
			'THEMEFONTS_CODE' => 'Open Sans',
			'THEMEFONTS_CODE_H' => 'Open Sans',
			'THEMEFONTS_SIZE' => '1.14286',
			'THEMEFONTS_USE' => 'Y',
			'COPYRIGHT_SHOW' => 'Y',
			'B24BUTTON_COLOR' => 'site',
			'UP_SHOW' => 'Y',
			'GMAP_USE' => 'N',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'BACKGROUND_USE' => 'N',
			'METAYANDEXVERIFICATION_USE' => 'N',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'METAGOOGLEVERIFICATION_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
		],
		'TITLE' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE-TITLE"),
		'LANDING_ID_INDEX' => 'empty-multipage/main',
		'LANDING_ID_404' => '0',
	],
	'layout' => [
		'code' => 'sidebar_left',
		'ref' => [
			1 => 'empty-multipage/sidebar',
		],
	],
	'folders' => [],
	'syspages' => [],
	'items' => [
		0 => 'empty-multipage/main',
		1 => 'empty-multipage/sidebar',
	],
];