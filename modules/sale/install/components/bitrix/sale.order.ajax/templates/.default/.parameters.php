<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Iblock;

if (!Loader::includeModule('sale'))
	return;

$arThemes = array();
if ($eshop = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix.eshop'))
{
	$arThemes['site'] = GetMessage('THEME_SITE');
}
$arThemesList = array(
	'blue' => GetMessage('THEME_BLUE'),
	'green' => GetMessage('THEME_GREEN'),
	'red' => GetMessage('THEME_RED'),
	'yellow' => GetMessage('THEME_YELLOW')
);
$dir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/main/themes/";
if (is_dir($dir))
{
	foreach ($arThemesList as $themeID => $themeName)
	{
		if (!is_file($dir.$themeID.'/style.css'))
			continue;
		$arThemes[$themeID] = $themeName;
	}
}

$arTemplateParameters = array(
	"TEMPLATE_THEME" => array(
		"NAME" => GetMessage("TEMPLATE_THEME"),
		"TYPE" => "LIST",
		'VALUES' => $arThemes,
		'DEFAULT' => $eshop ? 'site' : 'blue',
		"PARENT" => "VISUAL"
	),
	"SHOW_ORDER_BUTTON" => array(
		"NAME" => GetMessage("SHOW_ORDER_BUTTON"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'final_step' => GetMessage("SHOW_FINAL_STEP"),
			'always' => GetMessage("SHOW_ALWAYS")
		),
		"PARENT" => "VISUAL",
	),
	"SHOW_TOTAL_ORDER_BUTTON" => array(
		"NAME" => GetMessage("SHOW_TOTAL_ORDER_BUTTON"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "VISUAL",
	),
	"SHOW_PAY_SYSTEM_LIST_NAMES" => array(
		"NAME" => GetMessage("SHOW_PAY_SYSTEM_LIST_NAMES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_PAY_SYSTEM_INFO_NAME" => array(
		"NAME" => GetMessage("SHOW_PAY_SYSTEM_INFO_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_DELIVERY_LIST_NAMES" => array(
		"NAME" => GetMessage("SHOW_DELIVERY_LIST_NAMES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_DELIVERY_INFO_NAME" => array(
		"NAME" => GetMessage("SHOW_DELIVERY_INFO_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_DELIVERY_PARENT_NAMES" => array(
		"NAME" => GetMessage("DELIVERY_PARENT_NAMES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_STORES_IMAGES" => array(
		"NAME" => GetMessage("SHOW_STORES_IMAGES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SKIP_USELESS_BLOCK" => array(
		"NAME" => GetMessage("SKIP_USELESS_BLOCK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"BASKET_POSITION" => array(
		"NAME" => GetMessage("BASKET_POSITION"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => array(
			"after" => GetMessage("BASKET_POSITION_AFTER"),
			"before" => GetMessage("BASKET_POSITION_BEFORE")
		),
		"DEFAULT" => "after",
		"PARENT" => "VISUAL"
	),
	"SHOW_BASKET_HEADERS" => array(
		"NAME" => GetMessage("SHOW_BASKET_HEADERS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "VISUAL",
	),
	"DELIVERY_FADE_EXTRA_SERVICES" => array(
		"NAME" => GetMessage("DELIVERY_FADE_EXTRA_SERVICES"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N",
		"PARENT" => "VISUAL",
	),
	"SHOW_COUPONS_BASKET" => array(
		"NAME" => GetMessage("SHOW_COUPONS_BASKET"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_COUPONS_DELIVERY" => array(
		"NAME" => GetMessage("SHOW_COUPONS_DELIVERY"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_COUPONS_PAY_SYSTEM" => array(
		"NAME" => GetMessage("SHOW_COUPONS_PAY_SYSTEM"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_NEAREST_PICKUP" => array(
		"NAME" => GetMessage("SHOW_NEAREST_PICKUP"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N",
		"PARENT" => "VISUAL",
	),
	"DELIVERIES_PER_PAGE" => array(
		"NAME" => GetMessage("DELIVERIES_PER_PAGE"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "9",
		"PARENT" => "VISUAL",
	),
	"PAY_SYSTEMS_PER_PAGE" => array(
		"NAME" => GetMessage("PAY_SYSTEMS_PER_PAGE"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "9",
		"PARENT" => "VISUAL",
	),
	"PICKUPS_PER_PAGE" => array(
		"NAME" => GetMessage("PICKUPS_PER_PAGE"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "5",
		"PARENT" => "VISUAL",
	),
	"SHOW_PICKUP_MAP" => array(
		"NAME" => GetMessage("SHOW_PICKUP_MAP"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y",
		"PARENT" => "VISUAL",
	),
	"SHOW_MAP_IN_PROPS" => array(
		"NAME" => GetMessage("SHOW_MAP_IN_PROPS"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"PARENT" => "VISUAL",
	),
	"PICKUP_MAP_TYPE" => array(
		"NAME" => GetMessage("PICKUP_MAP_TYPE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => array(
			"yandex" => GetMessage("PICKUP_MAP_TYPE_YANDEX"),
			"google" => GetMessage("PICKUP_MAP_TYPE_GOOGLE")
		),
		"DEFAULT" => "yandex",
		"PARENT" => "VISUAL"
	),
	"SERVICES_IMAGES_SCALING" =>  array(
		"NAME" => GetMessage("SERVICES_IMAGES_SCALING"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'standard' => GetMessage("SOA_STANDARD"),
			'adaptive' => GetMessage("SOA_ADAPTIVE"),
			'no_scale' => GetMessage("SOA_NO_SCALE")
		),
		"DEFAULT" => "adaptive",
		"PARENT" => "ADDITIONAL_SETTINGS"
	),
	"PRODUCT_COLUMNS_HIDDEN" => array(
		"NAME" => GetMessage("PRODUCT_COLUMNS_HIDDEN"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"COLS" => 25,
		"SIZE" => 7,
		"VALUES" => array(),
		"DEFAULT" => array(),
		"ADDITIONAL_VALUES" => "N",
		"PARENT" => "ADDITIONAL_SETTINGS"
	),
	"HIDE_ORDER_DESCRIPTION" => array(
		"NAME" => GetMessage("HIDE_ORDER_DESCRIPTION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ADDITIONAL_SETTINGS"
	),
	"ALLOW_USER_PROFILES" => array(
		"NAME" => GetMessage("ALLOW_USER_PROFILES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"PARENT" => "BASE"
	),
	"ALLOW_NEW_PROFILE" => array(
		"NAME" => GetMessage("ALLOW_NEW_PROFILE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $arCurrentValues['ALLOW_USER_PROFILES'] !== 'Y' ? 'Y' : 'N',
		"PARENT" => "BASE"
	),
	"USE_YM_GOALS" => array(
		"NAME" => GetMessage("USE_YM_GOALS1"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
		"PARENT" => "ANALYTICS_SETTINGS"
	)
);

if ($arCurrentValues['USE_YM_GOALS'] == 'Y')
{
	$arTemplateParameters["YM_GOALS_COUNTER"] = array(
		"NAME" => GetMessage("YM_GOALS_COUNTER"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_INITIALIZE"] = array(
		"NAME" => GetMessage("YM_GOALS_INITIALIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-order-init",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_EDIT_REGION"] = array(
		"NAME" => GetMessage("YM_GOALS_EDIT_REGION"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-region-edit",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_EDIT_DELIVERY"] = array(
		"NAME" => GetMessage("YM_GOALS_EDIT_DELIVERY"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-delivery-edit",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_EDIT_PICKUP"] = array(
		"NAME" => GetMessage("YM_GOALS_EDIT_PICKUP"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-pickUp-edit",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_EDIT_PAY_SYSTEM"] = array(
		"NAME" => GetMessage("YM_GOALS_EDIT_PAY_SYSTEM"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-paySystem-edit",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_EDIT_PROPERTIES"] = array(
		"NAME" => GetMessage("YM_GOALS_EDIT_PROPERTIES"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-properties-edit",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_EDIT_BASKET"] = array(
		"NAME" => GetMessage("YM_GOALS_EDIT_BASKET"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-basket-edit",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_NEXT_REGION"] = array(
		"NAME" => GetMessage("YM_GOALS_NEXT_REGION"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-region-next",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_NEXT_DELIVERY"] = array(
		"NAME" => GetMessage("YM_GOALS_NEXT_DELIVERY"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-delivery-next",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_NEXT_PICKUP"] = array(
		"NAME" => GetMessage("YM_GOALS_NEXT_PICKUP"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-pickUp-next",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_NEXT_PAY_SYSTEM"] = array(
		"NAME" => GetMessage("YM_GOALS_NEXT_PAY_SYSTEM"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-paySystem-next",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_NEXT_PROPERTIES"] = array(
		"NAME" => GetMessage("YM_GOALS_NEXT_PROPERTIES"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-properties-next",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_NEXT_BASKET"] = array(
		"NAME" => GetMessage("YM_GOALS_NEXT_BASKET"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-basket-next",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
	$arTemplateParameters["YM_GOALS_SAVE_ORDER"] = array(
		"NAME" => GetMessage("YM_GOALS_SAVE_ORDER"),
		"TYPE" => "STRING",
		"DEFAULT" => "BX-order-save",
		"PARENT" => "ANALYTICS_SETTINGS"
	);
}

$arTemplateParameters['USE_ENHANCED_ECOMMERCE'] = array(
	'PARENT' => 'ANALYTICS_SETTINGS',
	'NAME' => GetMessage('USE_ENHANCED_ECOMMERCE'),
	'TYPE' => 'CHECKBOX',
	'REFRESH' => 'Y',
	'DEFAULT' => 'N'
);

if (isset($arCurrentValues['USE_ENHANCED_ECOMMERCE']) && $arCurrentValues['USE_ENHANCED_ECOMMERCE'] === 'Y')
{
	if (Loader::includeModule('catalog'))
	{
		$arIblockIDs = array();
		$arIblockNames = array();
		$catalogIterator = Catalog\CatalogIblockTable::getList(array(
			'select' => array('IBLOCK_ID', 'NAME' => 'IBLOCK.NAME'),
			'order' => array('IBLOCK_ID' => 'ASC')
		));
		while ($catalog = $catalogIterator->fetch())
		{
			$catalog['IBLOCK_ID'] = (int)$catalog['IBLOCK_ID'];
			$arIblockIDs[] = $catalog['IBLOCK_ID'];
			$arIblockNames[$catalog['IBLOCK_ID']] = $catalog['NAME'];
		}
		unset($catalog, $catalogIterator);

		if (!empty($arIblockIDs))
		{
			$arProps = array();
			$propertyIterator = Iblock\PropertyTable::getList(array(
				'select' => array('ID', 'CODE', 'NAME', 'IBLOCK_ID'),
				'filter' => array('@IBLOCK_ID' => $arIblockIDs, '=ACTIVE' => 'Y', '!=XML_ID' => CIBlockPropertyTools::XML_SKU_LINK),
				'order' => array('IBLOCK_ID' => 'ASC', 'SORT' => 'ASC', 'ID' => 'ASC')
			));
			while ($property = $propertyIterator->fetch())
			{
				$property['ID'] = (int)$property['ID'];
				$property['IBLOCK_ID'] = (int)$property['IBLOCK_ID'];
				$property['CODE'] = (string)$property['CODE'];

				if ($property['CODE'] == '')
				{
					$property['CODE'] = $property['ID'];
				}

				if (!isset($arProps[$property['CODE']]))
				{
					$arProps[$property['CODE']] = array(
						'CODE' => $property['CODE'],
						'TITLE' => $property['NAME'].' ['.$property['CODE'].']',
						'ID' => array($property['ID']),
						'IBLOCK_ID' => array($property['IBLOCK_ID'] => $property['IBLOCK_ID']),
						'IBLOCK_TITLE' => array($property['IBLOCK_ID'] => $arIblockNames[$property['IBLOCK_ID']]),
						'COUNT' => 1
					);
				}
				else
				{
					$arProps[$property['CODE']]['ID'][] = $property['ID'];
					$arProps[$property['CODE']]['IBLOCK_ID'][$property['IBLOCK_ID']] = $property['IBLOCK_ID'];

					if ($arProps[$property['CODE']]['COUNT'] < 2)
					{
						$arProps[$property['CODE']]['IBLOCK_TITLE'][$property['IBLOCK_ID']] = $arIblockNames[$property['IBLOCK_ID']];
					}

					$arProps[$property['CODE']]['COUNT']++;
				}
			}
			unset($property, $propertyIterator, $arIblockNames, $arIblockIDs);

			$propList = array();
			foreach ($arProps as $property)
			{
				$iblockList = '';

				if ($property['COUNT'] > 1)
				{
					$iblockList = ($property['COUNT'] > 2 ? ' ( ... )' : ' ('.implode(', ', $property['IBLOCK_TITLE']).')');
				}

				$propList['PROPERTY_'.$property['CODE']] = $property['TITLE'].$iblockList;
			}
			unset($property, $arProps);
		}
	}

	$arTemplateParameters['DATA_LAYER_NAME'] = array(
		'PARENT' => 'ANALYTICS_SETTINGS',
		'NAME' => GetMessage('DATA_LAYER_NAME'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'dataLayer'
	);

	if (!empty($propList))
	{
		$arTemplateParameters['BRAND_PROPERTY'] = array(
			'PARENT' => 'ANALYTICS_SETTINGS',
			'NAME' => GetMessage('BRAND_PROPERTY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'DEFAULT' => '',
			'VALUES' => array('' => '') + $propList
		);
	}
}

if ($arCurrentValues['SHOW_MAP_IN_PROPS'] == 'Y')
{
	$arDelivery = array();
	$services = Bitrix\Sale\Delivery\Services\Manager::getActiveList();
	foreach ($services as $service)
	{
		$arDelivery[$service['ID']] = $service['NAME'];
	}

	$arTemplateParameters["SHOW_MAP_FOR_DELIVERIES"] =  array(
		"NAME" => GetMessage("SHOW_MAP_FOR_DELIVERIES"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arDelivery,
		"DEFAULT" => "",
		"COLS" => 25,
		"ADDITIONAL_VALUES" => "N",
		"PARENT" => "VISUAL"
	);
}

$dbPerson = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array('ACTIVE' => 'Y'));
while ($arPerson = $dbPerson->GetNext())
{
	$arPers2Prop = array();

	$dbProp = CSaleOrderProps::GetList(
		array("SORT" => "ASC", "NAME" => "ASC"),
		array("PERSON_TYPE_ID" => $arPerson["ID"], 'UTIL' => 'N')
	);
	while ($arProp = $dbProp->Fetch())
	{
		if ($arProp["IS_LOCATION"] == 'Y')
		{
			if (intval($arProp["INPUT_FIELD_LOCATION"]) > 0)
				$altPropId = $arProp["INPUT_FIELD_LOCATION"];

			continue;
		}

		$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
	}

	if (isset($altPropId))
		unset($arPers2Prop[$altPropId]);

	if (!empty($arPers2Prop))
	{
		$arTemplateParameters["PROPS_FADE_LIST_".$arPerson["ID"]] =  array(
			"NAME" => GetMessage("PROPS_FADE_LIST").' ('.$arPerson["NAME"].')'.'['.$arPerson["LID"].']',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arPers2Prop,
			"DEFAULT" => "",
			"COLS" => 25,
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "VISUAL"
		);
	}
}
unset($arPerson, $dbPerson);

$arTemplateParameters["USE_CUSTOM_MAIN_MESSAGES"] =  array(
	"NAME" => GetMessage("USE_CUSTOM_MESSAGES"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => 'Y',
	"DEFAULT" => 'N',
	"PARENT" => "MAIN_MESSAGE_SETTINGS"
);

if ($arCurrentValues['USE_CUSTOM_MAIN_MESSAGES'] == 'Y')
{
	$arTemplateParameters["MESS_AUTH_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("AUTH_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("AUTH_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_REG_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("REG_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("REG_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_BASKET_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("BASKET_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("BASKET_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_REGION_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("REGION_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("REGION_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_PAYMENT_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("PAYMENT_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PAYMENT_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_DELIVERY_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("DELIVERY_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("DELIVERY_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_BUYER_BLOCK_NAME"] =  array(
		"NAME" => GetMessage("BUYER_BLOCK_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("BUYER_BLOCK_NAME_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_BACK"] =  array(
		"NAME" => GetMessage("BACK"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("BACK_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_FURTHER"] =  array(
		"NAME" => GetMessage("FURTHER"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("FURTHER_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_EDIT"] =  array(
		"NAME" => GetMessage("EDIT"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("EDIT_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_ORDER"] =  array(
		"NAME" => GetMessage("ORDER"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("ORDER_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_PRICE"] =  array(
		"NAME" => GetMessage("PRICE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PRICE_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_PERIOD"] =  array(
		"NAME" => GetMessage("PERIOD"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PERIOD_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_NAV_BACK"] =  array(
		"NAME" => GetMessage("NAV_BACK"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("NAV_BACK_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_NAV_FORWARD"] =  array(
		"NAME" => GetMessage("NAV_FORWARD"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("NAV_FORWARD_DEFAULT"),
		"PARENT" => "MAIN_MESSAGE_SETTINGS"
	);
}

$arTemplateParameters["USE_CUSTOM_ADDITIONAL_MESSAGES"] =  array(
	"NAME" => GetMessage("USE_CUSTOM_MESSAGES"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => 'Y',
	"DEFAULT" => 'N',
	"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
);

if ($arCurrentValues['USE_CUSTOM_ADDITIONAL_MESSAGES'] == 'Y')
{
	$arTemplateParameters["MESS_PRICE_FREE"] =  array(
		"NAME" => GetMessage("PRICE_FREE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PRICE_FREE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_ECONOMY"] =  array(
		"NAME" => GetMessage("ECONOMY"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("ECONOMY_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_REGISTRATION_REFERENCE"] =  array(
		"NAME" => GetMessage("REGISTRATION_REFERENCE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("REGISTRATION_REFERENCE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_AUTH_REFERENCE_1"] =  array(
		"NAME" => GetMessage("AUTH_REFERENCE_1"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("AUTH_REFERENCE_1_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_AUTH_REFERENCE_2"] =  array(
		"NAME" => GetMessage("AUTH_REFERENCE_2"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("AUTH_REFERENCE_2_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_AUTH_REFERENCE_3"] =  array(
		"NAME" => GetMessage("AUTH_REFERENCE_3"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("AUTH_REFERENCE_3_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_ADDITIONAL_PROPS"] =  array(
		"NAME" => GetMessage("ADDITIONAL_PROPS"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("ADDITIONAL_PROPS_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_USE_COUPON"] =  array(
		"NAME" => GetMessage("USE_COUPON"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("USE_COUPON_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_COUPON"] =  array(
		"NAME" => GetMessage("COUPON"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("COUPON_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_PERSON_TYPE"] =  array(
		"NAME" => GetMessage("PERSON_TYPE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PERSON_TYPE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_SELECT_PROFILE"] =  array(
		"NAME" => GetMessage("SELECT_PROFILE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("SELECT_PROFILE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_REGION_REFERENCE"] =  array(
		"NAME" => GetMessage("REGION_REFERENCE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("REGION_REFERENCE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_PICKUP_LIST"] =  array(
		"NAME" => GetMessage("PICKUP_LIST"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PICKUP_LIST_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_NEAREST_PICKUP_LIST"] =  array(
		"NAME" => GetMessage("NEAREST_PICKUP_LIST"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("NEAREST_PICKUP_LIST_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_SELECT_PICKUP"] =  array(
		"NAME" => GetMessage("SELECT_PICKUP"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("SELECT_PICKUP_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_INNER_PS_BALANCE"] =  array(
		"NAME" => GetMessage("INNER_PS_BALANCE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("INNER_PS_BALANCE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_INNER_PS_BALANCE"] =  array(
		"NAME" => GetMessage("INNER_PS_BALANCE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("INNER_PS_BALANCE_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_ORDER_DESC"] =  array(
		"NAME" => GetMessage("ORDER_DESC"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("ORDER_DESC_DEFAULT"),
		"PARENT" => "ADDITIONAL_MESSAGE_SETTINGS"
	);
}

$arTemplateParameters["USE_CUSTOM_ERROR_MESSAGES"] =  array(
	"NAME" => GetMessage("USE_CUSTOM_MESSAGES"),
	"TYPE" => "CHECKBOX",
	"REFRESH" => 'Y',
	"DEFAULT" => 'N',
	"PARENT" => "ERROR_MESSAGE_SETTINGS"
);

if ($arCurrentValues['USE_CUSTOM_ERROR_MESSAGES'] == 'Y')
{
	$arTemplateParameters["MESS_SUCCESS_PRELOAD_TEXT"] =  array(
		"NAME" => GetMessage("SUCCESS_PRELOAD_TEXT"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("SUCCESS_PRELOAD_TEXT_DEFAULT"),
		"PARENT" => "ERROR_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_FAIL_PRELOAD_TEXT"] =  array(
		"NAME" => GetMessage("FAIL_PRELOAD_TEXT"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("FAIL_PRELOAD_TEXT_DEFAULT"),
		"PARENT" => "ERROR_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_DELIVERY_CALC_ERROR_TITLE"] =  array(
		"NAME" => GetMessage("DELIVERY_CALC_ERROR_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("DELIVERY_CALC_ERROR_TITLE_DEFAULT"),
		"PARENT" => "ERROR_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_DELIVERY_CALC_ERROR_TEXT"] =  array(
		"NAME" => GetMessage("DELIVERY_CALC_ERROR_TEXT"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("DELIVERY_CALC_ERROR_TEXT_DEFAULT"),
		"PARENT" => "ERROR_MESSAGE_SETTINGS"
	);
	$arTemplateParameters["MESS_PAY_SYSTEM_PAYABLE_ERROR"] =  array(
		"NAME" => GetMessage("PAY_SYSTEM_PAYABLE_ERROR_TEXT"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("PAY_SYSTEM_PAYABLE_ERROR_DEFAULT"),
		"PARENT" => "ERROR_MESSAGE_SETTINGS"
	);
}