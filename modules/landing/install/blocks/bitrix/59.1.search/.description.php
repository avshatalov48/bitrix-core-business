<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_59_1_NAME'),
		'dynamic' => false,
		'section' => array('other'),
		'type' => ['knowledge', 'group'],
		'subtype' => 'search',
		'subtype_params' => [
			'type' => 'form',
			'resultPage' => 'search-result',
		],
		'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_1_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_1_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-bgimage' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_1_BGIMAGE'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'isWrapper' => true,
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-background-height-vh'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_59_1_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_59_1_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-button-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_59_1_BUTTON'),
				'type' => ['button-color', 'color', 'color-hover'],
			],
			'.landing-block-node-input-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_59_1_INPUT'),
				'type' => ['background-color', 'background-hover'],
			],
			'.landing-block-node-input-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_59_1_TEXT'),
				'type' => ['color', 'color-hover'],
			],
		],
	],
	'attrs' => [
		'.landing-block-node-form' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_1-SEARCH_RESULT'),
			'attribute' => 'action',
			'type' => 'url',
			'allowedTypes' => [
				'landing',
			],
			'disableCustomURL' => true,
			'disallowType' => true,
			'disableBlocks' => true
		]
	]
];