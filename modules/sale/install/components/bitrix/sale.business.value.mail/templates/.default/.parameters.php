<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters = array(
	"DISPLAY_NAME" => Array(
		"NAME" => GetMessage('SALE_BVAL_MAIL_DISPLAY_NAME'),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"DISPLAY_EMPTY" => Array(
		"NAME" => GetMessage('SALE_BVAL_MAIL_DISPLAY_EMPTY'),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
);
?>
