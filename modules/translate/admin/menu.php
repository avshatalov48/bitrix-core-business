<?
IncludeModuleLangFile(__FILE__);
if ($APPLICATION->GetGroupRight("translate") <= "D")
	return false;

$aMenu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "translate",
	"sort" => 500,
	"text" => GetMessage("TRANS_TRANSLATE"),
	"title" => GetMessage("TRANS_TRANSLATE"),
	"icon" => "translate_menu_icon",
	"page_icon" => "translate_page_icon",
	"items_id" => "menu_translate",
	"items" => array()
);

$aMenu['items'][] = array(
	"text" => GetMessage("TRANS_BROWS_FILES"),
	"url" => "translate_list.php?lang=".LANGUAGE_ID,
	"more_url" => array(
		"translate_edit.php",
		"translate_list.php",
		"translate_edit_php.php",
		"translate_show_php.php",
		"translate_check_files.php"
	),
	"title" => GetMessage("TRANS_BROWS_FILES")
);

$aMenu['items'][] = array(
	"text" => GetMessage("TRANS_COLLECTOR"),
	"url" => "translate_collector.php?lang=".LANGUAGE_ID,
	"more_url" => array("translate_collector.php"),
	"title" => GetMessage("TRANS_COLLECTOR")
);

return $aMenu;