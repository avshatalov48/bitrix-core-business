<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
	'code' => 'search-result',
	'section' => ['dynamic'],
	'name' => Loc::getMessage("LANDING_DEMO_SEARCH_RESULT-NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_SEARCH_RESULT-DESCRIPTION"),
	'active' => true,
	'publication' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	// 'sort' => 10,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'B24BUTTON_COLOR' => 'site',
			'VIEW_USE' => 'Y',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '2business',
			'THEME_CODE_TYPO' => 'app',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO_SEARCH_RESULT-NAME"),
	),
	'items' => array(
	),
);