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
	$arButtons = array();
	$arButtons[] = array(
		"TEXT"=>GetMessage("BPWC_WLCT_LIST"),
		"TITLE"=>GetMessage("BPWC_WLCT_LIST"),
		"LINK"=>$arResult["PATH_TO_LIST"],
		"ICON"=>"btn-list",
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
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.grid",
		"",
		array(
			"GRID_ID"=>$arResult["GRID_ID"],
			"HEADERS"=>$arResult["HEADERS"],
			"SORT"=>$arResult["SORT"],
			"ROWS"=>$arResult["RECORDS"],
			"FOOTER"=>array(array("title"=>GetMessage("BPWC_WLCT_TOTAL"), "value"=>count($arResult["RECORDS"]))),
			"ACTIONS"=>array("delete"=>true, "list"=>array()),
			"ACTION_ALL_ROWS"=>false,
			"EDITABLE"=>false,
			"NAV_OBJECT"=>null,
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