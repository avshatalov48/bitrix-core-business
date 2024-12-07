<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_32.2.IMG_ONE_BIG_NAME2'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['image', 'cover', 'recommended', 'widgets_image'],
		'dynamic' => false,
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_32.2.IMG_ONE_BIG_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1110],
			'create2xByDefault' => false,
		],
	],
	'style' => [
		'block' => [
			'type' => [
				'display',
				'background',
				'padding-top',
				'padding-bottom',
				'padding-left',
				'padding-right',
				'margin-top',
				'margin-bottom',
			],
		],
		'nodes' => [
			'.landing-block-node-img' => [
				'name' => Loc::getMessage('LANDING_BLOCK_32.2.IMG_ONE_BIG_NODES_LANDINGBLOCKNODEIMG'),
				'type' => 'animation',
			],
		],
	],
];