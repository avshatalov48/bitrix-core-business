<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @global array $arCurrentValues */
/** @var array $templateProperties */

use Bitrix\Main\Loader,
	Bitrix\Catalog,
	Bitrix\Iblock;

if (!Loader::includeModule('sale'))
	return;

$arColumns = array(
	"PREVIEW_PICTURE" => GetMessage("SBB_PREVIEW_PICTURE"),
	"DETAIL_PICTURE" => GetMessage("SBB_DETAIL_PICTURE"),
	"PREVIEW_TEXT" => GetMessage("SBB_PREVIEW_TEXT"),
	"DISCOUNT" => GetMessage("SBB_BDISCOUNT"),
	"WEIGHT" => GetMessage("SBB_BWEIGHT"),
	"PROPS" => GetMessage("SBB_BPROPS"),
	"DELETE" => GetMessage("SBB_BDELETE"),
	"DELAY" => GetMessage("SBB_BDELAY"),
	"TYPE" => GetMessage("SBB_BTYPE"),
	"SUM" => GetMessage("SBB_BSUM")
);

$iblockIds = array();
$iblockNames = array();

if (Loader::includeModule('catalog'))
{
	$parameters = array(
		'select' => array('IBLOCK_ID', 'NAME' => 'IBLOCK.NAME'),
		'order' => array('IBLOCK_ID' => 'ASC'),
	);

	$siteId = isset($_REQUEST['src_site']) && is_string($_REQUEST['src_site']) ? $_REQUEST['src_site'] : '';
	$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
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
		$iblockIds[] = $catalog['IBLOCK_ID'];
		$iblockNames[$catalog['IBLOCK_ID']] = $catalog['NAME'];
	}
	unset($catalog, $catalogIterator);

	$listProperties = array();

	if (!empty($iblockIds))
	{
		$arProps = array();
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'CODE', 'NAME', 'IBLOCK_ID', 'PROPERTY_TYPE'),
			'filter' => array('@IBLOCK_ID' => $iblockIds, '=ACTIVE' => 'Y', '!=XML_ID' => CIBlockPropertyTools::XML_SKU_LINK),
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

			if ($property['PROPERTY_TYPE'] === 'L')
			{
				$listProperties[$property['CODE']] = $property['NAME'].' ['.$property['CODE'].']';
			}

			if (!isset($arProps[$property['CODE']]))
			{
				$arProps[$property['CODE']] = array(
					'CODE' => $property['CODE'],
					'TITLE' => $property['NAME'].' ['.$property['CODE'].']',
					'ID' => array($property['ID']),
					'IBLOCK_ID' => array($property['IBLOCK_ID'] => $property['IBLOCK_ID']),
					'IBLOCK_TITLE' => array($property['IBLOCK_ID'] => $iblockNames[$property['IBLOCK_ID']]),
					'COUNT' => 1
				);
			}
			else
			{
				$arProps[$property['CODE']]['ID'][] = $property['ID'];
				$arProps[$property['CODE']]['IBLOCK_ID'][$property['IBLOCK_ID']] = $property['IBLOCK_ID'];
				if ($arProps[$property['CODE']]['COUNT'] < 2)
					$arProps[$property['CODE']]['IBLOCK_TITLE'][$property['IBLOCK_ID']] = $iblockNames[$property['IBLOCK_ID']];
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

$arYesNo = array(
	"Y" => GetMessage("SBB_DESC_YES"),
	"N" => GetMessage("SBB_DESC_NO"),
);

$arComponentParameters = Array(
	"GROUPS" => array(
		"OFFERS_PROPS" => array(
			"NAME" => GetMessage("SBB_OFFERS_PROPS"),
		),
		"IMAGE_SETTINGS" => array(
			"NAME" => GetMessage("SBB_IMAGE_SETTINGS")
		),
		"GIFTS" => array(
			"NAME" => GetMessage("SBB_GIFTS"),
		),
		'ANALYTICS_SETTINGS' => array(
			'NAME' => GetMessage('SBB_ANALYTICS_SETTINGS'),
			'SORT' => 11000
		)
	),
	"PARAMETERS" => Array(
		"PATH_TO_ORDER" => Array(
			"NAME" => GetMessage("SBB_PATH_TO_ORDER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order/make/",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"HIDE_COUPON" => Array(
			"NAME" => GetMessage("SBB_HIDE_COUPON"),
			"TYPE" => "CHECKBOX",
			"VALUES" => array(
				"N" => GetMessage("SBB_DESC_NO"),
				"Y" => GetMessage("SBB_DESC_YES")
			),
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"COLUMNS_LIST_EXT" => Array(
			"NAME" => GetMessage("SBB_COLUMNS_LIST"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arColumns,
			"DEFAULT" => array('PREVIEW_PICTURE', 'DISCOUNT', 'DELETE', 'DELAY', 'TYPE', 'SUM'),
			"COLS" => 25,
			"SIZE" => 7,
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "VISUAL",
			'REFRESH' => isset($templateProperties['COLUMNS_LIST_MOBILE']) ? 'Y' : 'N',
		),
		"COLUMNS_LIST_MOBILE" => array(),
		"PRICE_VAT_SHOW_VALUE" => array(
			"NAME" => GetMessage('SBB_VAT_SHOW_VALUE'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"USE_PREPAYMENT" => array(
			"NAME" => GetMessage('SBB_USE_PREPAYMENT'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"QUANTITY_FLOAT" => array(
			"NAME" => GetMessage('SBB_QUANTITY_FLOAT'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"CORRECT_RATIO" => array(
			"NAME" => GetMessage('SBB_CORRECT_RATIO'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"AUTO_CALCULATION" => array(
			"NAME" => GetMessage('SBB_AUTO_CALCULATION'),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" => Array(),
		"ACTION_VARIABLE" => array(
			"NAME" => GetMessage('SBB_ACTION_VARIABLE'),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "basketAction",
			"ADDITIONAL_VALUES" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		'COMPATIBLE_MODE' => array(
			'PARENT' => 'EXTENDED_SETTINGS',
			'NAME' => GetMessage('SBB_COMPATIBLE_MODE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y'
		),
		"USE_GIFTS" => array(
			"PARENT" => "GIFTS",
			"NAME" => GetMessage("SBB_GIFTS_USE_GIFTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"REFRESH" => "Y",
		),
	),
);

foreach ($iblockIds as $iblockId)
{
	$fileProperties = array('-' => GetMessage('SBB_DEFAULT'));
	$propertyIterator = CIBlockProperty::getList(
		array('SORT' => 'ASC', 'NAME' => 'ASC'),
		array('IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y')
	);
	while ($property = $propertyIterator->fetch())
	{
		if ($property['PROPERTY_TYPE'] == 'F')
		{
			$property['ID'] = (int)$property['ID'];
			$propertyName = '['.$property['ID'].']'.($property['CODE'] != '' ? '['.$property['CODE'].']' : '').' '.$property['NAME'];
			if ($property['CODE'] == '')
			{
				$property['CODE'] = $property['ID'];
			}

			$fileProperties[$property['CODE']] = $propertyName;
		}
	}

	$arComponentParameters['PARAMETERS']['ADDITIONAL_PICT_PROP_'.$iblockId] = array(
		'NAME' => GetMessage('SBB_ADDITIONAL_IMAGE').' ['.$iblockNames[$iblockId].']',
		'TYPE' => 'LIST',
		'MULTIPLE' => 'N',
		'VALUES' => $fileProperties,
		'ADDITIONAL_VALUES' => 'N',
		'PARENT' => 'IMAGE_SETTINGS'
	);
}

$arComponentParameters['PARAMETERS']['BASKET_IMAGES_SCALING'] = array(
	'NAME' => GetMessage('SBB_BASKET_IMAGES_SCALING'),
	'TYPE' => 'LIST',
	'VALUES' => array(
		'standard' => GetMessage('SBB_STANDARD'),
		'adaptive' => GetMessage('SBB_ADAPTIVE'),
		'no_scale' => GetMessage('SBB_NO_SCALE')
	),
	'DEFAULT' => 'adaptive',
	'PARENT' => 'IMAGE_SETTINGS'
);

// hack for correct sort
if (isset($templateProperties['COLUMNS_LIST_MOBILE']))
{
	$visibleColumns = isset($arCurrentValues['COLUMNS_LIST_EXT'])
		? $arCurrentValues['COLUMNS_LIST_EXT']
		: $arComponentParameters['PARAMETERS']['COLUMNS_LIST_EXT']['DEFAULT'];

	if (!empty($visibleColumns))
	{
		$templateProperties['COLUMNS_LIST_MOBILE']['VALUES'] = array();

		foreach ($visibleColumns as $column)
		{
			$templateProperties['COLUMNS_LIST_MOBILE']['VALUES'][$column] = $arComponentParameters['PARAMETERS']['COLUMNS_LIST_EXT']['VALUES'][$column];
		}

		if (!isset($arCurrentValues["COLUMNS_LIST_MOBILE"]))
		{
			$templateProperties['COLUMNS_LIST_MOBILE']['DEFAULT'] = array_keys($templateProperties['COLUMNS_LIST_MOBILE']['VALUES']);
		}

		$arComponentParameters['PARAMETERS']['COLUMNS_LIST_MOBILE'] = $templateProperties['COLUMNS_LIST_MOBILE'];
	}
	else
	{
		unset($arComponentParameters['PARAMETERS']['COLUMNS_LIST_MOBILE']);
	}

	unset($templateProperties['COLUMNS_LIST_MOBILE']);
}
else
{
	unset($arComponentParameters['PARAMETERS']['COLUMNS_LIST_MOBILE']);
}

if (isset($templateProperties['LABEL_PROP']))
{
	$templateProperties['LABEL_PROP']['VALUES'] = $listProperties;

	if (!empty($arCurrentValues['LABEL_PROP']))
	{
		$selected = array();

		foreach ($arCurrentValues['LABEL_PROP'] as $name)
		{
			if (isset($listProperties[$name]))
			{
				$selected[$name] = $listProperties[$name];
			}
		}

		$templateProperties['LABEL_PROP_MOBILE']['VALUES'] = $selected;
	}
	else
	{
		if (!empty($templateProperties['LABEL_PROP_MOBILE']))
		{
			$templateProperties['LABEL_PROP_MOBILE']['HIDDEN'] = 'Y';
		}

		if (!empty($templateProperties['LABEL_PROP_POSITION']))
		{
			$templateProperties['LABEL_PROP_POSITION']['HIDDEN'] = 'Y';
		}
	}
}

if(!Loader::includeModule('catalog'))
{
	unset($arComponentParameters["PARAMETERS"]["USE_GIFTS"]);
	unset($arComponentParameters["GROUPS"]["GIFTS"]);
}
elseif($arCurrentValues["USE_GIFTS"] === null && $arComponentParameters['PARAMETERS']['USE_GIFTS']['DEFAULT'] == 'Y' || $arCurrentValues["USE_GIFTS"] == "Y")
{
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			"GIFTS_PLACE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_PLACE_GIFT"),
				"TYPE" => "LIST",
				"DEFAULT" => "BOTTOM",
				"VALUES" => array(
					"TOP" => GetMessage('SGB_PARAMS_PLACE_GIFT_TOP'),
					"BOTTOM" => GetMessage('SGB_PARAMS_PLACE_GIFT_BOTTOM'),
				),
			),
			"GIFTS_BLOCK_TITLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_BLOCK_TITLE"),
				"TYPE" => "STRING",
				"DEFAULT" => GetMessage("SGB_PARAMS_BLOCK_TITLE_DEFAULT"),
			),
			"GIFTS_HIDE_BLOCK_TITLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_HIDE_BLOCK_TITLE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "",
			),
			"GIFTS_TEXT_LABEL_GIFT" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PARAMS_TEXT_LABEL_GIFT"),
				"TYPE" => "STRING",
				"DEFAULT" => GetMessage("SGB_PARAMS_TEXT_LABEL_GIFT_DEFAULT"),
			),
			"GIFTS_PRODUCT_QUANTITY_VARIABLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_PRODUCT_QUANTITY_VARIABLE"),
				"TYPE" => "STRING",
				"DEFAULT" => "quantity",
				"HIDDEN" => (isset($arCurrentValues['USE_PRODUCT_QUANTITY']) && $arCurrentValues['USE_PRODUCT_QUANTITY'] == 'Y' ? 'N' : 'Y')
			),
			"GIFTS_PRODUCT_PROPS_VARIABLE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_PRODUCT_PROPS_VARIABLE"),
				"TYPE" => "STRING",
				"DEFAULT" => "prop",
				"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
			),
			"GIFTS_SHOW_OLD_PRICE" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("CVP_SHOW_OLD_PRICE"),
				"TYPE" => "CHECKBOX",
				"VALUES" => "Y",
			),
			'GIFTS_SHOW_DISCOUNT_PERCENT' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_SHOW_DISCOUNT_PERCENT'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'Y'
			),
			'GIFTS_MESS_BTN_BUY' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_MESS_BTN_BUY_GIFT'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CVP_MESS_BTN_BUY_GIFT_DEFAULT')
			),
			'GIFTS_MESS_BTN_DETAIL' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_MESS_BTN_DETAIL'),
				'TYPE' => 'STRING',
				'DEFAULT' => GetMessage('CVP_MESS_BTN_DETAIL_DEFAULT')
			),
			"GIFTS_PAGE_ELEMENT_COUNT" => array(
				"PARENT" => "GIFTS",
				"NAME" => GetMessage("SGB_PAGE_ELEMENT_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => "4",
			),
			'GIFTS_CONVERT_CURRENCY' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_CONVERT_CURRENCY'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N',
				'REFRESH' => 'Y',
			),
			'GIFTS_HIDE_NOT_AVAILABLE' => array(
				'PARENT' => 'GIFTS',
				'NAME' => GetMessage('CVP_HIDE_NOT_AVAILABLE'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'N',
			),
		)
	);
}