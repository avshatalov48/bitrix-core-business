<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("form")) return;

$arrForms = array();
$rsForm = CForm::GetList($by='s_sort', $order='asc', array("SITE" => $_REQUEST["site"]), $v3);
while ($arForm = $rsForm->Fetch())
{
	$arrForms[$arForm["ID"]] = "[".$arForm["ID"]."] ".$arForm["NAME"];
}

$arComponentParameters = array(
	"GROUPS" => array(
		"FORM_PARAMS" => array(
			"NAME" => GetMessage("COMP_FORM_GROUP_PARAMS")
		),
	),

	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => Array(
			"WEB_FORM_ID" => Array("NAME" => GetMessage("COMP_FORM_PARAMS_WEB_FORM_ID")),
			"RESULT_ID" => Array("NAME" => GetMessage("COMP_FORM_PARAMS_RESULT_ID")),
		),
		"SEF_MODE" => Array(

		),

		"WEB_FORM_ID" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_WEB_FORM_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arrForms,
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT" => "={\$_REQUEST[WEB_FORM_ID]}",
			"PARENT" => "DATA_SOURCE",
		),

		"LIST_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_LIST_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "result_list.php",
			"PARENT" => "FORM_PARAMS",
		),

		"EDIT_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "result_edit.php",
			"PARENT" => "FORM_PARAMS",
		),

		"SUCCESS_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SUCCESS_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "FORM_PARAMS",
		),

		"CHAIN_ITEM_TEXT" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_CHAIN_ITEM_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "FORM_PARAMS",
		),

		"CHAIN_ITEM_LINK" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_CHAIN_ITEM_LINK"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "FORM_PARAMS",
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
?>