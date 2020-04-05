<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SUP_DESC_YES"),
	"N" => GetMessage("SUP_DESC_NO"),
);

global $USER_FIELD_MANAGER;
$SHOW_USER_FIELD = array( ""=>"" );
$arrUF = $USER_FIELD_MANAGER->GetUserFields( "SUPPORT", 0, LANGUAGE_ID );
foreach( $arrUF as $FIELD_ID => $arField )
{
	$SHOW_USER_FIELD[$FIELD_ID] = $arField["EDIT_FORM_LABEL"];
}

$arComponentParameters = array(
	"PARAMETERS" => array(

		"VARIABLE_ALIASES" => Array(
			"ID" => Array("NAME" => GetMessage("SUP_TICKET_ID_DESC"))
		),

		"SEF_MODE" => Array(
			"ticket_list" => Array(
				"NAME" => GetMessage("SUP_TICKET_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()
			),

			"ticket_edit" => Array(
				"NAME" => GetMessage("SUP_TICKET_EDIT_DESC"),
				"DEFAULT" => "#ID#.php",
				"VARIABLES" => array("ID")
			),
		),

		"TICKETS_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_LIST_TICKETS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "50"
		),

		"MESSAGES_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_EDIT_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
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
		
		"SET_SHOW_USER_FIELD" => Array(
			"NAME"=>GetMessage("SUP_SHOW_USER_FIELD"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"Y", 
			"PARENT" => "ADDITIONAL_SETTINGS", 
			"VALUES"=>$SHOW_USER_FIELD
		),

	)
);
?>
