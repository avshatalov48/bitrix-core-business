<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPHEEA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPHEEA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "HandleExternalEventActivity",
	"JSCLASS" => "HandleExternalEventActivity",
	"CATEGORY" => array(
		"ID" => "logic",
	),
	"RETURN" => array(
		"SenderUserId" => array(
			"NAME" => GetMessage("BPAA_DESCR_SENDER_USER_ID"),
			"TYPE" => "user",
		),
	)
);
?>
