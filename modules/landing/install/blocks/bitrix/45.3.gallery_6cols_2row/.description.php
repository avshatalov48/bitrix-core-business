<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_NAME'),
		'section' => ['image'],
		'dynamic' => false,
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-card-img'],
		],
	],
	'nodes' => [
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'allowInlineEdit' => false,
			'dimensions' => ['maxWidth' => 1350, 'maxHeight' => 900],
			'disableLink' => true,
			'create2xByDefault' => false,
		],
	],
	'style' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_45.3.GALLERY_6COLS_2ROW_CARDS_LANDINGBLOCKNODECARD'),
			'type' => 'animation',
		],
	],
	'assets' => [
		'ext' => ['landing_carousel', 'landing_gallery_cards'],
	],
];