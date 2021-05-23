<?
use Bitrix\Main\Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"PRODUCT_ID" => Array(
			"NAME" => GetMessage("CPSP_LABLE_PRODUCT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"BUTTON_ID" => Array(
			"NAME" => GetMessage("CPSP_LABLE_BUTTON_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"BUTTON_CLASS" => Array(
			"NAME" => GetMessage("CPSP_LABLE_BUTTON_CLASS"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"CACHE_TIME" => array("DEFAULT" => 3600),
	)
);