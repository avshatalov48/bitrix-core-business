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
			'group_label' => Loc::getMessage('LNDNGBLCK16_CARD_LABEL_1'),
		],
		'.landing-block-card-menu-contact' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT'),
			'label' => [
				'.landing-block-node-menu-contact-img',
				'.landing-block-node-menu-contactlink-img',
				'.landing-block-node-menu-contact-title',
				'.landing-block-node-menu-contactlink-title',
			],
			'presets' => include __DIR__ . '/presets.php',
			'group_label' => Loc::getMessage('LNDNGBLCK16_CARD_LABEL_2'),
		],
		'.landing-block-card-social' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_CARD'),
			'label' => ['.landing-block-card-social-icon'],
			'presets' => include __DIR__ . '/presets_social.php',
			'group_label' => Loc::getMessage('LNDNGBLCK16_CARD_LABEL_3'),
		],
	],
	'nodes' => [
		'.landing-block-node-menu-logo' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => ['maxWidth' => 180, 'maxHeight' => 60],
		],
		'.landing-block-node-menu-logo-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGOLINK'),
			'type' => 'link',
			'group' => 'logo',
		],

		//		contact-text
		'.landing-block-node-menu-contact-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_ICON'),
			'type' => 'icon',
		],
		'.landing-block-node-menu-contact-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-menu-contact-value' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
			'type' => 'text',
		],

		//		contact-link
		'.landing-block-node-menu-contactlink-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENUCONTACT_LINK'),
			'type' => 'link',
			'group' => 'contact',
			'skipContent' => true,
		],
		'.landing-block-node-menu-contactlink-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_ICON'),
			'type' => 'icon',
			'group' => 'contact',
		],
		'.landing-block-node-menu-contactlink-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],
		'.landing-block-node-menu-contactlink-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],

		//		menu
		'.landing-block-node-menu-list-item-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'type' => 'link',
		],

		//		social
		'.landing-block-card-social-icon-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_LINK'),
			'type' => 'link',
		],
		'.landing-block-card-social-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_ICON'),
			'type' => 'icon',
		],
	],
	'style' => [
		'block' => [
			'type' => ['display', 'header-on-scroll', 'header-position'],
		],
		'nodes' => [
			'.landing-block-node-top-block' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODE_TOP_BLOCK'),
				'type' => ['bg'],
			],
			'.landing-block-node-bottom-block' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODE_BOTTOM_BLOCK'),
				'type' => ['bg'],
			],

			'.landing-block-card-menu-contact' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT'),
				'type' => ['border-color'],
			],

			//			contact text
			'.landing-block-node-menu-contact-title' => [
				//deprecated
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
				'type' => ['typo'],
			],
			'.landing-block-node-menu-contact-title-style' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
				'type' => ['typo-link'],
			],
			'.landing-block-node-menu-contact-value' => [
				//deprecated
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
				'type' => ['typo'],
			],
			'.landing-block-node-menu-contact-text-style' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
				'type' => ['typo-link'],
			],
			'.landing-block-node-menu-contact-img-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_ICON'),
				'type' => ['color'],
			],

			//			menu
			'.landing-block-node-menu-list-item-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULISTITEMLINK'),
				'type' => ['typo-simple'],
			],
			'.navbar' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULIST'),
				'type' => ['navbar'],
			],

			//			social
			'.landing-block-card-social-icon-link' => [
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_ICON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-hover'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu', 'landing_header'],
	],
	'groups' => [
		'logo' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
		'contact' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT'),
	],
];