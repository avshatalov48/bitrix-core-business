<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*sample data
$arParams = array(
	"ITEMS" => array(
		"1" =>"first radio",
		"2" =>"second radio",
		"3" =>"fird radio"
		),
	"SELECTED" => "3",
	"TITLE" => "Radio buttons title",
	"JS_EVENT_TAKE_CHECKBOXES_VALUES" => "onGetSelectedRadio",
	"JS_RESULT_HANDLER" => "resultHandlerFunction",
	"DOM_CONTAINER_ID" => "rb_container",
	"RADIO_NAME" => "radio_name",
	"AJAX" => 'Y'
);
*/

if(!isset($arParams["ITEMS"]) || empty($arParams["ITEMS"]) || !is_array($arParams["ITEMS"]))
	return;

$arResult["TITLE"] = $arParams["TITLE"] ? $arParams["TITLE"] : false;
$arResult["DOM_CONTAINER_ID"] = $arParams["DOM_CONTAINER_ID"] ? $arParams["DOM_CONTAINER_ID"] : "ma_rb_".rand();
$arResult["RADIO_NAME"] = $arParams["RADIO_NAME"] ? $arParams["RADIO_NAME"] : "radio_".rand();
$arResult["JS_RESULT_HANDLER"] = $arParams["JS_RESULT_HANDLER"] ? $arParams["JS_RESULT_HANDLER"] : "false";

if(isset($arParams["SELECTED"]) && array_key_exists($arParams["SELECTED"], $arParams["ITEMS"]))
{
	$arResult["SELECTED"] = $arParams["SELECTED"];
}
else
{
	reset($arParams["ITEMS"]);
	$arResult["SELECTED"] = key($arParams["ITEMS"]);
}

$this->IncludeComponentTemplate();
?>
