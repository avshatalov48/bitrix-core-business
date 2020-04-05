<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arTemplateParameters = Array(
	"SUPPRESS_ERRORS" => Array(
		"NAME" => Loc::getMessage("SALE_SLS_SUPPRESS_ERRORS_PARAMETER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"
	),
	"DISABLE_KEYBOARD_INPUT" => Array(
		"NAME" => Loc::getMessage("SALE_SLS_DISABLE_KEYBOARD_INPUT_PARAMETER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"
	),
	"INITIALIZE_BY_GLOBAL_EVENT" => Array(
		"NAME" => Loc::getMessage("SALE_SLS_INITIALIZE_BY_GLOBAL_EVENT_PARAMETER"),
		"TYPE" => "STRING",
		"DEFAULT" => ""
	)
);