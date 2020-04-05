<?
IncludeModuleLangFile(__FILE__);

$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if($WORKFLOW_RIGHT!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_content",
		"section" => "workflow",
		"sort" => 400,
		"text" => GetMessage("FLOW_MENU_MAIN"),
		"title" => GetMessage("FLOW_MENU_MAIN_TITLE"),
		"icon" => "workflow_menu_icon",
		"page_icon" => "workflow_page_icon",
		"items_id" => "menu_workflow",
		"items" => array(
			array(
				"text" => GetMessage("FLOW_MENU_DOCUMENTS"),
				"title" => GetMessage("FLOW_MENU_DOCUMENTS_ALT"),
				"url" => "workflow_list.php?lang=".LANG,
				"more_url" => Array(
					"workflow_list.php",
					"workflow_edit.php"
				),
			),
			array(
				"text" => GetMessage("FLOW_MENU_HISTORY"),
				"title" => GetMessage("FLOW_MENU_HISTORY_ALT"),
				"url" => "workflow_history_list.php?lang=".LANG,
				"more_url" => Array(
					"workflow_history_list.php",
					"workflow_history_view.php"
				),
			),
		)
	);
	if($WORKFLOW_RIGHT!="D")
	{
		$aMenu["items"][] = array(
			"text" => GetMessage("FLOW_MENU_STAGE"),
			"title" => GetMessage("FLOW_MENU_STAGE_ALT"),
			"url" => "workflow_status_list.php?lang=".LANG,
			"more_url" => Array(
				"workflow_status_list.php",
				"workflow_status_edit.php"
			),
		);
	}
	return $aMenu;
}
return false;
?>
