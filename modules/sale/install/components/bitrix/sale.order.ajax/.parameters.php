<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Iblock;

if (!Loader::includeModule('sale'))
	return;

$siteId = isset($_REQUEST['src_site']) && is_string($_REQUEST['src_site']) ? $_REQUEST['src_site'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);

$arColumns = array(
	"PREVIEW_PICTURE" => GetMessage("SOA_PREVIEW_PICTURE"),
	"DETAIL_PICTURE" => GetMessage("SOA_DETAIL_PICTURE"),
	"PREVIEW_TEXT" => GetMessage("SOA_PREVIEW_TEXT"),
	"PROPS" => GetMessage("SOA_PROPS"),
	"NOTES" => GetMessage("SOA_PRICE_TYPE"),
	"DISCOUNT_PRICE_PERCENT_FORMATED" => GetMessage("SOA_DISCOUNT"),
	"PRICE_FORMATED" => GetMessage("SOA_PRICE_FORMATED"),
	"WEIGHT_FORMATED" => GetMessage("SOA_WEIGHT")
);

$arIblockIDs = array();
if (Loader::includeModule('catalog'))
{
	$arIblockNames = array();
	$parameters = array(
		'select' => array('IBLOCK_ID', 'NAME' => 'IBLOCK.NAME', 'SITE_ID' => 'IBLOCK_SITE.SITE_ID'),
		'order' => array('IBLOCK_ID' => 'ASC'),
		'filter' => array('SITE_ID' => 's1'),
		'runtime' => array(
			'IBLOCK_SITE' => array(
				'data_type' => 'Bitrix\Iblock\IblockSiteTable',
				'reference' => array(
					'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
				),
				'join_type' => 'inner'
			)
		)
	);
	$parameters = array(
		'select' => array('IBLOCK_ID', 'NAME' => 'IBLOCK.NAME'),
		'order' => array('IBLOCK_ID' => 'ASC'),
	);

	if (!empty($siteId) && is_string($siteId))
	{
		$parameters['select']['SITE_ID'] = 'IBLOCK_SITE.SITE_ID';
		$parameters['filter'] = array('SITE_ID' => $siteId);
		$parameters['runtime'] = array(
			'IBLOCK_SITE' => array(
				'data_type' => 'Bitrix\Iblock\IblockSiteTable',
				'reference' => array(
					'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
				),
				'join_type' => 'inner'
			)
		);
	}

	$catalogIterator = Catalog\CatalogIblockTable::getList($parameters);
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
				$property['CODE'] = $property['ID'];
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
					$arProps[$property['CODE']]['IBLOCK_TITLE'][$property['IBLOCK_ID']] = $arIblockNames[$property['IBLOCK_ID']];
				$arProps[$property['CODE']]['COUNT']++;
			}
		}
		unset($property, $propertyIterator);

		$propList = array();
		foreach ($arProps as &$property)
		{
			$iblockList = '';
			if ($property['COUNT'] > 1)
			{
				$iblockList = ($property['COUNT'] > 2 ? ' ( ... )' : ' ('.implode(', ', $property['IBLOCK_TITLE']).')');
			}
			$propList['PROPERTY_'.$property['CODE']] = $property['TITLE'].$iblockList;
		}
		unset($property, $arProps);

		if (!empty($propList))
			$arColumns = array_merge($arColumns, $propList);
		unset($propList);
	}
}

