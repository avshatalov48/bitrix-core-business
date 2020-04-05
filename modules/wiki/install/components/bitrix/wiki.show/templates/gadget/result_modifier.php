<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!array_key_exists("TEXT_LIMIT", $arParams) || $arParams["TEXT_LIMIT"] <= 0)
	$arParams["TEXT_LIMIT"] = 500;
	
$parser = new CSocNetTextParser();
$arResult["ELEMENT"]["DETAIL_TEXT"] = $parser->html_cut($arResult["ELEMENT"]["DETAIL_TEXT"], $arParams["TEXT_LIMIT"]) ;

if (intval($arResult['ELEMENT']['ID']) > 0)
  $arResult['ELEMENT']['URL'] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_POST"], array("wiki_name" => rawurlencode($arParams['ELEMENT_NAME'])));
?>