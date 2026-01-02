<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'widgets_columns'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-image' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_IMG'),
			'type' => 'img',
		],
		'.landing-block-node-tab' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_BADGE'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_CONTAINER'),
				'type' => ['background-color', 'padding-top', 'padding-bottom'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-tab' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_71_6_NODE_BADGE'),
				'type' => ['display-element', 'background-color', 'border-color', 'typo'],
			],
		],
	],
];

return $return;