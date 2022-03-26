<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NAME2'),
		'section' => ['image', 'cover', 'recommended'],
		'dynamic' => false,
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1920],
			'create2xByDefault' => false,
		],
	],
	'style' => [
		'block' => [
			'type' => ['display', 'padding-left', 'padding-right', 'bg'],
		],
		'nodes' => [
			'.landing-block-node-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32.2.2.IMG_ONE_BIG_FULL__NODES_LANDINGBLOCKNODEIMG'),
				'type' => ['animation'],
			]
		],
	],
];