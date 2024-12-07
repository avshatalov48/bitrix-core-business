<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_STORE.ORDER_NAME'),
		'section' => ['store'],
		'system' => true,
		'html' => false,
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:sale.order.ajax' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'SHOW_COUPONS' => [],
				],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_jquery'],
	],
];