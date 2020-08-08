<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!empty($arResult["ERROR_MESSAGE"])):
	ShowError($arResult["ERROR_MESSAGE"]);
endif;
global $by, $order; 

?><?$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID" => $arParams["GRID_ID"],
		"HEADERS" => array(
			array("id" => "NAME", "name" => GetMessage("BPADH_NAME"), "default" => true, "sort" => "name"), 
			array("id" => "MODIFIED", "name" => GetMessage("BPADH_MODIFIED"), "default" => true, "sort" => "modified"), 
			array("id" => "USER", "name" => GetMessage("BPADH_AUTHOR"), "default" => true, "sort" => "user_name")
		), 
		"SORT" => array(mb_strtolower($by) => mb_strtolower($order)),
		"ROWS" => $arResult["GRID_VERSIONS"],
		"FOOTER" => array(array("title" => GetMessage("BPADH_ALL"), "value" => count($arResult["GRID_VERSIONS"]))),
		"EDITABLE" => false,
		"ACTIONS" => array(
			"delete" => true
        ),
		"ACTION_ALL_ROWS" => false,
		"NAV_OBJECT" => $arResult["NAV_RESULT"],
		"AJAX_MODE" => "N",
	),
	($this->__component->__parent ? $this->__component->__parent : $component)
);
?>