<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Block;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$canUseSearch = Loader::includeModule('search') || Block::checkComponentExists('bitrix:search.title');

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NAME_NEW'),
		'section' => ['menu'],
		'dynamic' => false,
		'type' => $canUseSearch ? 'store' : 'null',
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCK_CARD'),
			'presets' => include __DIR__ . '/presets.php',
			'label' => [
				'.landing-block-node-card-icon',
				'.landing-block-node-card-contactlink-icon',
				'.landing-block-node-card-title',
				'.landing-block-node-menu-contactlink-title',
			],
		],
	],
	'nodes' => [
//		logo
		'.landing-block-node-logo' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODELOGO'),
			'type' => 'img',
			'dimensions' => ['width' => 180, 'height' => 60],
			'group' => 'logo',
		],
		'.landing-block-node-menu-logo-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODELINK'),
			'type' => 'link',
			'group' => 'logo',
		],

//		contact-text
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],

//		contact-link
		'.landing-block-node-card-contactlink-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODELINK'),
			'type' => 'link',
			'group' => 'contact',
			'skipContent' => true,
		],
		'.landing-block-node-card-contactlink-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
			'group' => 'contact',
		],
		'.landing-block-node-menu-contactlink-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],
		'.landing-block-node-card-contactlink-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],

//		search
		'bitrix:search.title' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'PAGE' => [
						'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_SEARCH_PAGE'),
						'type' => 'url',
						'disallowType' => true,
					],
				],
			],
		],
	],

	'style' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCK_CARD'),
			'type' => 'border-colors',
		],
		'.landing-block-node-card-title-style' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-card-text-style' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-card-icon-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.5.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'color',
		],
	],
];