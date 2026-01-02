<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['tiles', 'widgets_tiles'],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-button-left-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_BUTTON'),
			'type' => 'text',
		],
		'.landing-block-node-button-left' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
		'.landing-block-node-button-right-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_BUTTON'),
			'type' => 'text',
		],
		'.landing-block-node-button-right' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_LINK'),
			'type' => 'link',
			'skipContent' => true,
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_CONTAINER'),
				'type' => ['background-color', 'paddings'],
			],
			'.landing-block-node-button-left-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family'],
			],
			'.landing-block-node-button-right-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_11_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family'],
			],
		],
	],
];

return $return;