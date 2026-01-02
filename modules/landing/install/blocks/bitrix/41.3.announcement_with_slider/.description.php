<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NAME'),
		'section' => ['cover'],
		'dynamic' => false,
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-card-img'],
		],
	],
	'nodes' => [
		'.landing-block-node-bgimg' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'useInDesigner' => false,
			'create2xByDefault' => false,
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		],
		'.landing-block-node-date-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEDATEICON2'),
			'type' => 'icon',
		],
		'.landing-block-node-date-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEDATETITLE2'),
			'type' => 'text',
		],
		'.landing-block-node-date-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEDATETEXT2'),
			'type' => 'text',
		],
		'.landing-block-node-place-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEPLACEICON2'),
			'type' => 'icon',
		],
		'.landing-block-node-place-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEPLACETITLE2'),
			'type' => 'text',
		],
		'.landing-block-node-place-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEPLACETEXT2'),
			'type' => 'text',
		],
		'.landing-block-node-button' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBUTTON'),
			'type' => 'link',
		],
		'.landing-block-node-card-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODECARDIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1000, 'height' => 667],
			'allowInlineEdit' => false,
			'create2xByDefault' => false,
		],
		'.landing-block-node-block-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBLOCKTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-block-subtitle' => [
			'name' => Loc::getMessage(
				'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBLOCKSUBTITLE'
			),
			'type' => 'text',
		],
		'.landing-block-node-block-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBLOCKTEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['display', 'bg', 'paddings'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODETITLE'),
				'type' => ['typo', 'heading'],
			],
			'.landing-block-node-date-title' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEDATETITLE2'
				),
				'type' => 'typo',
			],
			'.landing-block-node-date-text' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEDATETEXT2'
				),
				'type' => 'typo',
			],
			'.landing-block-node-place-title' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEPLACETITLE2'
				),
				'type' => 'typo',
			],
			'.landing-block-node-place-text' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEPLACETEXT2'
				),
				'type' => 'typo',
			],
			'.landing-block-node-button' => [
				'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBUTTON'),
				'type' => 'button',
			],
			'.landing-block-node-block-title' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBLOCKTITLE'
				),
				'type' => ['typo', 'heading'],
			],
			'.landing-block-node-block-subtitle' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBLOCKSUBTITLE'
				),
				'type' => 'typo',
			],
			'.landing-block-node-block-text' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBLOCKTEXT'
				),
				'type' => 'typo',
			],
			'.landing-block-node-bgimg' => [
				'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEBGIMG'),
				'type' => ['background'],
			],
			'.landing-block-node-date-icon-container' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEDATEICON2'
				),
				'type' => 'color',
			],
			'.landing-block-node-place-icon-container' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODEPLACEICON2'
				),
				'type' => 'color',
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage(
					'LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODE_CONTAINER'
				),
				'type' => 'animation',
			],
			'.landing-block-node-inner-block' => [
				'name' => Loc::getMessage('LANDING_BLOCK_41.2.ANNOUNCEMENT_WITH_SLIDER_NODES_LANDINGBLOCKNODE_BLOCK'),
				'type' => 'bg',
			],
			'.landing-block-slider' => [
				'additional' => [
					'name' => Loc::getMessage('LANDING_BLOCK_41_2_ANNOUNCEMENT_WITH_SLIDER_NODES_SLIDER'),
					'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show'],
				],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_carousel'],
	],
];