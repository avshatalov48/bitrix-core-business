<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-instagram/payment',
	'name' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--PAYMENT--NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--PAYMENT--NAME"),
	'active' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--PAYMENT--NAME"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '1construction',
			'THEME_CODE_TYPO' => '3corporate',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'store-instagram/header',
			2 => 'store-instagram/footer',
		),
	),
	'items' => array(
		'#block7140' => array(
			'code' => 'store.payment',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(),
			'attrs' => array(),
		),
	),
);