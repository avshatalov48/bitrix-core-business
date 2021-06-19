<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_STORE_SALESCENTER_PAYMENT_PAY_STORE_V3_NAME_2'),
		'section' => ['store'],
		'type' => 'null',
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:salescenter.payment.pay' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					// visual
					'TEMPLATE_MODE' => [
						'style' => true,
					],
					'ALLOW_PAYMENT_REDIRECT' => [
						'style' => true,
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [],
	],
];


return $return;