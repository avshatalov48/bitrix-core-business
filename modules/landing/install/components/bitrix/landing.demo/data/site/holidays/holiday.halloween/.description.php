<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
//	'code' => 'holiday.halloween',
	'name' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
//	'type' => 'page',
	'version' => 2,
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(10,10,10,31) ? 51 : -161,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'UP_SHOW' => 'Y',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'shipping',
			'THEME_CODE_TYPO' => 'shipping',
			'SETTINGS_HIDE_NOT_AVAILABLE' => 'L',
			'SETTINGS_HIDE_NOT_AVAILABLE_OFFERS' => 'N',
			'SETTINGS_PRODUCT_SUBSCRIPTION' => 'Y',
			'SETTINGS_USE_PRODUCT_QUANTITY' => 'Y',
			'SETTINGS_DISPLAY_COMPARE' => 'Y',
			'SETTINGS_PRICE_CODE' => array(
				0 => 'BASE',
			),
			'SETTINGS_USE_PRICE_COUNT' => 'N',
			'SETTINGS_SHOW_PRICE_COUNT' => 1,
			'SETTINGS_PRICE_VAT_INCLUDE' => 'Y',
			'SETTINGS_SHOW_OLD_PRICE' => 'Y',
			'SETTINGS_SHOW_DISCOUNT_PERCENT' => 'Y',
			'SETTINGS_USE_ENHANCED_ECOMMERCE' => 'Y',
			'COPYRIGHT_SHOW' => 'Y',
			'B24BUTTON_COLOR' => 'site',
			'GMAP_USE' => 'N',
			'GTM_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO___HALLOWEEN-TITLE"),
		'LANDING_ID_INDEX' => 'holiday.halloween',
		'LANDING_ID_404' => '0',
	),
	'layout' => array(),
	'folders' => array(),
	'syspages' => array(),
//	'items' => array(
//		0 => 'holiday.halloween',
//	),
);