<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-instagram/checkout',
	'name' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--CHECKOUT--NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--CHECKOUT--NAME"),
	'active' => \LandingSiteDemoComponent::checkActive([
		'ONLY_IN' => [],
		'EXCEPT' => ['ru'],
	]),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage("LANDING_DEMO_STORE_INSTAGRAM--CHECKOUT--NAME"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '1construction',
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
		'#block7139' => array(
			'code' => 'store.order',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-pb-0',
				),
			),
			'attrs' => array(),
		),
	),
);