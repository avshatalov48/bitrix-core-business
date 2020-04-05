<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID" => $arResult["GRID"]["ID"],
		"HEADERS" => array(
			array("id" => "NAME", "name" =>GetMessage("IDEA_SUBSCRIBE_TITLE"), "default" => true, "sort" => "TITLE"),
			array("id" => "STATUS", "name" => GetMessage("IDEA_SUBSCRIBE_STATUS"), "default" => true, "sort" => CIdeaManagment::UFStatusField),
			array("id" => "PUBLISHED", "name" => GetMessage("IDEA_SUBSCRIBE_PUBLISHED"), "default" => false, "sort" => "DATE_PUBLISH"),
			array("id" => "AUTHOR", "name" => GetMessage("IDEA_SUBSCRIBE_AUTHOR"), "default" => false, "sort" => "AUTHOR"),
		),
		"SORT" => $arResult["GRID"]["SORT"],
		"SORT_VARS" => $arResult["GRID"]["SORT_VARS"],
		"ROWS" => $arResult["GRID"]["DATA"],
		"FOOTER" => array(array("title" => GetMessage("IDEA_SUBSCRIBE_TOTAL"), "value" => count($arResult["GRID"]["DATA"]))),
		"EDITABLE" => false,
		"ACTIONS" => array(
			"delete" => true
		),
		"ACTION_ALL_ROWS" => true,
		"NAV_OBJECT" => $arResult["GRID"]["NAVIGATION"],
		"AJAX_MODE" => "N",
	),
	$component
);?>