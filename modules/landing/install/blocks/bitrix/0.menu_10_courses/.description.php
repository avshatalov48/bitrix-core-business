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
	),
	'cards' => array(
		'.landing-block-node-menu-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
		),
	),
	'nodes' => array(
		'.landing-block-node-menu-list-item-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULISTITEMLINK'),
			'type' => 'link',
		),
		//		todo: social
		//			'.landing-block-node-menu-list-social' =>
		//				array(
		//					'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULIST_SOCIAL'),
		//					'type' => 'ul',
		//				),
		//			'.landing-block-node-menu-list-social-item-link' =>
		//				array(
		//					'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULIST_SOCIAL_ITEMLINK'),
		//					'type' => 'link',
		//				),
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
			'type' => array('display', 'background-color', 'background-gradient'),
		),
		'nodes' => array(
			'.landing-block-node-menu-list-item-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_STYLE_LANDINGBLOCKNODEMENULISTITEMLINK'),
					'type' => 'typo',
				),
		),
		'groups' => array(
			'logo' => Loc::getMessage('LANDING_BLOCK_0.MENU_1_NODES_LANDINGBLOCKNODEMENULOGO'),
		),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
);