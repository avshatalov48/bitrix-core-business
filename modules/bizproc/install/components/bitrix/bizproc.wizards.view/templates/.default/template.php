<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["FatalErrorMessage"] <> '')
{
	?>
	<span class='errortext'><?= $arResult["FatalErrorMessage"] ?></span><br /><br />
	<?
}
else
{
	if ($arResult["ErrorMessage"] <> '')
	{
		?>
		<span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br />
		<?
	}
	$arButtons = array(
		array(
			"TEXT"=>GetMessage("BPWC_WNCT_2LIST"),
			"TITLE"=>GetMessage("BPWC_WNCT_2LIST"),
			"LINK"=>$arResult["PATH_TO_LIST"],
			"ICON"=>"btn-list",
		),
	);
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arButtons
		),
		$component
	);
	?>

	<?
	$arResult["FORM_ID"] = "form_bp_".implode("_", $arResult["DocumentType"]);

	$arTabs = array();

	$arFieldsTmp = array(
		array("id" => "STATE", "name" => GetMessage("BPWC_WNCT_STATE"), "type" => "custom", "value" => "<a href=\"".$arResult["PATH_TO_LOG"]."\">".$arResult["BP"]["DOCUMENT_STATE"]["STATE_TITLE"]."</a>"),
	);

	if (count($arResult["BP"]["DOCUMENT_STATE_TASKS"]) > 0)
	{
		$tasksTmp = "";
		foreach ($arResult["BP"]["DOCUMENT_STATE_TASKS"] as $arTask)
			$tasksTmp .= '<a href="'.$arTask["URL"].'" onclick="" title="'.strip_tags($arTask["DESCRIPTION"]).'">'.$arTask["NAME"].'</a><br />';
		$arFieldsTmp[] = array("id" => "TASKS", "name" => GetMessage("BPWC_WNCT_TASKS"), "type" => "custom", "value" => $tasksTmp);
	}

	if (!empty($arResult["BP"]["DOCUMENT_STATE_EVENTS"]))
	{
		$eventsTmp = "";
		foreach ($arResult["BP"]["DOCUMENT_STATE_EVENTS"] as $e)
			$eventsTmp .= '<a href="'.$e["URL"].'">'.$e["TITLE"].'</a><br />';
		$arFieldsTmp[] = array("id" => "EVENTS", "name" => GetMessage("BPWC_WNCT_EVENTS"), "type" => "custom", "value" => $eventsTmp);
	}

	if (count($arResult["Block"]["VISIBLE_FIELDS"]) <= 0 || in_array("NAME", $arResult["Block"]["VISIBLE_FIELDS"]))
		$arFieldsTmp[] = array("id" => "NAME", "name" => GetMessage("BPWC_WNCT_NAME"), "type" => "label");
	if (count($arResult["Block"]["VISIBLE_FIELDS"]) <= 0 || in_array("CREATED_BY_PRINTABLE", $arResult["Block"]["VISIBLE_FIELDS"]))
		$arFieldsTmp[] = array("id" => "CREATED_BY_PRINTABLE", "name" => $arResult["DocumentFields"]["CREATED_BY_PRINTABLE"]["Name"], "type" => "label");
	if (count($arResult["Block"]["VISIBLE_FIELDS"]) <= 0 || in_array("TIMESTAMP_X", $arResult["Block"]["VISIBLE_FIELDS"]))
		$arFieldsTmp[] = array("id" => "TIMESTAMP_X", "name" => $arResult["DocumentFields"]["TIMESTAMP_X"]["Name"], "type" => "label");

	$arTabs[] = array(
		"id" => "tab1", "name" => GetMessage("BPWC_WNCT_TAB1"), "title" => GetMessage("BPWC_WNCT_TAB1"), "icon" => "",
		"fields" => $arFieldsTmp
	);

	$arFieldsTmp = array();
	foreach ($arResult["DocumentFields"] as $key => $value)
	{
		if (count($arResult["Block"]["VISIBLE_FIELDS"]) > 0 && !in_array($key, $arResult["Block"]["VISIBLE_FIELDS"]))
			continue;
		if (in_array($key, array("NAME", "CREATED_BY_PRINTABLE", "TIMESTAMP_X")))
			continue;
		if (in_array($value["BaseType"], array("user")))
			continue;

		$arFieldsTmp[] = array("id" => $key, "name" => $value["Name"], "type"=>"label");
	}

	$arTabs[] = array(
		"id" => "tab2", "name" => GetMessage("BPWC_WNCT_TAB2"), "title" => GetMessage("BPWC_WNCT_TAB2"), "icon" => "",
		"fields" => $arFieldsTmp
	);


	$arResultGrid["GRID_ID"] = "form_bp_grid_".implode("_", $arResult["DocumentType"]);

	$gridOptions = new CGridOptions($arResultGrid["GRID_ID"]);
	$arSort = $gridOptions->GetSorting(array("sort" => array("id" => "desc"), "vars" => array("by" => "by", "order" => "order")));
	$arNav = $gridOptions->GetNavParams(array("nPageSize" => 20));

	$dbTrack = CBPTrackingService::GetList(
		$arSort["sort"],
		array(
			"WORKFLOW_ID" => $arResult["BP"]["DOCUMENT_STATE"]["ID"],
			"TYPE" => array(
				CBPTrackingType::Custom,
				CBPTrackingType::FaultActivity,
				CBPTrackingType::Report,
				CBPTrackingType::Error
			)
		)
	);

	$dbTrack->NavStart($arNav["nPageSize"]);

	$arRowsTmp = array();
	while ($arTrackRecord = $dbTrack->GetNext())
	{
		foreach ($arTrackRecord as $key=>$value)
		{
			if ($key != 'ACTION_NOTE' && CheckDateTime($value))
			{
				$arTrackRecord[$key] = FormatDateFromDB($value);
			}
		}
		$note = $arTrackRecord["ACTION_NOTE"];
		$note = CBPTrackingService::parseStringParameter($note,$arResult["DocumentType"]);

		$arCols = array("ACTION_NOTE" => $note);

		$arRowsTmp[] = array(
			"data" => $arTrackRecord,
			"actions" => array(),
			"columns" => $arCols,
			"editable" => false,
		);
	}

	$arResultGrid["ROWS"] = $arRowsTmp;
	$arResultGrid["ROWS_COUNT"] = $dbTrack->SelectedRowsCount();
	$arResultGrid["SORT"] = $arSort["sort"];
	$arResultGrid["SORT_VARS"] = $arSort["vars"];

	$dbTrack->bShowAll = false;
	$arResultGrid["NAV_OBJECT"] = $dbTrack;

	ob_start();

	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.grid",
		"",
		array(
			"GRID_ID" => $arResultGrid["GRID_ID"],
			"HEADERS" => array(
				array("id"=>"MODIFIED", "name"=>GetMessage("BPWC_WNCT_TL_DATE"), "sort"=>"MODIFIED", "default"=>true, "editable"=>false),
				array("id"=>"ACTION_TITLE", "name"=>GetMessage("BPWC_WNCT_TL_NAME"), "default"=>true, "editable"=>false),
				array("id"=>"ACTION_NOTE", "name"=>GetMessage("BPWC_WNCT_TL_NOTE"), "default"=>true, "editable"=>false),
			),
			"SORT"=>$arResultGrid["SORT"],
			"SORT_VARS"=>$arResultGrid["SORT_VARS"],
			"ROWS"=>$arResultGrid["ROWS"],
			"FOOTER"=>array(array("title"=>GetMessage("BPWC_WNCT_TL_TOTAL"), "value"=>$arResultGrid["ROWS_COUNT"])),
			"ACTIONS"=>array(),
			"ACTION_ALL_ROWS"=>false,
			"EDITABLE"=>false,
			"NAV_OBJECT"=>$arResultGrid["NAV_OBJECT"],
			"AJAX_MODE"=>"N",
			"AJAX_OPTION_JUMP"=>"N",
			"AJAX_OPTION_STYLE"=>"Y",
			"FORM_ID"=>$arResult["FORM_ID"],
			"TAB_ID"=>"tab3",
		),
		$component
	);

	$gridTmp = ob_get_clean();


	$arTabs[] = array(
		"id" => "tab3", "name" => GetMessage("BPWC_WNCT_TL_HISTORY"), "title" => GetMessage("BPWC_WNCT_TL_HISTORY"), "icon" => "",
		"fields" => array(
			array("id" => "GRID", "name" => GetMessage("BPWC_WNCT_TL_HISTORY"), "type" => "custom", "value" => $gridTmp, "colspan" => true),
		)
	);


	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.form",
		"",
		array(
			"FORM_ID" => $arResult["FORM_ID"],
			"TABS" => $arTabs,
			"BUTTONS"=>array("custom_html"=>"", "standard_buttons"=>false),
			"DATA"=>$arResult["BP"],
			"THEME_GRID_ID"=>"user_grid",
			"SHOW_SETTINGS"=>"Y",
			"AJAX_MODE"=>"N",
			"AJAX_OPTION_JUMP"=>"N",
			"AJAX_OPTION_STYLE"=>"Y",
		),
		$component
	);
}
?>