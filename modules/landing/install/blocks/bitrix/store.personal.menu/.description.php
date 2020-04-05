<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_PM_NAME'),
		'section' => array('store'),
		'type' => 'store',
		'html' => false,
		'subtype' => 'menu',
		'subtype_params' => array(
			'selector' => '.landing-node-item-link',
			'source' => 'personal',
		),
	),
	'cards' => array(
		'.landing-node-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_PERSONAL_MENU_ITEM'),
			'label' => ['.landing-node-item-link'],
		),
	),
	'nodes' => array(
		'.landing-node-item-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_PERSONAL_MENU_ITEM'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('display', 'box'),
		),
		'nodes' => array(
			'.landing-node-item-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_PERSONAL_MENU_ITEM'),
				'type' => array('color', 'color-hover', 'typo'),
			),
		),
	),
);