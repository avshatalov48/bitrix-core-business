<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

if (!CModule::IncludeModule('bizproc') || !CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"]))
{
	ShowError(GetMessage('BIZPROC_MODULE_NOT_INSTALLED'));

	return;
}

CJSCore::Init(array('lists'));

$listElementUrl = CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#"),
	array($arResult["VARIABLES"]["list_id"], 0),
	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list"]
), array("list_section_id" => ""));

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
}
?>
<div class="pagetitle-container pagetitle-align-right-container">
	<a href="<?=$listElementUrl?>" class="ui-btn ui-btn-sm ui-btn-link ui-btn-themes lists-list-back">
		<?=GetMessage("CT_BL_TOOLBAR_RETURN_LIST_ELEMENT")?>
	</a>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.toolbar",
	"",
	array(
		"BUTTONS"=>array(
			array(
				"TEXT" => GetMessage("CT_BL_STATE_BIZPROC"),
				"TITLE" => GetMessage("CT_BL_STATE_BIZPROC_TITLE"),
				"LINK" => CHTTP::urlAddParams(str_replace(
						array("#list_id#", "#ID#"),
						array($arResult["VARIABLES"]["list_id"], 0),
						$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_edit"]
				), array("init" => "statemachine")),
				"ICON" => "btn-new",
			),
			array(
				"TEXT" => GetMessage("CT_BL_SEQ_BIZPROC"),
				"TITLE" => GetMessage("CT_BL_SEQ_BIZPROC_TITLE"),
				"LINK" => str_replace(
						array("#list_id#", "#ID#"),
						array($arResult["VARIABLES"]["list_id"], 0),
						$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_edit"]
				),
				"ICON" => "btn-new",
			),
		),
	),
	$component, array("HIDE_ICONS" => "Y")
);
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$moduleId = "lists";
	$entity = "BizprocDocument";
	$createDefaultTemplate = 'N';
}
else
{
	$moduleId = "lists";
	$entity = 'Bitrix\Lists\BizprocDocumentLists';
	$createDefaultTemplate = 'Y';
}
$APPLICATION->IncludeComponent("bitrix:bizproc.workflow.list", ".default", Array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_ID" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"CREATE_DEFAULT_TEMPLATE" => $createDefaultTemplate,
	"EDIT_URL" => str_replace(
				array("#list_id#"),
				array($arResult["VARIABLES"]["list_id"]),
				$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_edit"]
			),
	"SET_TITLE" => "Y",
	"EDIT_VARS_URL" => str_replace(
				array("#list_id#"),
				array($arResult["VARIABLES"]["list_id"]),
				$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_vars"]
			),
	"EDIT_CONSTANTS_URL" => str_replace(
		array("#list_id#"),
		array($arResult["VARIABLES"]["list_id"]),
		$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_constants"]
	),
	"TARGET_MODULE_ID" => "lists",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
