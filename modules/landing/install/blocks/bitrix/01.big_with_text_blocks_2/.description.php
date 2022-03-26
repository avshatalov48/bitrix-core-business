<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NAME'),
		'section' => ['cover'],
		'dynamic' => false,
	],
	'cards' => [
		'.landing-block-node-card-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_CARDS_LANDINGBLOCKNODECARDBLOCK'),
			'label' => ['.landing-block-node-card-bgimg', '.landing-block-node-card-title'],
		],
	],
	'nodes' => [
		'.landing-block-node-card-bgimg' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDBGIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
			'useInDesigner' => false,
			'create2xByDefault' => false,
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDTITLE'),
			'type' => 'text',

		],
		'.landing-block-node-card-label-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLABELTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-label-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLABELTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-label-title2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLABELTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-label-text2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLABELTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-link1' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLINK'),
			'type' => 'link',
		],
		'.landing-block-node-card-link2' => [
			'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLINK'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [
			'.landing-block-card-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01_BIG_WITH_TEXT_BLOCKS_2-CARD_CONTAINER'),
				'type' => ['row-align', 'align-self'],
			],
			'.landing-block-node-card-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDTITLE'),
				'type' => ['typo', 'animation', 'heading'],
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDTEXT'),
				'type' => ['typo', 'animation'],
			],
			'.landing-block-node-card-label-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDLABELTITLE'),
				'type' => ['typo', 'box', 'paddings'],
			],
			'.landing-block-node-card-label-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDLABELTEXT'),
				'type' => ['typo', 'box', 'paddings'],
			],
			'.landing-block-node-card-label-title2' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDLABELTITLE'),
				'type' => ['typo', 'box', 'paddings'],
			],
			'.landing-block-node-card-label-text2' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARDLABELTEXT'),
				'type' => ['typo', 'box', 'paddings'],
			],
			'.landing-block-node-card-buttons' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARD_BUTTONS'),
				'type' => ['animation', 'text-align'],
			],
			'.landing-block-node-card-buttons2' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_STYLE_LANDINGBLOCKNODECARD_BUTTONS'),
				'type' => ['animation', 'text-align'],
			],
			'.landing-block-node-card-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDLINK'),
				'type' => 'button',
			],
			'.landing-block-node-card-bgimg' => [
				'name' => Loc::getMessage('LANDING_BLOCK_01.BIG_WITH_TEXT_BLOCKS_2_NODES_LANDINGBLOCKNODECARDBGIMG'),
				'type' => ['background-overlay', 'height-vh', 'paddings'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];