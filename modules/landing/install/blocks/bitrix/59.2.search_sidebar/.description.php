<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_59_2-NAME'),
		'dynamic' => false,
		'section' => array('sidebar', 'other', 'recommended'),
		'type' => ['knowledge', 'group'],
		'subtype' => 'search',
		'subtype_params' => [
			'type' => 'form',
			'resultPage' => 'search-result2'
		],
		'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'style' => [
		'.landing-block-node-button-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_2-BUTTON'),
			'type' => ['background-color', 'background-hover', 'color', 'color-hover'],
		],
		'.landing-block-node-input-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_2-INPUT'),
			'type' => ['color', 'background-color', 'border-colors'],
		],
	],
	'attrs' => [
		'.landing-block-node-form' => [
			'name' => Loc::getMessage('LANDING_BLOCK_59_2-SEARCH_RESULT'),
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