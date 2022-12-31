<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME'),
		'section' => ['text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_CARD'),
			'label' => ['.landing-block-faq-visible-text'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-visible-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_TITLE'),
			'type' => ['typo', 'animation', 'heading'],
		],
		'.landing-block-faq-visible' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_ELEMENTS'),
			'type' => ['color', 'color-hover', 'border-color'],
		],
		'.landing-block-faq-visible-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_TITLE'),
			'type' => ['typo', 'color-hover', 'border-color'],
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_TEXT'),
			'type' => ['typo'],
		],
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_3_NAME_CARD'),
			'type' => ['border-color'],
		],
	],
	'assets' => [
		'ext' => ['landing_faq'],
	],
];