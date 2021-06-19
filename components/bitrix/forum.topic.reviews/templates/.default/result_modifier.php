<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
$arParams["form_index"] = $this->randString(4);

$arParams["FORM_ID"] = "REPLIER".$arParams["form_index"];

$arParams["tabIndex"] = intval(intval($arParams["TAB_INDEX"]) > 0 ? $arParams["TAB_INDEX"] : 10);


$arParams["EDITOR_CODE_DEFAULT"] = ($arParams["EDITOR_CODE_DEFAULT"] == "Y" ? "Y" : "N");
$arResult["QUESTIONS"] = (is_array($arResult["QUESTIONS"]) ? array_values($arResult["QUESTIONS"]) : array());

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($arParams['AJAX_POST'] == 'Y'
	&& (
		$request->isPost()
			&& $request->getPost('save_product_review') === 'Y')
		||
		!$request->isPost()
			&& $request->get('ajax') == 'y'
	)
{
	ob_start();
}
$arResult["isIntranetInstalled"] = \Bitrix\Main\ModuleManager::isModuleInstalled('intranet');
?>
