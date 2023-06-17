<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5'),
		'section' => ['text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_CARD'),
			'label' => ['.landing-block-faq-title'],
		],
	],
	'nodes' => [
		'.landing-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'container'],
		],
		'nodes' => [
			'.landing-block-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_TITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-faq-visible' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_CARD'),
				'type' => ['background', 'border-color'],
			],
			'.landing-block-faq-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_TITLE'),
				'type' => ['typo', 'color-hover'],
			],
			'.landing-block-faq-hidden' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_COLUMN'),
				'type' => ['columns'],
			],
			'.landing-block-faq-icons' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_5_ICON'),
				'type' => ['background'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_faq'],
	],
];