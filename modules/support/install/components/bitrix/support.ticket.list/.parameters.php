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

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"TICKET_EDIT_TEMPLATE" => Array(
			"NAME" => GetMessage("SUP_LIST_DEFAULT_TEMPLATE_PARAM_1_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT" => "ticket_edit.php?ID=#ID#",
			"COLS" => 45
		),

		"TICKETS_PER_PAGE" => Array(
			"NAME" => GetMessage("SUP_LIST_TICKETS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT" => "50"
		),

		"SET_PAGE_TITLE" => Array(
			"NAME"=>GetMessage("SUP_SET_PAGE_TITLE"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>"Y", 
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
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