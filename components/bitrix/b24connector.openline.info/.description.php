<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("COMP_MAIN_AUTH_OPENLINE_TITLE"),
	"DESCRIPTION" => GetMessage("COMP_MAIN_AUTH_OPENLINE_DESCR"),
	"ICON" => "/images/user_authform.gif",
	"PATH" => array(
			"ID" => "utility",
			"CHILD" => array(
				"ID" => "user",
				"NAME" => GetMessage("MAIN_USER_GROUP_NAME")
			)
		),	
);
?>