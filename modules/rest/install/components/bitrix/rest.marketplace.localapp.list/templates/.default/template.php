<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID"=>$arResult["GRID_ID"],
		"HEADERS"=>$arResult["HEADERS"],
		"ROWS"=>$arResult["ELEMENTS_ROWS"],
		"NAV_OBJECT"=>$arResult["NAV_OBJECT"],
		"FOOTER" => array(
			array("title" => GetMessage("CT_BLL_SELECTED"), "value" => $arResult["ROWS_COUNT"])
		),
		"AJAX_MODE" => "Y",
	),
	$component, array("HIDE_ICONS" => "Y")
);
?>

<script>
	BX.message({
		"APPLIST_DELETE_CONFIRM" : "<?=GetMessageJS("APPLIST_DELETE_CONFIRM")?>",
		"APPLIST_DELETE_ERROR" : "<?=GetMessageJS("APPLIST_DELETE_ERROR")?>"
	});
	BX.Marketplace.LocalappList.init({"url": "<?=CUtil::JSEscape($this->GetFolder()."/ajax.php")?>"});
</script>