<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
	//	'code' => 'holiday.thanksgiving',
	'name' => 'Thanksgiving Day',
	'description' => "It's the day to be thankful. It's the time to add more colors to the palette of your page.
Send your Thanksgiving wishes with special Bitrix24 Thanksgiving Template. Your clients will enjoy it!",
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => 'page',
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(11,01,11,30) ? 41 : -181,
	'version' => 2,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array('en'),
	)),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => 'real-estate',
			'THEME_CODE_TYPO' => 'real-estate',
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
		'TITLE' => 'Thanksgiving Day',
		'LANDING_ID_INDEX' => 'holiday.thanksgiving',
		'LANDING_ID_404' => '0',
	),
	'layout' => array(),
	'folders' => array(),
	'syspages' => array(),
);