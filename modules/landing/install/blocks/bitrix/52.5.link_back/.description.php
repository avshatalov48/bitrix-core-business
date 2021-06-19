<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_STORE_V3_LINK_BACK_NAME_2'),
		'section' => ['tiles'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_STORE_V3_LINK_BACK_TITLE'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_STORE_V3_LINK_BACK_TITLE'),
				'type' => ['typo'],
			],
		],
	],
];