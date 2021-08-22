<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(\Bitrix\Landing\Manager::getDocRoot() . '/bitrix/modules/landing/blocks/.components.php');

\CBitrixComponent::includeComponentClass('bitrix:landing.blocks.cmpfilter');

return [
	'block' => [
		'name' => Loc::getMessage('LNDBLCK_STORE_SECTIONS_CAROUSEL_NAME2'),
		'section' => ['store'],
		'type' => 'store',
		'html' => false,
		'subtype' => 'component',
		'subtype_params' => [
			'required' => 'catalog',
		],
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:catalog.section' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'PRICE_CODE' => [],
				],
			],
		],
	],
];
