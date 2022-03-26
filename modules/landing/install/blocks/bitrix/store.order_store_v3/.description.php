<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_STORE_ORDER_NAME'),
		'section' => ['store'],
		'type' => 'null',
		'html' => false,
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:sale.order.checkout' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'SHOW_COUPONS' => [],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [],
	]
];