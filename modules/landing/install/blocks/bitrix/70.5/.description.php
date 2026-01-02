<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'widgets_image'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_CARD'),
			'label' => ['.landing-block-node-card-link'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_CARD_BUTTON'),
			'type' => 'link',
		],
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_IMG'),
			'type' => 'img',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-card-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_70_5_NODE_CARD_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family'],
			],
		],
	],
];

return $return;