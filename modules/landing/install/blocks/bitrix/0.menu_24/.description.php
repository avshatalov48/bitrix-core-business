<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-NAME'),
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
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-NAVBAR'),
			'root' => [
				'ulClassName' => 'landing-block-node-menu g-menu-multilevel w-100 navbar-nav flex-column list-unstyled js-scroll-nav',
				'liClassName' => 'landing-block-node-menu-item nav-item',
				'aClassName' => 'landing-block-node-menu-link nav-link row no-gutters align-items-center g-text-decoration-none--hover rounded g-mx-5 g-px-25 g-py-8 g-rounded-25',
			],
			'children' => [
				'ulClassName' => 'landing-block-node-menu navbar-nav g-menu-sublevel g-ml-20 flex-column list-unstyled js-scroll-nav',
				'liClassName' => 'landing-block-node-menu-item nav-item',
				'aClassName' => 'landing-block-node-menu-link nav-link row no-gutters align-items-center g-text-decoration-none--hover rounded g-mx-5 g-px-25 g-py-8 g-rounded-25',
			],
			'nodes' => [
				'.landing-block-node-menu-link' => [
					'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-LINK'),
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
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_24-NAVBAR'),
				'type' => ['navbar-color', 'navbar-bg', 'navbar-color-hover', 'navbar-bg-hover', 'navbar-marker', 'typo-simple'],
			),
			'.landing-block-node-hamburger' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_24_HAMB'),
				'type' => ['hamburger-size', 'hamburger-animation'],
			],
		),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
);