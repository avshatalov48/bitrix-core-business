<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NAME'),
		'section' => ['menu'],
		'dynamic' => false,
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD'),
			'presets' => include __DIR__ . '/presets.php',
			'label' => [
				'.landing-block-node-card-icon',
				'.landing-block-node-card-contactlink-icon',
				'.landing-block-node-card-title',
				'.landing-block-node-menu-contactlink-title',
			],
			'group_label' => Loc::getMessage('LNDNGBLCK354_CARD_LABEL_1'),
		],
		'.landing-block-node-social-item' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_SOCIAL'),
			'label' => ['.landing-block-node-social-icon'],
			'presets' => include __DIR__ . '/presets_social.php',
			'group_label' => Loc::getMessage('LNDNGBLCK354_CARD_LABEL_2'),
		],
	],
	'groups' => [
		'logo' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODELOGO'),
		'contact' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD'),
	],
	'nodes' => [
		//		logo
		'.landing-block-node-logo' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODELOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => ['width' => 180, 'height' => 60],
		],
		'.landing-block-node-menu-logo-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_LINK'),
			'type' => 'link',
			'group' => 'logo',
		],

		//		contact-text
		'.landing-block-node-card-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-card-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-card-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		],

		//		contact-link
		'.landing-block-node-card-contactlink-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_LINK'),
			'type' => 'link',
			'group' => 'contact',
			'skipContent' => true,
		],
		'.landing-block-node-card-contactlink-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
			'group' => 'contact',
		],
		'.landing-block-node-menu-contactlink-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],
		'.landing-block-node-card-contactlink-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],

		//		social
		'.landing-block-node-social-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_LINK'),
			'type' => 'link',
		],
		'.landing-block-node-social-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_ICON'),
			'type' => 'icon',
		],
	],
	'style' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD'),
			'type' => 'border-colors',
		],
		'.landing-block-node-card-title-style' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'typo-link',
		],
		'.landing-block-node-card-text-style' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo-link',
		],
		'.landing-block-node-card-icon-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'color',
		],

		//		social
		'.landing-block-node-social-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_ICON'),
			'type' => ['color', 'color-hover', 'background-color', 'background-hover'],
		],
	],
];