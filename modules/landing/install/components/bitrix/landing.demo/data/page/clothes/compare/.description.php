<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/compare',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-COMPARE--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/compare/preview.jpg',
			'THEME_CODE' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'clothes/header',
			2 => 'clothes/footer',
		),
	),
	'items' => array(
		0 => array(
			'code' => 'store.compare',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(),
			'attrs' => array(),
		),
	),
);