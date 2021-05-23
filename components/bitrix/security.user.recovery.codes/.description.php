<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => getMessage("SECURITY_USER_RECOVERY_CODES_NAME"),
	"DESCRIPTION" => getMessage("SECURITY_USER_RECOVERY_CODES_DESCRIPTION"),
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "security",
			"NAME" => getMessage("SECURITY_USER_RECOVERY_CODES_DESCRIPTION")
		)
	),
);

?>