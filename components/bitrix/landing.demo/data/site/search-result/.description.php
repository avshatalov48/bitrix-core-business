<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);


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
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_COLOR' => 'site',
			'VIEW_USE' => 'Y',
			'UP_SHOW' => 'Y',
			'THEME_CODE' => '2business',
			'THEMEFONTS_CODE' => 'Open Sans',
			'THEMEFONTS_CODE_H' => 'Open Sans',
			'THEMEFONTS_SIZE' => '1.14286',
			'THEMEFONTS_USE' => 'Y',
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