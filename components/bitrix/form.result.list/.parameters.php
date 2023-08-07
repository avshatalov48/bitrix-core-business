<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("form")) return;

$arrForms = array();
$rsForm = CForm::GetList('s_sort', 'asc', !empty($_REQUEST["site"]) ? array("SITE" => $_REQUEST["site"]) : array());
while ($arForm = $rsForm->Fetch())
{
	$arrForms[$arForm["ID"]] = "[".$arForm["ID"]."] ".$arForm["NAME"];
}

if (intval($arCurrentValues["WEB_FORM_ID"]) > 0)
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

$arYesNo = array("Y" => GetMessage("FORM_COMP_VALUE_YES"), "N" => GetMessage("FORM_COMP_VALUE_NO"));

$arComponentParameters = array(
	"GROUPS" => array(
		"FORM_PARAMS" => array(
			"NAME" => GetMessage("COMP_FORM_GROUP_PARAMS")
		),
	),	

	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => Array(
		),
		"SEF_MODE" => Array(
		), 		

		"WEB_FORM_ID" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_WEB_FORM_ID"), 
			"TYPE" => "LIST",
			"VALUES" => $arrForms,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT" => "={\$_REQUEST[WEB_FORM_ID]}",
			"PARENT" => "DATA_SOURCE",
		),
		
		"VIEW_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_VIEW_URL"), 
			"TYPE" => "STRING",
			"DEFAULT" => "result_view.php",
			"PARENT" => "FORM_PARAMS",
		),
		
		"EDIT_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_URL"), 
			"TYPE" => "STRING",
			"DEFAULT" => "result_edit.php",
			"PARENT" => "FORM_PARAMS",
		),

		"NEW_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_NEW_URL"), 
			"TYPE" => "STRING",
			"DEFAULT" => "result_new.php",
			"PARENT" => "FORM_PARAMS",
		),
		
		"SHOW_ADDITIONAL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_ADDITIONAL"), 
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",
			"PARENT" => "FORM_PARAMS",
		),

		"SHOW_ANSWER_VALUE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_ANSWER_VALUE"), 
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",
			"PARENT" => "FORM_PARAMS",
			),
		
		"SHOW_STATUS" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_STATUS"), 
			"TYPE" => "CHECKBOX",
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "FORM_PARAMS",
			),

		"NOT_SHOW_FILTER" => array(
			"NAME" => $show_list ? GetMessage("COMP_FORM_PARAMS_NOT_SHOW_FILTER_LIST") : GetMessage("COMP_FORM_PARAMS_NOT_SHOW_FILTER"), 
			"TYPE" => $show_list ? "LIST" : "STRING",
			"MULTIPLE" => $show_list ? "Y" : "",
			"VALUES" => $show_list ? $arFieldList : "",
			"ADDITIONAL_VALUES" => $show_list ? "Y" : "",
			"DEFAULT" => "",
			"PARENT" => "FORM_PARAMS",
		),

		"NOT_SHOW_TABLE" => array(
			"NAME" => $show_list ? GetMessage("COMP_FORM_PARAMS_NOT_SHOW_TABLE_LIST") : GetMessage("COMP_FORM_PARAMS_NOT_SHOW_TABLE_LIST"), 
			"TYPE" => $show_list ? "LIST" : "STRING",
			"MULTIPLE" => $show_list ? "Y" : "",
			"VALUES" => $show_list ? $arFieldList : "",
			"ADDITIONAL_VALUES" => $show_list ? "Y" : "",
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

		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("COMP_FORM_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	),
	
);
?>