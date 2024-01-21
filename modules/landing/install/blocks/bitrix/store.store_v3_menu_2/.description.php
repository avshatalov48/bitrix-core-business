<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_NAME_2'),
		'section' => ['store'],
		'dynamic' => false,
		'subtype' => ['menu', 'component'],
		'namespace' => 'bitrix',
	],
	'cards' => [
		'.landing-block-node-menu-top-item' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_ITEM'),
			'label' => ['.landing-block-node-menu-top-link'],
			'group_label' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_ITEMS_TOP_MSGVER_1'),
		],
		'.landing-block-node-menu-bottom-item' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_ITEM'),
			'label' => ['.landing-block-node-menu-bottom-text'],
			'group_label' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_ITEMS_BOTTOM_MSGVER_1'),
		],
	],
	'nodes' => [
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_TITLE'),
			'type' => 'link',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_LINK_TEXT_2'),
			'type' => 'text',
			'allowInlineEdit' => false,
			'textOnly' => true,
			'group' => 'phone',
		],
		'.landing-block-node-phone' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_LINK'),
			'type' => 'link',
			'skipContent' => true,
			'group' => 'phone',
		],
		'.landing-block-node-menu-top-link' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_LINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-bottom-link' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_LINK'),
			'type' => 'link',
			'group' => 'menu_link_bottom',
			'skipContent' => true,
		],
		'.landing-block-node-menu-bottom-text' => [
			'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_LINK'),
			'type' => 'text',
			'group' => 'menu_link_bottom',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-paddings', 'header-on-scroll', 'header-position'],
		],
		'nodes' => [
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LNDBLCK_STOREMENUV3_2_LINK_TEXT_2'),
				'type' => ['typo'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu', 'landing_header', 'landing_backlinks'],
	],
	'groups' => [
		'phone' => Loc::getMessage('LNDBLCK_STOREMENUV3_1_PHONE'),
	],
];