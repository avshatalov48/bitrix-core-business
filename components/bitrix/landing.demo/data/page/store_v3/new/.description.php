<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/new',
	//'name' => Loc::getMessage('LANDING_DEMO_STORE_NEW_NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => '(item)/([^/]+)',
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => 'photography',
		],
	],
	'layout' => [
		'code' => 'header_footer',
		'ref' => [
			1 => 'store_v3/header2',
			2 => 'store_v3/footer',
		],
	],
	'items' => [
		0 => [
			'code' => 'store.catalog.sections_carousel',
			'style' => [],
			'attrs' => [],
		],
	],
];