<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_NAME'),
		'section' => ['tiles'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		],
	],
	'style' => [
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'button',
		],
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'text-align',
		],
		'.landing-block-node-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_6_ONE_COL_FIX_TEXT_AND_BUTTON_CONTAINER'),
			'type' => ['container'],
		],
	],
];