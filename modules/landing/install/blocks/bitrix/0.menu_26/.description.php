<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-NAME'),
		'section' => ['menu'],
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => [
			'source' => 'structure',
		],
		 'version' => '20.0.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'nodes' =>[
		'.landing-block-node-hamburger-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-NAVBAR'),
			'type' => 'text',
			'allowInlineEdit' => false,
			'useInDesigner' => false,
		],
	],
	'menu' => [
		'.landing-block-node-menu' => [
			'item' => '.landing-block-node-menu-item',
			'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-NAVBAR'),
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
					'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-LINK'),
					'type' => 'link',
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['display', 'bg', 'border-colors', 'header-on-scroll', 'header-position']
		],
		'nodes' => [
			'.landing-block-node-navbar' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-NAVBAR'),
				'type' => ['navbar-bg-color', 'navbar-collapse-bg'],
			],
			'.landing-block-node-menu-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-NAVBAR'),
				'type' => ['background-color', 'font-size'],
			],
			'.landing-block-node-hamburger-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_26-HAMBURGER'),
				'type' => ['color', 'typo-simple'],
			],
			'.landing-block-node-hamburger' => [
				'name' => Loc::getMessage('LANDING_BLOCK_MENU_26_HAMB'),
				'type' => ['hamburger-size', 'hamburger-animation'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu', 'landing_header'],
	],
];