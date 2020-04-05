<?
use \Bitrix\Main\Loader as Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!Loader::includeModule("sale") || !Loader::includeModule("catalog"))
{
	ShowError(GetMessage("SBP_NEED_REQUIRED_MODULES"));
	die();
}

$arComponentParameters = array(
	"GROUPS" => array(
		"DISCOUNT" => array(
			"NAME" => GetMessage("SBP_GROUPS_DISCOUNT"),
		),
		"COUPON" => array(
			"NAME" => GetMessage("SBP_GROUPS_COUPON"),
		),
		"REPL_SETT" => array(
			"NAME" => GetMessage("SBP_GROUPS_REPL_SETT"),
		),
	),
	"PARAMETERS" => array(
		"DISCOUNT_VALUE" => array(
			"PARENT" => "DISCOUNT",
			"NAME" => GetMessage("SBP_PARAMETERS_DISCOUNT_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "10",
		),
		'DISCOUNT_UNIT' => array(
			'PARENT' => 'DISCOUNT',
			'NAME' => GetMessage("SBP_PARAMETERS_DISCOUNT_UNIT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				"Perc" => '%',
				"CurEach" => GetMessage("SBP_PARAMETERS_DISCOUNT_UNIT_EACH"),
				"CurAll" => GetMessage("SBP_PARAMETERS_DISCOUNT_UNIT_ALL"),
			),
			"DEFAULT" => "Prsnt",
		),
		'COUPON_TYPE' => array(
			'PARENT' => 'COUPON',
			'NAME' => GetMessage("SBP_PARAMETERS_COUPON_TYPE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				"Order" => GetMessage("SBP_PARAMETERS_COUPON_TYPE_ORDER"),
				"Basket" => GetMessage("SBP_PARAMETERS_COUPON_TYPE_BASKET"),
			),
			"DEFAULT" => "Order",
		),
		"DISCOUNT_XML_ID" => array(
			"PARENT" => "REPL_SETT",
			"NAME" => GetMessage("SBP_PARAMETERS_REPL_SETT_DISCOUNT_XML_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#SENDER_CHAIN_CODE#}",
		),
		"COUPON_DESCRIPTION" => array(
			"PARENT" => "REPL_SETT",
			"NAME" => GetMessage("SBP_PARAMETERS_REPL_SETT_COUPON_DESCRIPTION"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#EMAIL_TO#}",
		),
	),
);

?>