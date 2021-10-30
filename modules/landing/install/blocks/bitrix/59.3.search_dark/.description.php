<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_59_3_NAME2'),
		'dynamic' => false,
		'section' => array('sidebar', 'other'),
		'type' => ['knowledge', 'group'],
		'subtype' => 'search',
		'subtype_params' => [
			'type' => 'form',
			'resultPage' => 'search-result3-dark'
		],
	],
	'style' => [
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_3_BUTTON'),
			'type' => ['background-color', 'background-hover', 'color', 'color-hover', 'font-family'],
		],
		'.landing-block-node-input-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_3_INPUT'),
			'type' => ['color', 'background-color', 'border-colors'],
		],
	],
	'attrs' => [
		'.landing-block-node-form' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_3_SEARCH_RESULT'),
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