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
	'code' => 'search-result2',
	'section' => ['dynamic'],
	'name' => Loc::getMessage("LANDING_DEMO_SEARCH_RESULT2-NAME"),
	'description' => Loc::getMessage("LANDING_DEMO_SEARCH_RESULT2-DESCRIPTION"),
	'active' => true,
	'publication' => true,
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'B24BUTTON_COLOR' => 'site',
			'VIEW_USE' => 'Y',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '2business',
			'THEMEFONTS_CODE' => 'g-font-open-sans',
			'THEMEFONTS_CODE_H' => 'g-font-montserrat',
			'THEMEFONTS_SIZE' => '1.14286',
			'THEMEFONTS_USE' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
		),
		'TITLE' => Loc::getMessage("LANDING_DEMO_SEARCH_RESULT2-NAME"),
	),
	'items' => array(
	),
);