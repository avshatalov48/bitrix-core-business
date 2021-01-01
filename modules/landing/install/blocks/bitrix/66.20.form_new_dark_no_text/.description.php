<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDNGBLCK_66_20_NAME'),
		// 'section' => 'other',
		'dynamic' => false,
		'subtype' => 'embedform',
		// 'type' => ['page', 'store', 'smn'],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-bgimg' => [
			'name' => Loc::getMessage('LNDNGBLCK_66_20_BG'),
			'type' => 'img',
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'allowInlineEdit' => false,
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-background-overlay'],
		],
		'nodes' => [
			'.landing-block-node-bgimg' => [
				'name' => Loc::getMessage('LNDNGBLCK_66_20_BG'),
				'type' => 'background-attachment',
			],
		],
	],
	'assets' => [
	    'ext' => ['landing_form_embed'],
	],
];