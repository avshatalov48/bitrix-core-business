<?php
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/
/** @global CUser $USER */
global $USER;

IncludeModuleLangFile(__FILE__);

if (!$USER->CanDoOperation('clouds_config'))
{
	return false;
}

$arMenu = [
	'parent_menu' => 'global_menu_settings',
	'section' => 'clouds',
	'sort' => 1650,
	'text' => GetMessage('CLO_MENU_ITEM'),
	'title' => GetMessage('CLO_MENU_TITLE'),
	'url' => 'clouds_storage_list.php?lang=' . LANGUAGE_ID,
	'more_url' => [
		'clouds_storage_list.php',
		'clouds_storage_edit.php',
		'clouds_duplicates_list.php',
	],
	'icon' => 'clouds_menu_icon',
	'page_icon' => 'clouds_page_icon',
];

return $arMenu;
