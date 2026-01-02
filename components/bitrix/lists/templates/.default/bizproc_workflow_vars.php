<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$moduleId = "lists";
	$entity = "BizprocDocument";
}
else
{
	$moduleId = "lists";
	$entity = 'Bitrix\Lists\BizprocDocumentLists';
}
$link = str_replace(
	array("#list_id#"),
	array($arResult["VARIABLES"]["list_id"]),
	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
);
CJSCore::Init(array('lists'));

$returnButton = new Bitrix\UI\Buttons\Button([
	'text' => Loc::getMessage('CT_BL_LIST_PROCESSES'),
	'color' => Bitrix\UI\Buttons\Color::LINK,
	'link' => $link,
	'icon' => Bitrix\UI\Buttons\Icon::BACK,
]);
$returnButton->setSize(Bitrix\UI\Buttons\Size::SMALL);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($returnButton);

$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.setvar", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_TYPE" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"ID" => $arResult['VARIABLES']['ID'],
	"EDIT_PAGE_TEMPLATE" => str_replace(
		array("#list_id#"),
		array($arResult["VARIABLES"]["list_id"]),
		$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_vars"]
	),
	"LIST_PAGE_URL" => str_replace(
		array("#list_id#"),
		array($arResult["VARIABLES"]["list_id"]),
		$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
	),
	"SHOW_TOOLBAR" => "N",
	"SET_TITLE" => "Y",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
