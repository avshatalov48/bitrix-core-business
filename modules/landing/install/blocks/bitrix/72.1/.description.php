<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['text_image', 'widgets_text_image'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_BUTTON'),
			'type' => 'link',
		],
		'.landing-block-node-image' => [
			'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_IMG'),
			'type' => 'img',
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [
			'.landing-block-node-content' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_CONTAINER'),
				'type' => ['container-max-width'],
			],
			'.landing-block-node-containers' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_CONTAINERS'),
				'type' => ['align-items'],
			],
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_TITLE'),
				'type' => ['typo', 'margin-top'],
			],
			'.landing-block-node-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_BUTTON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-color-hover', 'font-family', 'margin-top', 'margin-bottom'],
			],
			'.landing-block-node-image' => [
				'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_72_1_NODE_IMG'),
				'type' => ['border-radius'],
			],
		],
	],
];

return $return;