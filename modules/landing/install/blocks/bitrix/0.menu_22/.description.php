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
		'type' => ['knowledge', 'group'],
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => array(
			'source' => 'catalog',
		),
		'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'menu' => [
		'.landing-block-node-menu' => [
			'item' => '.landing-block-node-menu-item',
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_22-NAVBAR'),
			'root' => [
				'ulClassName' => 'landing-block-node-menu navbar-nav g-menu-multilevel flex-column list-unstyled js-scroll-nav',
				'liClassName' => 'landing-block-node-menu-item nav-item g-mb-14',
				'aClassName' => 'landing-block-node-menu-link justify-content-between u-link-v5 mb-3',
			],
			'children' => [
				'ulClassName' => 'landing-block-node-menu navbar-nav g-menu-sublevel g-ml-20 flex-column list-unstyled js-scroll-nav',
				'liClassName' => 'landing-block-node-menu-item nav-item g-mt-14',
				'aClassName' => 'landing-block-node-menu-link justify-content-between u-link-v5 mb-3',
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
				'type' => ['navbar'],
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_menu'),
	),
);