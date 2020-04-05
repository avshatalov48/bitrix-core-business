<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult["TD_WIDTH"] = round(100/$arParams["LINE_ELEMENT_COUNT"])."%";
$arResult["nRowsPerItem"] = 2; //Image and Name
$arResult["bDisplayFields"] = count($arParams["FIELD_CODE"])>0;
foreach($arResult["ITEMS"] as $arItem)
{
	if(count($arItem["DISPLAY_PROPERTIES"])>0)
		$arResult["bDisplayFields"] = true;
	if($arResult["bDisplayFields"])
		break;
}
if($arResult["bDisplayFields"])
	$arResult["nRowsPerItem"]++; // Plus one row for fields
//array_chunk
$arResult["ROWS"] = array();
while(count($arResult["ITEMS"])>0)
{
	$arRow = array_splice($arResult["ITEMS"], 0, $arParams["LINE_ELEMENT_COUNT"]);
	while(count($arRow) < $arParams["LINE_ELEMENT_COUNT"])
		$arRow[]=false;
	$arResult["ROWS"][]=$arRow;
}
?>
