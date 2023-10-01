<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NAME_NEW'),
		'section' => 'menu',
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => [
			'selector' => '.landing-block-node-menu-list-item-link',
			'count' => 5,
			'source' => 'catalog',
		],
		// old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'version' => '18.4.0',
	],
	'cards' => [
		'.landing-block-node-menu-list-item' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'label' => ['.landing-block-node-menu-list-item-link'],
			'group_label' => Loc::getMessage('LNDNGBLCK15_CARD_LABEL_1'),
		],
		'.landing-block-node-card-menu-contact' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENU_CARD_CONTACT'),
			'label' => ['.landing-block-node-menu-contact-title'],
			'presets' => include __DIR__ . '/presets.php',
			'group_label' => Loc::getMessage('LNDNGBLCK15_CARD_LABEL_2'),
		],
		'.landing-block-card-social' => [
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_15_SOCIAL_ITEM'),
			'label' => ['.landing-block-card-social-icon'],
			'presets' => include __DIR__ . '/presets_social.php',
			'group_label' => Loc::getMessage('LNDNGBLCK15_CARD_LABEL_3'),
		],
	],
	'nodes' => [
		'.landing-block-node-menu-contact-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACTTITLE_NEW'),
			'type' => 'text',
		],
		'.landing-block-node-menu-contact-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_15_NODE_MENUCONTACTLINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-contact-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT_TEXT'),
			'type' => 'text',
		],
		'.landing-block-card-social-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_15_SOCIAL_ITEMICON'),
			'type' => 'icon',
		],
		'.landing-block-card-social-icon-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_15_SOCIAL_ITEMLINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-list-item-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-logo-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGOLINK'),
			'type' => 'link',
			'group' => 'logo',
		],
		'.landing-block-node-menu-logo' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => ['maxWidth' => 180, 'maxHeight' => 60],
		],
	],
	'style' => [
		'block' => [
			'type' => ['display', 'header-on-scroll', 'header-position'],
		],
		'nodes' => [
			'.landing-block-node-top-block' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODE_TOP_BLOCK'),
				'type' => 'bg',
			],
			'.landing-block-node-bottom-block' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODE_BOTTOM_BLOCK'),
				'type' => 'bg',
			],
			'.landing-block-node-menu-contact-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACTTITLE_NEW'),
				'type' => 'typo',
			],
			'.landing-block-node-menu-contact-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_15_NODE_MENUCONTACTLINK'),
				'type' => 'typo-link',
			],
			'.landing-block-node-menu-contact-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT_TEXT'),
				'type' => 'typo',
			],
			'.landing-block-card-social-icon-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_15_SOCIAL_ITEMLINK'),
				'type' => ['color', 'color-hover', 'background-color', 'background-hover'],
			],
			'.landing-block-node-menu-list-item-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULISTITEMLINK'),
				'type' => ['typo-simple'],
			],
			'.navbar' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULIST'),
				'type' => ['navbar'],
			],
			'.landing-block-node-hamburger' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_1_STYLE_LANDINGBLOCKNODE_HAMB'),
				'type' => ['hamburger-size', 'hamburger-animation'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu', 'landing_header'],
	],
	'groups' => [
		'logo' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
	],

];