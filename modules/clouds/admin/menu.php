<?
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/
IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation("clouds_config"))
	return false;

$arMenu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "clouds",
	"sort" => 1650,
	"text" => GetMessage("CLO_MENU_ITEM"),
	"title" => GetMessage("CLO_MENU_TITLE"),
	"url" => "clouds_storage_list.php?lang=".LANGUAGE_ID,
	"more_url" => array("clouds_storage_list.php", "clouds_storage_edit.php"),
	"icon" => "clouds_menu_icon",
	"page_icon" => "clouds_page_icon",
);

return $arMenu;
?>
