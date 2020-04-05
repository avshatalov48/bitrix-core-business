<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("CD_BSAC_NAME"),
	"DESCRIPTION" => GetMessage("CD_BCI1_DESCRIPTION"),
	"ICON" => "/images/user_authform.gif",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "user",
			"NAME" => GetMessage("MAIN_USER_GROUP_NAME")
		),
	),
);
?>