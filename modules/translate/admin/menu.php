<?
use \Bitrix\Main\Localization\Loc;

if ($APPLICATION->GetGroupRight("translate") <= 'D')
{
	return false;
}

$aMenu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "translate",
	"sort" => 500,
	"text" => Loc::getMessage("TRANS_TRANSLATE"),
	"title" => Loc::getMessage("TRANS_INTERFACE"),
	"icon" => "translate_menu_icon",
	"page_icon" => "translate_page_icon",
	"items_id" => "menu_translate",
	"items" => array()
);

$aMenu['items'][] = array(
	"text" => Loc::getMessage("TRANS_BROWS_FILES"),
	"url" => "translate_list.php?lang=".LANGUAGE_ID,
	"more_url" => array(
		"translate_edit.php",
		"translate_list.php",
		"translate_edit_php.php",
		"translate_show_php.php",
		"translate_check_files.php"
	),
	"title" => Loc::getMessage("TRANS_INTERFACE_ALT")
);

$aMenu['items'][] = array(
	"text" => Loc::getMessage("TRANS_COLLECTOR"),
	"url" => "translate_collector.php?lang=".LANGUAGE_ID,
	"more_url" => array("translate_collector.php"),
	"title" => Loc::getMessage("TRANS_COLLECTOR")
);

return $aMenu;