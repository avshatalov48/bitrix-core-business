<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

$arComponentParameters = array(
	"PARAMETERS" => array(
		"USER_ID" => array(
			"NAME" => GetMessage("CP_BSAC_USER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "confirm_user_id",
		),
		"CONFIRM_CODE" => array(
			"NAME" => GetMessage("CP_BSAC_CONFIRM_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => "confirm_code",
		),
		"LOGIN" => array(
			"NAME" => GetMessage("CP_BSAC_LOGIN"),
			"TYPE" => "STRING",
			"DEFAULT" => "login",
		),
	),
);
?>