<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NAME'),
		'section' => 'menu',
		'subtype' => 'menu',
		'subtype_params' => array(
			'selector' => '.landing-block-node-menu-list-item-link',
			'count' => 5,
			'source' => 'catalog',
		),
		'version' => '18.4.0',
	),
	'cards' => array(
		'.landing-block-node-menu-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'label' => array('.landing-block-node-menu-list-item-link'),
		),
		'.landing-block-card-social' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULIST_SOCIAL_ITEM'),
			'label' => array('.landing-block-card-social-icon'),
			'presets' => include __DIR__ . '/presets_social.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-menu-list-item-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'type' => 'link',
		),
		'.landing-block-card-social-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULIST_SOCIAL_ITEMICON'),
			'type' => 'icon',
		),
		'.landing-block-card-social-icon-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULIST_SOCIAL_ITEMLINK'),
				'type' => 'link',
			),
		'.landing-block-node-menu-logo-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGOLINK'),
			'type' => 'link',
			'group' => 'logo',
		),
		'.landing-block-node-menu-logo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => array('width' => 180, 'height' => 60),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-paddings'),
		),
		'nodes' => array(
			'.landing-block-node-menu-list-item-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULISTITEMLINK'),
				'type' => ['typo-simple'],
			),
			'.navbar' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULIST'),
				'type' => ['navbar'],
			),
			'.landing-block-card-social-icon-link' => array(
					'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULIST_SOCIAL_ITEMLINK'),
					'type' => ['color', 'color-hover', 'background-color', 'background-hover']
				),
		),
	),
	'groups' => array(
		'logo' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
);