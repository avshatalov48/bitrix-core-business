<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("MAPP_ME_MOBILEAPP_NOT_INSTALLED");
	return;
}
$arResult["FORM_ACTION"] = isset($arParams["FORM_ACTION"]) ? $arParams["FORM_ACTION"] : $APPLICATION->GetCurPageParam();
$arResult["FORM_ID"] = isset($arParams["FORM_ID"]) ? $arParams["FORM_ID"] : 'mapp_edit_form_id';
$arResult["FORM_NAME"] = isset($arParams["FORM_NAME"]) ? $arParams["FORM_NAME"] : 'mapp_edit_form_name';
$arResult["SKIP_FORM"] = isset($arParams["SKIP_FORM"]) && $arParams["SKIP_FORM"] == 'Y' ? true: false;

if(isset($arParams["ON_BEFORE_FORM_SUBMIT"]))
	$arResult["ON_BEFORE_FORM_SUBMIT"] = $arParams["ON_BEFORE_FORM_SUBMIT"];

$this->IncludeComponentTemplate();
?>