<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("SALE_BVAL_MAIL_NAME"),
	"DESCRIPTION" => GetMessage("SALE_BVAL_MAIL_DESC"),
	"SORT" => 20,
	"TYPE" => "mail",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "e-store",
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("SALE_BVAL_MAIL_CHILD"),
			"SORT" => 10,
		),
	),
);

?>