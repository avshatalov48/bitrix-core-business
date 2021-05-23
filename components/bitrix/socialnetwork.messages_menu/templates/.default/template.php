<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div style="margin-bottom: 1em;">
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.menu", 
	"", 
	Array(
		"MAX_ITEMS" => $arParams["MAX_ITEMS"],
		"ENTITY_TYPE" => "M",
		"ENTITY_ID" => $GLOBALS["USER"]->GetId(),
		"PAGE_ID" => "user_".$arParams["PAGE_ID"],
		"USE_MAIN_MENU" => $arParams["USE_MAIN_MENU"],
		"MAIN_MENU_TYPE" => $arParams["MAIN_MENU_TYPE"],		
		"arResult" => $arResult,
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>