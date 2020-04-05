<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\ModuleManager;

$arPrice = array();
if(CModule::IncludeModule("catalog"))
{
	$rsPrice=CCatalogGroup::GetList($v1="sort", $v2="asc");
	while($arr=$rsPrice->Fetch())
		$arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];
}

$arTemplateParameters = array(
	"SHOW_INPUT" => array(
		"NAME" => GetMessage("TP_BST_SHOW_INPUT"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	),
	"INPUT_ID" => array(
		"NAME" => GetMessage("TP_BST_INPUT_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => "title-search-input",
	),
	"CONTAINER_ID" => array(
		"NAME" => GetMessage("TP_BST_CONTAINER_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => "title-search",
	),
	"PRICE_CODE" => array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("TP_BST_PRICE_CODE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arPrice,
	),
	"PRICE_VAT_INCLUDE" => array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("TP_BST_PRICE_VAT_INCLUDE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"PREVIEW_TRUNCATE_LEN" => array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("TP_BST_PREVIEW_TRUNCATE_LEN"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	),
	"SHOW_PREVIEW" => array(
		"NAME" => GetMessage("TP_BST_SHOW_PREVIEW"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	),
);

if (isset($arCurrentValues['SHOW_PREVIEW']) && 'Y' == $arCurrentValues['SHOW_PREVIEW'])
{
	$arTemplateParameters["PREVIEW_WIDTH"] = array(
		"NAME" => GetMessage("TP_BST_PREVIEW_WIDTH"),
		"TYPE" => "STRING",
		"DEFAULT" => 75,
	);
	$arTemplateParameters["PREVIEW_HEIGHT"] = array(
		"NAME" => GetMessage("TP_BST_PREVIEW_HEIGHT"),
		"TYPE" => "STRING",
		"DEFAULT" => 75,
	);
}

if (CModule::IncludeModule('catalog') && CModule::IncludeModule('currency'))
{
	$arTemplateParameters['CONVERT_CURRENCY'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('TP_BST_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
	{
		$arCurrencyList = array();
		$rsCurrencies = CCurrency::GetList(($by = 'SORT'), ($order = 'ASC'));
		while ($arCurrency = $rsCurrencies->Fetch())
		{
			$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
		}
		$arTemplateParameters['CURRENCY_ID'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('TP_BST_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arCurrencyList,
			'DEFAULT' => CCurrency::GetBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}
}

$arThemes = array(
	'blue' => GetMessage('TP_BST_THEME_BLUE'),
	'green' => GetMessage('TP_BST_THEME_GREEN'),
	'red' => GetMessage('TP_BST_THEME_RED'),
	'yellow' => GetMessage('TP_BST_THEME_YELLOW'),
);

if (ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('TP_BST_THEME_SITE');
}

$arTemplateParameters['TEMPLATE_THEME'] = array(
	'PARENT' => 'VISUAL',
	'NAME' => GetMessage("TP_BST_TEMPLATE_THEME"),
	'TYPE' => 'LIST',
	'VALUES' => $arThemes,
	'DEFAULT' => 'blue',
	'ADDITIONAL_VALUES' => 'Y'
);

?>
