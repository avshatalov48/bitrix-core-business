<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NAME'),
		'section' => ['text'],
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-faq-visible'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-faq-visible' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_CARDS_LANDINGBLOCKNODECARD'),
			'type' => ['animation', 'border-color'],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODETITLE'),
			'type' => ['typo', 'animation', 'heading'],
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODETEXT'),
			'type' => ['typo', 'animation'],
		],
		'.landing-block-faq-visible' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODECARD_TITLE'),
			'type' => ['typo', 'color-hover'],
		],
		'.landing-block-faq-hidden' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FAQ_2_NODES_LANDINGBLOCKNODECARD_TEXT'),
			'type' => ['typo'],
		],
	],
	'assets' => [
		'ext' => ['landing_faq'],
	],
];