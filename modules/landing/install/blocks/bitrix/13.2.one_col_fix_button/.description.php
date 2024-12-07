<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_BUTTON_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['title', 'recommended', 'widgets_text'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => [
				//button
				'button-color',
				'button-type',
				'button-size',
				'button-padding',
				'border-radius',
				'color',
				'color-hover',
				'font-family',
				'text-transform',
			],
		],
	],
];