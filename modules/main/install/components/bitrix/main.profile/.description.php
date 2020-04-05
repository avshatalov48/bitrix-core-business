<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COMP_MAIN_PROFILE_TITLE"),
	"DESCRIPTION" => GetMessage("COMP_MAIN_PROFILE_DESCR"),
	"ICON" => "/images/user_profile.gif",
	"PATH" => array(
			"ID" => "utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => GetMessage("MAIN_USER_GROUP_NAME")
			)
		),
);
?>