$arComponentParameters = array(
	"GROUPS" => array(
		"ANALYTICS_SETTINGS" => array(
			"NAME" => GetMessage("SOA_ANALYTICS_SETTINGS")
		),
		"MAIN_MESSAGE_SETTINGS" => array(
			"NAME" => GetMessage("SOA_MAIN_MESSAGE_SETTINGS")
		),
		"ADDITIONAL_MESSAGE_SETTINGS" => array(
			"NAME" => GetMessage("SOA_ADDITIONAL_MESSAGE_SETTINGS")
		),
		"ERROR_MESSAGE_SETTINGS" => array(
			"NAME" => GetMessage("SOA_ERROR_MESSAGE_SETTINGS1")
		)
	),
	"PARAMETERS" => array(
		"USER_CONSENT" => array(),
		"ACTION_VARIABLE" => array(
			"NAME" => GetMessage('SOA_ACTION_VARIABLE'),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "soa-action",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_BASKET" => array(
			"NAME" => GetMessage("SOA_PATH_TO_BASKET1"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/cart/",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PERSONAL" => array(
			"NAME" => GetMessage("SOA_PATH_TO_PERSONAL1"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "index.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => array(
			"NAME" => GetMessage("SOA_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_AUTH" => array(
			"NAME" => GetMessage("SOA_PATH_TO_AUTH1"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/auth/",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PAY_FROM_ACCOUNT" => array(
			"NAME" => GetMessage("SOA_ALLOW_PAY_FROM_ACCOUNT1"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "BASE",
		),
		"ONLY_FULL_PAY_FROM_ACCOUNT" => array(
			"NAME" => GetMessage("SOA_ONLY_FULL_PAY_FROM_ACCOUNT1"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "BASE",
		),
		"ALLOW_AUTO_REGISTER" => array(
			"NAME" => GetMessage("SOA_ALLOW_AUTO_REGISTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "BASE",
		),
		"ALLOW_APPEND_ORDER" => array(
			"NAME" => GetMessage("SOA_ALLOW_APPEND_ORDER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SEND_NEW_USER_NOTIFY" => array(
			"NAME" => GetMessage("SOA_SEND_NEW_USER_NOTIFY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"DELIVERY_NO_AJAX" => array(
			"NAME" => GetMessage("SOA_DELIVERY_NO_AJAX3"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array(
				'N' => GetMessage("SOA_DELIVERY_NO_AJAX_NO"),
				'H' => GetMessage("SOA_DELIVERY_NO_AJAX_HANDLER"),
				'Y' => GetMessage("SOA_DELIVERY_NO_AJAX_YES"),
			),
			"DEFAULT" => "N",
			"REFRESH" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_NOT_CALCULATED_DELIVERIES" => array(
			"NAME" => GetMessage("SOA_SHOW_NOT_CALCULATED_DELIVERIES"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "L",
			"VALUES" => array(
				'N' => GetMessage("SOA_SHOW_NOT_CALCULATED_DELIVERIES_N"),
				'L' => GetMessage("SOA_SHOW_NOT_CALCULATED_DELIVERIES_L"),
				'Y' => GetMessage("SOA_SHOW_NOT_CALCULATED_DELIVERIES_Y"),
			),
			"HIDDEN" => isset($arCurrentValues['DELIVERY_NO_AJAX']) && $arCurrentValues['DELIVERY_NO_AJAX'] === 'Y' ? 'N' : 'Y',
			"PARENT" => "BASE",
		),
		"DELIVERY_NO_SESSION" => array(
			"NAME" => GetMessage("SOA_DELIVERY_NO_SESSION"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"TEMPLATE_LOCATION" => array(
			"NAME" => GetMessage("SBB_TEMPLATE_LOCATION1"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array(
				"popup" => GetMessage("SBB_TMP_POPUP"),
				".default" => GetMessage("SBB_TMP_DEFAULT1")
			),
			"DEFAULT" => "popup",
			"COLS" => 25,
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),
		"SPOT_LOCATION_BY_GEOIP" => array(
			"NAME" => GetMessage("SBB_SPOT_LOCATION_BY_GEOIP"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),
		"DELIVERY_TO_PAYSYSTEM" => array(
			"NAME" => GetMessage("SBB_DELIVERY_PAYSYSTEM"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array(
				"d2p" => GetMessage("SBB_TITLE_PD"),
				"p2d" => GetMessage("SBB_TITLE_DP")
			),
			"PARENT" => "BASE",
		),
		"SHOW_VAT_PRICE" => array(
			"NAME" => GetMessage('SOA_SHOW_VAT_PRICE'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),
		"SET_TITLE" => array(),
		"USE_PREPAYMENT" => array(
			"NAME" => GetMessage('SBB_USE_PREPAYMENT'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "BASE",
		),
		"DISABLE_BASKET_REDIRECT" => array(
			"NAME" => GetMessage('SOA_DISABLE_BASKET_REDIRECT2'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		),
		"EMPTY_BASKET_HINT_PATH" => array(
			"NAME" => GetMessage('SOA_EMPTY_BASKET_HINT_PATH'),
			"TYPE" => "STRING",
			"DEFAULT" => "/"
		),
		"USE_PHONE_NORMALIZATION" => array(
			"NAME" => GetMessage("SOA_USE_PHONE_NORMALIZATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS"
		)
	)
);

//compatibility to old default columns in basket
$defaultColumns = array();
if (!isset($arCurrentValues['PRODUCT_COLUMNS']) && !isset($arCurrentValues['PRODUCT_COLUMNS_VISIBLE']))
	$defaultColumns = array('PREVIEW_PICTURE', 'PROPS');
else if (!isset($arCurrentValues['PRODUCT_COLUMNS_VISIBLE']))
{
	if (isset($arCurrentValues['PRODUCT_COLUMNS']))
		$defaultColumns = array_merge($arCurrentValues['PRODUCT_COLUMNS'], array('PRICE_FORMATED'));
	else
		$defaultColumns = array('PROPS', 'DISCOUNT_PRICE_PERCENT_FORMATED', 'PRICE_FORMATED');
}

$arComponentParameters["PARAMETERS"]["PRODUCT_COLUMNS_VISIBLE"] = array(
	"NAME" => GetMessage("SOA_PRODUCT_COLUMNS"),
	"TYPE" => "LIST",
	"MULTIPLE" => "Y",
	"COLS" => 25,
	"SIZE" => 7,
	"VALUES" => $arColumns,
	"DEFAULT" => $defaultColumns,
	"ADDITIONAL_VALUES" => "N",
	"PARENT" => "ADDITIONAL_SETTINGS",
);

if (is_array($templateProperties['PRODUCT_COLUMNS_HIDDEN']) && !empty($templateProperties['PRODUCT_COLUMNS_HIDDEN']))
{
	$templateProperties['PRODUCT_COLUMNS_HIDDEN']['VALUES'] = $arColumns;
}

if ($arCurrentValues['COUNT_DELIVERY_TAX'] == 'Y')
{
	$arComponentParameters["PARAMETERS"]["COUNT_DELIVERY_TAX"] = array(
		"NAME" => GetMessage("SOA_COUNT_DELIVERY_TAX"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "BASE",
	);
}

$arComponentParameters["PARAMETERS"]['COMPATIBLE_MODE'] =  array(
	"NAME" => GetMessage("SOA_COMPATIBLE_MODE1"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"PARENT" => "BASE"
);

$arComponentParameters["PARAMETERS"]['USE_PRELOAD'] = array(
	"NAME" => GetMessage("SOA_USE_PRELOAD"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"PARENT" => "BASE"
);

foreach ($arIblockIDs as $iblockId)
{
	$fileProperties = array('-' => GetMessage("SOA_DEFAULT"));
	$propertyIterator = CIBlockProperty::getList(
		array("SORT" => "ASC", "NAME" => "ASC"),
		array("IBLOCK_ID" => $iblockId, "ACTIVE" => "Y")
	);
	while ($property = $propertyIterator->fetch())
	{
		if ($property['PROPERTY_TYPE'] == 'F')
		{
			$property['ID'] = (int)$property['ID'];
			$propertyName = '['.$property['ID'].']'.($property['CODE'] != '' ? '['.$property['CODE'].']' : '').' '.$property['NAME'];
			if ($property['CODE'] == '')
				$property['CODE'] = $property['ID'];

			$fileProperties[$property['CODE']] = $propertyName;
		}
	}

	$arComponentParameters["PARAMETERS"]['ADDITIONAL_PICT_PROP_'.$iblockId] = array(
		"NAME" => GetMessage("SOA_ADDITIONAL_IMAGE").' ['.$arIblockNames[$iblockId].']',
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" =>  $fileProperties,
		"ADDITIONAL_VALUES" => "N",
		"PARENT" => 'ADDITIONAL_SETTINGS'
	);
}

$arComponentParameters["PARAMETERS"]['BASKET_IMAGES_SCALING'] =  array(
	"NAME" => GetMessage("SOA_BASKET_IMAGES_SCALING"),
	"TYPE" => "LIST",
	"VALUES" => array(
		'standard' => GetMessage("SOA_STANDARD"),
		'adaptive' => GetMessage("SOA_ADAPTIVE"),
		'no_scale' => GetMessage("SOA_NO_SCALE")
	),
	"DEFAULT" => "adaptive",
	"PARENT" => "ADDITIONAL_SETTINGS"
);