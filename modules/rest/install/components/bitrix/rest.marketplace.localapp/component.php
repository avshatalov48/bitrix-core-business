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
	"index" => "",
	"edit" => "edit/#id#/",
	"list" => "edit/",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array("id", "action");

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
		$componentPage = "index";
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
	if(strlen($arVariables["id"]) > 0)
	{
		$componentPage = "edit";
	}
	elseif($arVariables["action"] == "list")
	{
		$componentPage = "list";
	}
	else
	{
		$componentPage = "index";
	}
}

$arResult = array(
	"FOLDER" => $SEF_FOLDER,
	"URL_TEMPLATES" => $arUrlTemplates,
	"VARIABLES" => $arVariables,
	"ALIASES" => $arVariableAliases
);

$arParams["LIST_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["list"], $arVariables);
$arParams["ADD_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["edit"], array('id' => '0'));

$arParams["EDIT_URL_TPL"] = $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["edit"];

$arParams["COMPONENT_PAGE"] = $componentPage;

$APPLICATION->SetTitle(GetMessage("MARKETPLACE_LOCAL_TITLE"));

$this->IncludeComponentTemplate($componentPage);

