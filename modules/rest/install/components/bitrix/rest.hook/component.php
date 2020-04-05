<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$arDefaultUrlTemplates404 = array(
	"list" => "",
	"event_list" => "event/",
	"event_edit" => "event/#id#/",
	"ap_list" => "ap/",
	"ap_edit" => "ap/#id#/",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array("id", "type");

$SEF_FOLDER = "";
$arUrlTemplates = array();

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	if(strlen($componentPage) <= 0)
	{
		$componentPage = "list";
	}

	CComponentEngine::InitComponentVariables($componentPage,
		$arComponentVariables,
		$arVariableAliases,
		$arVariables
	);

	$SEF_FOLDER = $arParams["SEF_FOLDER"];
}
else
{
	$arVariables = array();

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";
	if($arVariables["type"] == "event")
	{
		$componentPage = "event";
	}
	elseif($arVariables["type"] == "ap")
	{
		$componentPage = "ap";
	}
	else
	{
		$componentPage = "list";
	}
}

$arResult = array(
	"FOLDER" => $SEF_FOLDER,
	"URL_TEMPLATES" => $arUrlTemplates,
	"VARIABLES" => $arVariables,
	"ALIASES" => $arVariableAliases
);

$arParams["LIST_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["list"], $arVariables);
$arParams["EVENT_ADD_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["event_edit"], array('id' => '0'));
$arParams["AP_ADD_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["ap_edit"], array('id' => '0'));

$arParams["EVENT_EDIT_URL_TPL"] = $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["event_edit"];
$arParams["AP_EDIT_URL_TPL"] = $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["ap_edit"];

$arParams["COMPONENT_PAGE"] = $componentPage;

$APPLICATION->SetTitle(GetMessage("REST_HOOK_TITLE"));

$this->IncludeComponentTemplate($componentPage);

