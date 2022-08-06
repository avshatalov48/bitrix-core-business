<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FAQ_1'),
		'section' => ['text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_1_CARD'),
			'label' => ['.landing-block-faq-visible'],
		],
	],
	'nodes' => [
		'.landing-block-faq-visible' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_1_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_1_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default'],
		],
		'nodes' => [
			'.landing-block-faq-visible' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_1_TITLE'),
				'type' => ['typo', 'color-hover'],
			],
			'.landing-block-faq-hidden' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FAQ_1_TEXT'),
				'type' => ['typo'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_faq'],
	],
];