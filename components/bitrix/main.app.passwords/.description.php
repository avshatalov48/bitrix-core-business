<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("main_app_passwords_comp_name"),
	"DESCRIPTION" => GetMessage("main_app_passwords_comp_desc"),
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "user",
			"NAME" => GetMessage("main_app_passwords_comp_user")
		)
	),
);
