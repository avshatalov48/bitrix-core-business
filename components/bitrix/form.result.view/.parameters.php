<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule("form")) return;

$arYesNo = array("Y" => GetMessage("FORM_COMP_VALUE_YES"), "N" => GetMessage("FORM_COMP_VALUE_NO"));
			
$arComponentParameters = array(
	"GROUPS" => array(
		"FORM_PARAMS" => array(
			"NAME" => GetMessage("COMP_FORM_GROUP_PARAMS")
		),
	),	

	"PARAMETERS" => array(
	
		"SEF_MODE" => array(
			"view" => array(
				"NAME" => GetMessage("COMP_FORM_SEF_RESULT_VIEW_PAGE"),
				"DEFAULT" => "#RESULT_ID#/",
				"VARIABLES" => array("RESULT_ID"),
			),
		),		
	
		"RESULT_ID" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_RESULT_ID"), 
			"TYPE" => "STRING",
			"DEFAULT" => "={\$_REQUEST[\"RESULT_ID\"]}",
			"PARENT" => "DATA_SOURCE",
		),

		"SHOW_ADDITIONAL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_ADDITIONAL"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "FORM_PARAMS",
		),

		"SHOW_ANSWER_VALUE" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_ANSWER_VALUE"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "FORM_PARAMS",
			),
		
		"SHOW_STATUS" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_SHOW_STATUS"), 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "FORM_PARAMS",
			),
	
		"EDIT_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_URL"), 
			"DEFAULT" => "result_edit.php",
			"TYPE" => "STRING",
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

		//"SET_TITLE" => array(),
		//"CACHE_TIME" => array(),
	),
);
