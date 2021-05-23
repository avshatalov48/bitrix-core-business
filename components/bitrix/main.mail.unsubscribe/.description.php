<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("MAIN_MAIL_UNSUBSCRIBE_DESC_NAME"),
	"DESCRIPTION" => GetMessage("MAIN_MAIL_UNSUBSCRIBE_DESC_DESC"),
	"ICON" => "/images/selector.gif",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "user",
			"NAME" => GetMessage("MAIN_USER_GROUP_NAME")
		)
	),
);

?>