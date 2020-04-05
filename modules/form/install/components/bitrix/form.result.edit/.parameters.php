<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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
			"edit" => array(
				"NAME" => GetMessage("COMP_FORM_SEF_RESULT_EDIT_PAGE"),
				"DEFAULT" => "#RESULT_ID#/",
				"VARIABLES" => array("RESULT_ID"),
			),
		),
	
		"RESULT_ID" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_RESULT_ID"), 
			"TYPE" => "STRING",
			"DEFAULT" => "={\$_REQUEST[RESULT_ID]}",
			"PARENT" => "DATA_SOURCE",
		),

		"EDIT_ADDITIONAL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_ADDITIONAL"), 
			"TYPE" => "LIST",
			"VALUES" => $arYesNo,
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "N",
			"PARENT" => "FORM_PARAMS",
		),

		"EDIT_STATUS" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_EDIT_STATUS"), 
			"TYPE" => "LIST",
			"VALUES" => $arYesNo,
			"ADDITIONAL_VALUES" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "FORM_PARAMS",
			),
	
		"LIST_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_LIST_URL"), 
			"TYPE" => "STRING",
			"DEFAULT" => "result_list.php",
			"PARENT" => "FORM_PARAMS",
		),

		"VIEW_URL" => array(
			"NAME" => GetMessage("COMP_FORM_PARAMS_VIEW_URL"), 
			"TYPE" => "STRING",
			"DEFAULT" => "result_view.php",
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
		
		//"SET_TITLE" => array(),
		//"CACHE_TIME" => array(),
	),
);
?>