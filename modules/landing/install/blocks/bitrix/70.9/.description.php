<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'widgets_image'],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_LINK'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_CONTAINER'),
				'type' => ['container-max-width'],
			],
			'.landing-block-node-content' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_CONTAINER'),
				'type' => ['padding-left', 'padding-right'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-link-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_CONTAINER'),
				'type' => ['padding-left', 'padding-right'],
			],
			'.landing-block-node-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_9_NODE_BUTTON'),
				'type' => ['background', 'color', 'background-hover', 'color-hover', 'font-family'],
			],
		],
	],
];

return $return;