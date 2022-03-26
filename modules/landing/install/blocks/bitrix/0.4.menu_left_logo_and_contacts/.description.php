<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		// 'name' => Loc::getMessage('LNDBLCK_MENU_0_4-NAME'),
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
			'name' => Loc::getMessage('LNDBLCK_MENU_0_4-MENU_LINK'),
			'label' => ['.landing-block-node-menu-list-item-link'],
		],
		'.landing-block-node-contact-text' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_4-CONTACT_TEXT'),
			'label' => ['.landing-block-node-contact-text'],
			'presets' => include __DIR__ . '/presets.php',
			'group_label' => Loc::getMessage('LNDBLCK_MENU_0_4-CONTACTS_TEXTS'),
		],
		'.landing-block-node-contact-button' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_4-CONTACT_BUTTON'),
			'label' => ['.landing-block-node-contact-button'],
			'group_label' => Loc::getMessage('LNDBLCK_MENU_0_4-CONTACTS_BUTTONS'),
		],
		'.landing-block-node-contact-social' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_4-CONTACT_SOCIAL'),
			'label' => ['.landing-block-node-contacts-socials'],
		],
	],
	'nodes' => [
		'.landing-block-node-menu-list-item-link' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_4-MENU_LINK'),
			'type' => 'link',
		],
		'.landing-block-node-menu-logo' => [
			'name' => Loc::getMessage('LNDBLCK_MENU_0_4-LOGO'),
			'type' => 'img',
			// 'group' => 'logo',
			'dimensions' => ['maxWidth' => 180, 'maxHeight' => 60],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-paddings', 'header-on-scroll', 'header-position',  'padding-top', 'padding-bottom'],
		],
		'nodes' => [
			'.landing-block-node-menu-list-item-link' => [
				'name' => Loc::getMessage('LNDBLCK_MENU_0_4-MENU_LINK'),
				'type' => ['typo-simple'],
			],
			'.navbar' => [
				'name' => Loc::getMessage('LNDBLCK_MENU_0_4-MENU'),
				'type' => ['navbar', 'border-color'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_menu', 'landing_header'],
	],
];