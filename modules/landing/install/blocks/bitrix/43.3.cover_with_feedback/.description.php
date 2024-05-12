<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NAME'),
		'section' => ['feedback'],
		'type' => ['page', 'store', 'smn'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-card-photo', '.landing-block-node-card-name'],
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-photo' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
			'type' => 'img',
			'dimensions' => ['width' => 240, 'height' => 240],
			'create2xByDefault' => false,
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-card-name' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
			'type' => 'text',
		],
		'.landing-block-node-bgimg' => [
			'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'create2xByDefault' => false,
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'heading'],
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODETEXT'),
				'type' => 'typo',
			],
			'.landing-block-node-card-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'typo',
			],
			'.landing-block-node-card-name' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDNAME'),
				'type' => 'typo',
			],
			'.landing-block-node-bgimg' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => ['background'],
			],
			'.landing-block-node-header' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODEHEADER'),
				'type' => ['animation', 'heading'],
			],
			'.landing-block-node-card-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_CARDS_LANDINGBLOCKNODECARD'),
				'type' => 'animation',
			],
			'.landing-block-node-card-photo' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
				'type' => 'border-radius',
			],
			'.landing-block-img-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDPHOTO'),
				'type' => 'align-self',
			],
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_CARD'),
				'type' => 'align-self',
			],
			'.landing-block-text-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_43.3.COVER_WITH_FEEDBACK_NODES_LANDINGBLOCKNODECARDTEXT'),
				'type' => 'align-self',
			],
			'.landing-block-slider' => [
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_43_3_COVER_WITH_FEEDBACK_NODES_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show', 'arrows', 'dots'],
				]
			],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];