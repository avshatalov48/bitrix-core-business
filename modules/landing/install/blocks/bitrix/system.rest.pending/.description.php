<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_SYSTEM_REST_PENDING_TITLE'),
		'namespace' => 'bitrix',
	],
	'nodes' => [
		'bitrix:landing.rest.pending' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'BLOCK_ID' => [
						'hidden' => true
					],
					'DATA' => [
						'hidden' => true
					],
					'REPLACE' => [
						'hidden' => true
					]
				]
			]
		]
	]
];
