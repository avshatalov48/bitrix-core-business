<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_MENU_22-NAME'),
		'section' => array('sidebar', 'menu'),
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => array(
			'source' => 'structure',
		),
		'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'menu' => [
		'.landing-block-node-menu' => [
			'item' => '.landing-block-node-menu-item',
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_22-NAVBAR'),
			'root' => [
				'ulClassName' => 'landing-block-node-menu navbar-nav w-100 g-menu-multilevel flex-column list-unstyled js-scroll-nav',
				'liClassName' => 'landing-block-node-menu-item nav-item',
				'aClassName' => 'landing-block-node-menu-link nav-link u-link-v5 d-block g-pa-0 g-py-7',
			],
			'children' => [
				'ulClassName' => 'landing-block-node-menu navbar-nav g-menu-sublevel g-ml-20 flex-column list-unstyled js-scroll-nav',
				'liClassName' => 'landing-block-node-menu-item nav-item',
				'aClassName' => 'landing-block-node-menu-link nav-link u-link-v5 d-block g-pa-0 g-py-7',
			],
			'nodes' => [
				'.landing-block-node-menu-link' => [
					'name' => Loc::getMessage('LANDING_BLOCK_MENU_22-LINK'),
					'type' => 'link',
				],
			],
		],
	],
	'style' => array(
		'block' => array(
			'type' => ['block-default', 'block-border']
		),
		'nodes' => array(
			'.landing-block-node-navbar' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_22-NAVBAR'),
				'type' => ['navbar-color', 'navbar-color-hover', 'typo-simple'],
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
);