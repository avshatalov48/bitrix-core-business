<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '227',
	'parent' => 'empty-multipage',
	'code' => 'empty-multipage/main',
	'name' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE_MAIN-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'active' => false,
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE_MAIN-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE_MAIN-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE_MAIN-DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE_MAIN-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_EMPTY_MULTIPAGE_MAIN-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/empty/preview.jpg',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
		],
	],
	'layout' => [],
	'items' => [],
];
