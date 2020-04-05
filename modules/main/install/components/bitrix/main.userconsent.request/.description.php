<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COMP_MAIN_USER_CONSENT_TITLE"),
	"DESCRIPTION" => GetMessage("COMP_MAIN_USER_CONSENT_DESCRIPTION"),
	"ICON" => "/images/user_profile.gif",
	"PATH" => array(
			"ID" => "utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => GetMessage("COMP_MAIN_USER_CONSENT_GROUP_NAME")
			)
		),
);
?>