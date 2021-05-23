<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SUCCESSFUL_URL" => array(
			"PARENT" => "BASE",
			"NAME" => getMessage("SECURITY_USER_OTP_INIT_SUCCESSFUL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/",
		),
		"SHOW_DESCRIPTION" => array(
			"PARENT" => "BASE",
			"NAME" => getMessage("SECURITY_USER_OTP_INIT_SHOW_DESCRIPTION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);
?>