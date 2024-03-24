<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule("form")) return;

$arStartPage = array("list" => GetMessage("COMP_FORM_VALUES_LIST"), "new" => GetMessage("COMP_FORM_VALUES_NEW"));

$arrForms = array();
$rsForm = CForm::GetList('s_sort', 'asc', !empty($_REQUEST["site"]) ? array("SITE" => $_REQUEST["site"]) : array());
while ($arForm = $rsForm->Fetch())
{
	$arrForms[$arForm["ID"]] = "[".$arForm["ID"]."] ".$arForm["NAME"];
}

if (isset($arCurrentValues["WEB_FORM_ID"]) && intval($arCurrentValues["WEB_FORM_ID"]) > 0)
{
	$show_list = true;
	$rsFieldList = CFormField::GetList(intval($arCurrentValues["WEB_FORM_ID"]), "ALL");
	$arFieldList = array();
	while ($arField = $rsFieldList->GetNext())
	{
		$arFieldList[$arField["SID"]] = "[".$arField["SID"]."] ".$arField["TITLE"];
	}
}
else
{
	$show_list = false;
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"AJAX_MODE" => array(),
		"VARIABLE_ALIASES" => Array(
			"action" => Array("NAME" => GetMessage("COMP_FORM_PARAMS_ACTION_ALIAS")),
		),
		"SEF_MODE" => Array(
			"new" => array(
				"NAME" => GetMessage("COMP_FORM_SEF_RESULT_NEW_PAGE"),
				"DEFAULT" => "#WEB_FORM_ID#/",
				"VARIABLES" => array("WEB_FORM_ID"),
			),
			"list" => array(
				"NAME" => GetMessage("COMP_FORM_SEF_RESULT_LIST_PAGE"),
				"DEFAULT" => "#WEB_FORM_ID#/list/",
				"VARIABLES" => array("WEB_FORM_ID" => "WEB_FORM_ID"),
			),
			"edit" => array(
				"NAME" => GetMessage("COMP_FORM_SEF_RESULT_EDIT_PAGE"),
				"DEFAULT" => "#WEB_FORM_ID#/edit/#RESULT_ID#/",
				"VARIABLES" => array("WEB_FORM_ID", "RESULT_ID"),
			),
			"view" => array(
				"NAME" => GetMessage("COMP_FORM_SEF_RESULT_VIEW_PAGE"),
				"DEFAULT" => "#WEB_FORM_ID#/view/#RESULT_ID#/",
				"VARIABLES" => array("WEB_FORM_ID", "RESULT_ID"),
			),

		),
		"WEB_FORM_ID" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_WEB_FORM_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arrForms,
			"ADDITIONAL_VALUES"	=> "Y",
			"REFRESH" => "Y",
			"DEFAULT" => "={\$_REQUEST[\"WEB_FORM_ID\"]}",
			"PARENT" => "DATA_SOURCE",
		),

		"RESULT_ID" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_RESULT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "={\$_REQUEST[\"RESULT_ID\"]}",
			"PARENT" => "DATA_SOURCE",
		),

		"START_PAGE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_START_PAGE"),
			"TYPE" => "LIST",
			"VALUES" => $arStartPage,
			"DEFAULT" => "new",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),

		"SHOW_LIST_PAGE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_LIST_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),

		"SHOW_EDIT_PAGE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_EDIT_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),

		"SHOW_VIEW_PAGE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_VIEW_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),

		"SUCCESS_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SUCCESS_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),

		"SHOW_ANSWER_VALUE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_ANSWER_VALUE"),
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),

		"SHOW_ADDITIONAL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_ADDITIONAL"),
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),

		"SHOW_STATUS" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_STATUS"),
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "VISUAL",
			),

		"EDIT_ADDITIONAL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_ADDITIONAL"),
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),

		"EDIT_STATUS" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_STATUS"),
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "VISUAL",
			),

		"NOT_SHOW_FILTER" => array(
			"NAME" => $show_list ? GetMessage("COMP_FORM_PARAMS_NOT_SHOW_FILTER_LIST") : GetMessage("COMP_FORM_PARAMS_NOT_SHOW_FILTER"),
			"TYPE" => $show_list ? "LIST" : "STRING",
			"MULTIPLE" => $show_list ? "Y" : "",
			"VALUES" => $show_list ? $arFieldList : "",
			"ADDITIONAL_VALUES" => $show_list ? "Y" : "",
			"DEFAULT" => "",
			"PARENT" => "VISUAL",
		),

		"NOT_SHOW_TABLE" => array(
			"NAME" => $show_list ? GetMessage("COMP_FORM_PARAMS_NOT_SHOW_TABLE_LIST") : GetMessage("COMP_FORM_PARAMS_NOT_SHOW_TABLE"),
			"TYPE" => $show_list ? "LIST" : "STRING",
			"MULTIPLE" => $show_list ? "Y" : "",
			"VALUES" => $show_list ? $arFieldList : "",
			"ADDITIONAL_VALUES" => $show_list ? "Y" : "N",
			"DEFAULT" => "",
			"PARENT" => "VISUAL",
		),

		"CHAIN_ITEM_TEXT" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_CHAIN_ITEM_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"CHAIN_ITEM_LINK" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_CHAIN_ITEM_LINK"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("COMP_FORM_PARAMS_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"IGNORE_CUSTOM_TEMPLATE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_IGNORE_CUSTOM_TEMPLATE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),

		"USE_EXTENDED_ERRORS" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_USE_EXTENDED_ERRORS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),

		"CACHE_TIME" => array("DEFAULT" => "3600"),
	),
);
