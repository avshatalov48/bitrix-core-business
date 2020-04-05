<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("socialnetwork") >= "R")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "socialnetwork",
		"sort" => 550,
		"text" => GetMessage("BLG_AM_SONETS"),
		"title"=> GetMessage("BLG_AM_SONETS_ALT"),
		"icon" => "sonet_menu_icon",
		"page_icon" => "sonet_page_icon",
		"items_id" => "menu_sonet",
		"items" => array(
			array(
				"text" => GetMessage("SONET_MENU_SUBJECT"),
				"url" => "socnet_subject.php?lang=".LANGUAGE_ID,
				"more_url" => array("socnet_subject_edit.php"),
				"title" => GetMessage("SONET_MENU_SUBJECT_ALT")
			),
			array(
				"text" => GetMessage("SONET_MENU_GROUP"),
				"url" => "socnet_group.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("SONET_MENU_GROUP_ALT")
			),
			array(
				"text" => GetMessage("SONET_MENU_SMILES"),
				"url" => "socnet_smile.php?lang=".LANGUAGE_ID,
				"more_url" => array("socnet_smile_edit.php"),
				"title" => GetMessage("SONET_MENU_SMILES_ALT")
			),
		)
	);

	return $aMenu;
}
return false;
?>
