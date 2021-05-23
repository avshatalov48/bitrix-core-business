<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?><?
/** @var array $arCurrentValues */
use Bitrix\Main\Loader,
	Bitrix\Currency;

if (!Loader::includeModule('currency'))
	return;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"arrCURRENCY_FROM" => array(
			"NAME" => GetMessage("CURRENCY_FROM"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => Currency\CurrencyManager::getCurrencyList(),
			"GROUP" => "BASE",
		),
		"CURRENCY_BASE" => array(
			"NAME" => GetMessage("CURRENCY_BASE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => Currency\CurrencyManager::getCurrencyList(),
			"DEFAULT" => Currency\CurrencyManager::getBaseCurrency(),
			"GROUP" => "BASE",
		),
		"RATE_DAY" => array(
			"NAME" => GetMessage("CURRENCY_RATE_DAY"),
			"TYPE" => "STRING",
			"GROUP" => "ADDITIONAL_PARAMETERS",
			),
		"SHOW_CB" => array(
			"NAME" => GetMessage("T_CURRENCY_CBRF"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES" => "N",
			"GROUP" => "ADDITIONAL_PARAMETERS",
		),
		"CACHE_TIME" => array("DEFAULT" => "86400"),
	),
);