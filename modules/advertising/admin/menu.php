<?
IncludeModuleLangFile(__FILE__);

$ADV_RIGHT = $APPLICATION->GetGroupRight("advertising");
if($ADV_RIGHT!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_marketing",
		"section" => "advertising",
		"sort" => 1000,
		"text" => GetMessage("AD_MENU_MAIN"),
		"title" => GetMessage("AD_MENU_MAIN_TITLE"),
		"icon" => "advertising_menu_icon",
		"page_icon" => "advertising_page_icon",
		"items_id" => "menu_advertising",
		"items" => array(
			array(
				"text" => GetMessage("AD_MENU_REPORTS"),
				"more_url" => array(),
				"title" => GetMessage("AD_MENU_REPORTS_TITLE"),
				"page_icon" => "advertising_page_icon",
				"items_id"	=> "menu_advertising_graph",
				"items" => Array(
					array(
						"text" => GetMessage("AD_MENU_BAN_GR"),
						"url" => "adv_banner_graph.php?lang=".LANGUAGE_ID,
						"more_url" => array(),
						"title" => GetMessage("AD_MENU_STATISTICS_ALT_GRAPH")
					),
					array(
						"text" => GetMessage("AD_MENU_BAN_CO"),
						"url" => "adv_contract_graph.php?lang=".LANGUAGE_ID,
						"more_url" => array(),
						"title" => GetMessage("AD_MENU_BAN_CO_TITLE")
					),
					array(
						"text" => GetMessage("AD_MENU_DIAG_BAN"),
						"url" => "adv_banner_diagram.php?lang=".LANGUAGE_ID,
						"more_url" => array(),
						"title" => GetMessage("AD_MENU_STATISTICS_ALT_DIAGRAM")
					),
					array(
						"text" => GetMessage("AD_MENU_DIAG_CO"),
						"url" => "adv_contract_diagram.php?lang=".LANGUAGE_ID,
						"more_url" => array(),
						"title" => GetMessage("AD_MENU_DIAG_CO_TITLE")
					),
				),
			),
			array(
				"text" => GetMessage("AD_MENU_BANNER_LIST"),
				"url" => "adv_banner_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("adv_banner_list.php", "adv_banner_edit.php"),
				"title" => GetMessage("AD_MENU_BANNER_LIST_ALT")
			),
			array(
				"text" => GetMessage("AD_MENU_CONTRACT_LIST"),
				"url" => "adv_contract_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("adv_contract_list.php", "adv_contract_edit.php"),
				"title" => GetMessage("AD_MENU_CONTRACT_LIST_ALT")
			),
			array(
				"text" => GetMessage("AD_MENU_TYPE_LIST"),
				"url" => "adv_type_list.php?lang=".LANGUAGE_ID,
				"more_url" => array("adv_type_list.php", "adv_type_edit.php"),
				"title" => GetMessage("AD_MENU_TYPE_LIST_ALT")
			),
		)
	);

	return $aMenu;
}
return false;
?>
