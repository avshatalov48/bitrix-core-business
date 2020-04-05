<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("subscribe")!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "subscribe",
		"sort" => 200,
		"text" => GetMessage("mnu_sect"),
		"title" => GetMessage("mnu_sect_title"),
		"icon" => "subscribe_menu_icon",
		"page_icon" => "subscribe_page_icon",
		"items_id" => "menu_subscribe",
		"items" => array(
			array(
				"text" => GetMessage("mnu_posting"),
				"url" => "posting_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("posting_edit.php"),
				"title" => GetMessage("mnu_posting_alt")
			),
			array(
				"text" => GetMessage("mnu_subscr"),
				"url" => "subscr_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("subscr_edit.php"),
				"title" => GetMessage("mnu_subscr_alt")
			),
			array(
				"text" => GetMessage("mnu_subscr_import"),
				"url" => "subscr_import.php?lang=".LANGUAGE_ID,
				"more_url" => array("subscr_import.php"),
				"title" => GetMessage("mnu_subscr_import_alt")
			),
			array(
				"text" => GetMessage("mnu_rub"),
				"url" => "rubric_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("rubric_edit.php", "template_test.php"),
				"title" => GetMessage("mnu_rub_alt")
			),
		)
	);

	return $aMenu;
}
return false;
?>