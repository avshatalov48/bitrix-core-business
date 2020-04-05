<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule('sale');

$arCurrency = array("default" => GetMessage("VIEWED_DEFAULT"));
$dbCurrencyList = CCurrency::GetList(($by="sort"), ($order="asc"));
while ($items = $dbCurrencyList->Fetch())
{
	$arCurrency[htmlspecialcharsbx($items["CURRENCY"])] = htmlspecialcharsbx($items["CURRENCY"])." (".htmlspecialcharsbx($items["FULL_NAME"]).")";
}

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"VIEWED_COUNT" => Array(
			"NAME" => GetMessage("VIEWED_COUNT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "5",
			"COLS" => 5,
			"PARENT" => "BASE",
		),
		"VIEWED_NAME" => Array(
			"NAME"=>GetMessage("VIEWED_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y",
			"PARENT" => "BASE",
		),
		"VIEWED_IMAGE" => Array(
			"NAME"=>GetMessage("VIEWED_IMAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y",
			"PARENT" => "BASE",
		),
		"VIEWED_PRICE" => Array(
			"NAME"=>GetMessage("VIEWED_PRICE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y",
			"PARENT" => "BASE",
		),
		"VIEWED_CURRENCY" => Array(
			"NAME"=>GetMessage("VIEWED_CURRENCY"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"N",
			"DEFAULT"=>"N",
			"VALUES"=>$arCurrency,
			"PARENT" => "BASE",
		),
		"VIEWED_CANBUY" => Array(
			"NAME"=>GetMessage("VIEWED_CANBUY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N",
			"PARENT" => "BASE",
		),
		"VIEWED_CANBASKET" => Array(
			"NAME"=>GetMessage("VIEWED_CANBASKET"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N",
			"PARENT" => "BASE",
		),
		"BASKET_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("VIEWED_BASKET_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "/personal/basket.php",
		),
		"ACTION_VARIABLE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("VIEWED_ACTION_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "action",
		),
		"PRODUCT_ID_VARIABLE" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("VIEWED_PRODUCT_ID_VARIABLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "id",
		),

		"SET_TITLE" => Array(),

	)
);
?>