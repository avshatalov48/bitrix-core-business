<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SUP_DESC_YES"),
	"N" => GetMessage("SUP_DESC_NO"),

);

$arComponentParameters = array(
	"PARAMETERS" => array(

		"ID" => array(
			"NAME" => GetMessage("SUP_EDIT_DEFAULT_TEMPLATE_PARAM_1_NAME"), 
			"TYPE" => "STRING",
			"PARENT" => "BASE",
			"DEFAULT" => "={\$_REQUEST[\"ID\"]}"
		),

		"TICKET_LIST_URL" => Array(
			"NAME" => GetMessage("SUP_EDIT_DEFAULT_TEMPLATE_PARAM_2_NAME"), 
			"TYPE" => "STRING",
			"COLS" => 45,
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT" => "ticket_list.php"
		),

		"MESSAGES_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_EDIT_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"MULTIPLE" => "N",
			"DEFAULT" => "20"
		),
		
		"MESSAGE_MAX_LENGTH" => Array(
			"NAME" => GetMessage("SUP_MESSAGE_MAX_LENGTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "70"
		),
			
		"MESSAGE_SORT_ORDER" => Array(
			"NAME" => GetMessage("SUP_MESSAGE_SORT_ORDER"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES" =>Array(
				"asc"=>GetMessage("SUP_SORT_ASC"),
				"desc"=>GetMessage("SUP_SORT_DESC")
			),
		),

		"SET_PAGE_TITLE" => Array(
			"NAME"=>GetMessage("SUP_SET_PAGE_TITLE"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),
		
		"SHOW_COUPON_FIELD" => Array(
			"NAME" => GetMessage("SUP_SHOW_COUPON_FIELD"),
			"TYPE" => "CHECKBOX",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "N",
		),
		
	)
);
?>