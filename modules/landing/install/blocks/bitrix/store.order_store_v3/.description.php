<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_STORE_ORDER_NAME'),
		'section' => ['store'],
		'system' => true,
		'html' => false,
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:sale.order.checkout' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'SHOW_RETURN_BUTTON' => [
						'hidden' => true,
					],
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