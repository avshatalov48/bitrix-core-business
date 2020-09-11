<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_MENU_25-NAME'),
		'section' => array('sidebar', 'menu'),
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => array(
			'source' => 'structure',
		),
		'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'menu' => [
		'.landing-block-node-menu' => [
			'item' => '.landing-block-node-menu-item',
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_25-NAVBAR'),
			'root' => [
				'ulClassName' => 'landing-block-node-menu navbar-nav g-menu-multilevel js-scroll-nav flex-column list-unstyled w-100 g-mb-0',
				'liClassName' => 'landing-block-node-menu-item nav-item',
				'aClassName' => 'landing-block-node-link nav-link g-text-decoration-none--hover g-py-12 g-px-5 d-block g-brd-bottom g-brd-gray-light-v3 g-brd-1',
			],
			'children' => [
				'ulClassName' => 'landing-block-node-list navbar-nav g-menu-sublevel g-pl-20 js-scroll-nav flex-column list-unstyled w-100 g-mb-0',
				'liClassName' => 'landing-block-node-menu-item nav-item',
				'aClassName' => 'landing-block-node-link nav-link g-text-decoration-none--hover g-py-12 g-px-5 d-block g-brd-bottom g-brd-gray-light-v3 g-brd-1',
			],
			'nodes' => [
				'.landing-block-node-link' => [
					'name' => Loc::getMessage('LANDING_BLOCK_MENU_25-LINK'),
					'type' => 'link',
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-navbar' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_25-NAVBAR'),
				'type' => ['navbar-color', 'navbar-color-hover', 'typo-simple'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu'],
	],
];