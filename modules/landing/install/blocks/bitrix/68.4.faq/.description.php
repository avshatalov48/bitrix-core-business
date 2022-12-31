<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4'),
		'section' => ['text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_CARD'),
			'label' => ['.landing-block-faq-visible'],
		],
	],
	'nodes' => [
		'.landing-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-visible' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default'],
		],
		'nodes' => [
			'.landing-block-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_TITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-faq-visible' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_TITLE'),
				'type' => ['typo', 'color-hover'],
			],
			'.landing-block-faq-hidden' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_4_TEXT'),
				'type' => ['typo', 'border-color'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_faq'],
	],
];