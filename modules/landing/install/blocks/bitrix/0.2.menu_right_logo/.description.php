<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		// 'name' => Loc::getMessage('LNDBLCK_MENU_0_2-NAME'),
		'section' => 'menu',
		'dynamic' => false,
		'subtype' => 'menu',
		'subtype_params' => [
			'selector' => '.landing-block-node-menu-list-item-link',
			'count' => 5,
			'source' => 'catalog'
		],
	],
	'cards' => [
		'.landing-block-node-menu-list-item' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_2-MENU_LINK'),
			'label' => ['.landing-block-node-menu-list-item-link']
		],
	],
	'nodes' => [
		'.landing-block-node-menu-list-item-link' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_2-MENU_LINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-logo' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_2-LOGO'),
			'type' => 'img',
			'dimensions' => ['maxWidth' => 180, 'maxHeight' => 60],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-paddings', 'header-on-scroll', 'header-position'],
		],
		'nodes' => [
			'.landing-block-node-menu-list-item-link' => [
				'name' => Loc::getMessage('LNDBLCK_MENU_0_2-MENU_LINK'),
				'type' => ['typo-simple'],
			],
			'.navbar' => [
				'name' => Loc::getMessage('LNDBLCK_MENU_0_2-MENU'),
				'type' => ['navbar'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu', 'landing_header'],
	],
];