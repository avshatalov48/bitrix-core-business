<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_NAME_3'),
		'section' => ['store'],
		'dynamic' => false,
		'subtype' => ['menu', 'component'],
		'namespace' => 'bitrix',
	],
	'cards' => [
		'.landing-block-node-section-menu-item' => [
			'name' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_NODES_MENU_ITEM'),
			'label' => ['.landing-block-node-menu-link'],
			'group_label' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_TOP'),
		],
		'.landing-block-node-section-menu-item-custom' => [
			'name' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_NODES_MENU_ITEM'),
			'label' => ['.landing-block-node-menu-link-custom'],
			'group_label' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_BOTTOM'),
		],
	],
	'nodes' => [
		'.landing-block-node-menu-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_LINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-link-custom' => [
			'name' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_LINK'),
			'type' => 'link',
		],
	],
	'style' => [
		'block' => [
			'type' => ['display', 'header-on-scroll', 'header-position', 'margin-top', 'margin-bottom'],
		],
		'nodes' => [
			'.landing-block-node-menu-link-custom' => [
				'name' => Loc::getMessage('LANDING_BLOCK_STORE_MENU_SIDEBAR_LINK'),
				'type' => ['typo'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_header_sidebar'],
	],
];