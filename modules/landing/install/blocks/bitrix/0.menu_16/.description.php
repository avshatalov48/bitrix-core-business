<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NAME_NEW'),
		'section' => 'menu',
		'subtype' => 'menu',
		'subtype_params' => array(
			'selector' => '.landing-block-node-menu-list-item-link',
			'count' => 5,
			'source' => 'catalog'
		),
		'version' => '18.4.0',
	),
	'cards' => array(
		'.landing-block-card-menu-contact' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT'),
			'label' => array(
				'.landing-block-node-menu-contact-img',
				'.landing-block-node-menu-contactlink-img',
				'.landing-block-node-menu-contact-title',
				'.landing-block-node-menu-contactlink-title'
			),
			'presets' => include __DIR__ . '/presets.php',
		),
		'.landing-block-card-social' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_CARD'),
			'label' => array('.landing-block-card-social-icon'),
			'presets' => include __DIR__ . '/presets_social.php',
		),
		'.landing-block-node-menu-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'label' => array('.landing-block-node-menu-list-item-link')
		),
	),
	'nodes' => array(
		'.landing-block-node-menu-logo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => array('width' => 180, 'height' => 60),
		),
		'.landing-block-node-menu-logo-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGOLINK'),
			'type' => 'link',
			'group' => 'logo',
		),

//		contact-text
		'.landing-block-node-menu-contact-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-menu-contact-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-menu-contact-value' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
			'type' => 'text',
		),
		
//		contact-link
		'.landing-block-node-menu-contactlink-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENUCONTACT_LINK'),
			'type' => 'link',
			'group' => 'contact',
			'skipContent' => true,
		),
		'.landing-block-node-menu-contactlink-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_ICON'),
			'type' => 'icon',
			'group' => 'contact',
		),
		'.landing-block-node-menu-contactlink-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		),
		'.landing-block-node-menu-contactlink-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		),
		
		
//		menu
		'.landing-block-node-menu-list-item-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'type' => 'link',
		),

		
//		social
		'.landing-block-card-social-icon-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_LINK'),
			'type' => 'link',
		),
		'.landing-block-card-social-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_ICON'),
			'type' => 'icon',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display'),
		),
		'nodes' => array(
			'.landing-block-node-top-block' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODE_TOP_BLOCK'),
				'type' => ['bg'],
			),
			'.landing-block-node-bottom-block' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODE_BOTTOM_BLOCK'),
				'type' => ['bg'],
			),
			
			'.landing-block-card-menu-contact' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT'),
				'type' => ['border-color']
			),
			
//			contact text
			'.landing-block-node-menu-contact-title' => array(
				//deprecated
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
				'type' => ['typo'],
			),
			'.landing-block-node-menu-contact-title-style' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TITLE'),
				'type' => ['typo'],
			),
			'.landing-block-node-menu-contact-value' => array(
				//deprecated
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
				'type' => ['typo'],
			),
			'.landing-block-node-menu-contact-text-style' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_TEXT'),
				'type' => ['typo'],
			),
			'.landing-block-node-menu-contact-img-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-CONTACT_ICON'),
				'type' => ['color'],
			),

//			menu
			'.landing-block-node-menu-list-item-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULISTITEMLINK'),
				'type' => ['typo-simple']
			),
			'.navbar' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULIST'),
				'type' => ['navbar'],
			),
			
//			social
			'.landing-block-card-social-icon-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0_MENU_16-SOCIAL_ICON'),
				'type' => ['color', 'color-hover', 'background-color', 'background-hover']
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
	'groups' => array(
		'logo' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
		'contact' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENUCONTACT'),
	),
);