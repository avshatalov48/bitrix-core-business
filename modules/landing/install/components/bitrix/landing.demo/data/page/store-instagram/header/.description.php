<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'store-instagram',
	'code' => 'store-instagram/header',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--HEADER--NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--HEADER--NAME'),
	'active' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--HEADER--NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '1construction',
			'THEME_CODE_TYPO' => '3corporate',
		),
	),
	'layout' => array(),
	'items' => array(
		'#block7864' => array(
			'code' => '0.menu_07_construction',
			'cards' => array(
				'.landing-block-node-menu-list-item' => 2,
			),
			'nodes' => array(
				'.landing-block-node-menu-list-item-link' => array(
					0 => array(
						'text' => 'Home',
						'href' => '#system_mainpage',
						'target' => '_self',
					),
					1 => array(
						'text' => 'Our instagram',
						'href' => 'https://instagram.com',
						'target' => '_blank',
					),
				),
				'.landing-block-node-menu-logo-link' => array(
					0 => array(
						'text' => '
					<img class="landing-block-node-menu-logo u-header__logo-img u-header__logo-img--main g-max-width-180" src="https://cdn.bitrix24.site/bitrix/images/landing/logos/instagram-logo.png" alt="" data-fileid="7238" />',
						'href' => '#system_mainpage',
						'target' => '_self',
					),
				),
				'.landing-block-node-menu-logo' => array(
					0 => array(
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/instagram-logo.png',
					),
				),
			),
			'style' => array(
				'.landing-block-node-menu-list-item-link' => array(
					0 => 'landing-block-node-menu-list-item-link nav-link p-0',
					1 => 'landing-block-node-menu-list-item-link nav-link p-0',
				),
				'.navbar' => array(
					0 => 'navbar navbar-expand-lg g-py-0 u-navbar-color-black u-navbar-align-right',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-block-menu g-bg-white u-header u-header--floating u-header--floating-relative g-z-index-9999',
				),
			),
		),
	),
);