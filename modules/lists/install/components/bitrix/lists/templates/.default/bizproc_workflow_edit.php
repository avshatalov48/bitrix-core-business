<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

if (!CModule::IncludeModule('bizproc') || !CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"]))
{
	ShowError(GetMessage('BIZPROC_MODULE_NOT_INSTALLED'));

	return;
}

if (!CModule::IncludeModule('bizprocdesigner'))
{
	ShowError(GetMessage('BIZPROCDESIGNER_MODULE_NOT_INSTALLED'));

	return;
}

\Bitrix\Main\Loader::includeModule('ui');

CJSCore::Init(['window', 'lists']);

\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
		'link' => str_replace(
			["#list_id#"],
			[$arResult["VARIABLES"]["list_id"]],
			$arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
		),
		'color' => \Bitrix\UI\Buttons\Color::LINK,
		'text' => GetMessage("LISTS_BP_EDIT_RETURN"),
		'classList' => ['lists-list-back'],
	]
);

if ($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$moduleId = "lists";
	$entity = "BizprocDocument";
}
else
{
	$moduleId = "lists";
	$entity = 'Bitrix\Lists\BizprocDocumentLists';
}
$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.edit", ".default", [
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_TYPE" => "iblock_" . $arResult["VARIABLES"]["list_id"],
	"ID" => $arResult['VARIABLES']['ID'],
	"EDIT_PAGE_TEMPLATE" => str_replace(
		["#list_id#"],
		[$arResult["VARIABLES"]["list_id"]],
		$arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_workflow_edit"]
	),
	"LIST_PAGE_URL" => str_replace(
		["#list_id#"],
		[$arResult["VARIABLES"]["list_id"]],
		$arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
	),
	"SHOW_TOOLBAR" => "Y",
	"SET_TITLE" => "Y",
],
	$component,
	["HIDE_ICONS" => "Y"]
);
