<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
	'name' => Loc::getMessage("LANDING_DEMO___XMAS-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO___XMAS-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'version' => 2,
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(12,1,12,25) ? 21 : -191,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
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
		'TITLE' => Loc::getMessage("LANDING_DEMO___XMAS-TITLE"),
		'LANDING_ID_INDEX' => 'holiday.xmas',
		'LANDING_ID_404' => '0',
	),
	'layout' => array(),
	'folders' => array(),
	'syspages' => array(),
);