<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		// 'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-NAME'),
		'section' => array('sidebar', 'menu'),
		'type' => ['knowledge', 'group'],
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => array(
			'source' => 'catalog',
		),
		'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'cards' => array(
		'.landing-block-node-menu-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-LINK'),
			'label' => array('.landing-block-node-menu-link-text'),
		),
	),
	'nodes' => array(
		'.landing-block-node-menu-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-LINK'),
			'type' => 'link',
			'group' => 'link',
			'skipContent' => true,
		),
		'.landing-block-node-menu-link-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-TEXT'),
			'type' => 'text',
			'group' => 'link',
			'allowInlineEdit' => false,
			'textOnly' => true,
		),
	),
	'style' => array(
		'block' => array(
			'type' => ['block-default', 'block-border']
		),
		'nodes' => array(
			'.landing-block-node-menu-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-LINK'),
				'type' => ['typo-simple', 'row-align']
			),
			'.landing-block-node-navbar' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-NAVBAR'),
				'type' => ['navbar-bg'],
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
);