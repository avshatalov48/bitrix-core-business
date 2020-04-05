<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_21_NAME'),
		'section' => array('menu'),
		'subtype' => 'menu',
		'subtype_params' => array(
			'source' => 'catalog',
		)
		//			'type' => 'store',
		//			'html' => false
	),
	'cards' => array(
		'.landing-block-node-menu-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_21_LINK'),
			'label' => array('.landing-block-node-menu-list-item-link'),
		),
	),
	'nodes' => array(
		'.landing-block-node-menu-list-item-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_21_LINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'block-default-wo-paddings'
		),
		'nodes' => array(
			'.landing-block-node-menu-list-item-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_0.MENU_21_LINK'),
				'type' => ['typo-simple']
			),
			'.navbar' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_21--NAVBAR'),
				'type' => ['navbar'],
			),
		),
	),
);