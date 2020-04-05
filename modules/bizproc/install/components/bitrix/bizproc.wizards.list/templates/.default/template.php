<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (strlen($arResult["FatalErrorMessage"]) > 0)
{
	?>
	<span class='errortext'><?= $arResult["FatalErrorMessage"] ?></span><br /><br />
	<?
}
else
{
	if (strlen($arResult["ErrorMessage"]) > 0)
	{
		?>
		<span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br />
		<?
	}
	if ($arResult["AllowCreate"] || $arResult["AllowAdmin"])
	{
		$arButtons = array();
		if ($arResult["AllowCreate"])
		{
			$arButtons[] = array(
				"TEXT"=>(strlen($arResult["CreateTitle"]) > 0 ? $arResult["CreateTitle"] : GetMessage("BPWC_WLCT_NEW")),
				"TITLE"=>(strlen($arResult["CreateTitle"]) > 0 ? $arResult["CreateTitle"] : GetMessage("BPWC_WLCT_NEW")),
				"LINK"=>$arResult["PATH_TO_START"],
				"ICON"=>"btn-new",
			);
			$arButtons[] = array("SEPARATOR"=>true);
		}
		$arButtons[] = array(
			"TEXT"=>GetMessage("BPWC_WLCT_LIST"),
			"TITLE"=>GetMessage("BPWC_WLCT_LIST"),
			"LINK"=>$arResult["PATH_TO_INDEX"],
			"ICON"=>"btn-list",
		);
		if ($arResult["AllowAdmin"])
		{
			$arButtons[] = array("SEPARATOR"=>true);
			$arButtons[] = array(
				"TEXT"=>GetMessage("BPWC_WLCT_VARS"),
				"TITLE"=>GetMessage("BPWC_WLCT_VARS"),
				"LINK"=>$arResult["PATH_TO_SETVAR"],
				"ICON"=>"",
			);
			if (IsModuleInstalled("bizprocdesigner"))
			{
				$arButtons[] = array(
					"TEXT"=>GetMessage("BPWC_WLCT_BP"),
					"TITLE"=>GetMessage("BPWC_WLCT_BP"),
					"LINK"=>$arResult["PATH_TO_BP"],
					"ICON"=>"",
				);
			}
		}

		$APPLICATION->IncludeComponent(
			"bitrix:main.interface.toolbar",
			"",
			array(
				"BUTTONS" => $arButtons
			),
			$component
		);
	}
	?>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.grid",
		"",
		array(
			"GRID_ID"=>$arResult["GRID_ID"],
			"HEADERS"=>$arResult["HEADERS"],
			"SORT"=>$arResult["SORT"],
			"ROWS"=>$arResult["RECORDS"],
			"FOOTER"=>array(array("title"=>GetMessage("BPWC_WLCT_TOTAL"), "value"=>$arResult["ROWS_COUNT"])),
			"ACTIONS"=>array("delete"=>true, "list"=>array()),
			"ACTION_ALL_ROWS"=>false,
			"EDITABLE"=>false,
			"NAV_OBJECT"=>$arResult["NAV_RESULT"],
			"AJAX_MODE"=>"Y",
			"AJAX_OPTION_JUMP"=>"N",
			"FILTER"=>$arResult["FILTER"],
		),
		$component
	);
	?>

	<?
}
?>