<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Catalog;
use Bitrix\Iblock;

$arYesNo = [
	"Y" => GetMessage("SPOD_DESC_YES"),
	"N" => GetMessage("SPOD_DESC_NO"),
];

$arColumns = [
	"PICTURE" => GetMessage("SPOD_BPICTURE"),
	"NAME" => GetMessage("SPOD_BNAME"),
	"DISCOUNT_PRICE_PERCENT_FORMATED" => GetMessage("SPOD_BDISCOUNT"),
	"WEIGHT_FORMATED" => GetMessage("SPOD_BWEIGHT"),
	"PROPS" => GetMessage("SPOD_BPROPS"),
	"TYPE" => GetMessage("SPOD_BTYPE"),
	"PRICE_FORMATED" => GetMessage("SPOD_BPRICE"),
	"QUANTITY" => GetMessage("SPOD_BQUANTITY"),
];

if (CModule::IncludeModule("catalog"))
{
	$siteId = (string)($_REQUEST['src_site'] ?? '');
	$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);

	$parameters = [
		'select' => [
			'IBLOCK_ID',
			'NAME' => 'IBLOCK.NAME',
		],
		'order' => [
			'IBLOCK_ID' => 'ASC',
		],
	];

	if ($siteId !== '')
	{
		$parameters['select']['SITE_ID'] = 'IBLOCK_SITE.SITE_ID';
		$parameters['filter'] = [
			'=SITE_ID' => $siteId,
		];
		$parameters['runtime'] = [
			'IBLOCK_SITE' => [
				'data_type' => 'Bitrix\Iblock\IblockSiteTable',
				'reference' => [
					'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
				],
				'join_type' => 'inner',
			],
		];
	}

	// get iblock props from all catalog iblocks including sku iblocks
	$arIblockIDs = [];
	$arIblockNames = [];

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
		$propertyIterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID',
				'CODE',
				'NAME',
				'IBLOCK_ID',
			],
			'filter' => [
				'@IBLOCK_ID' => $arIblockIDs,
				'=ACTIVE' => 'Y',
				'!=XML_ID' => CIBlockPropertyTools::XML_SKU_LINK,
			],
			'order' => [
				'IBLOCK_ID' => 'ASC',
				'SORT' => 'ASC',
				'ID' => 'ASC',
			]
		]);
		while ($property = $propertyIterator->fetch())
		{
			$property['ID'] = (int)$property['ID'];
			$property['IBLOCK_ID'] = (int)$property['IBLOCK_ID'];
			$property['CODE'] = (string)$property['CODE'];
			if ($property['CODE'] == '')
				$property['CODE'] = $property['ID'];
			if (!isset($arProps[$property['CODE']]))
			{
				$arProps[$property['CODE']] = [
					'CODE' => $property['CODE'],
					'TITLE' => $property['NAME'].' ['.$property['CODE'].']',
					'ID' => [$property['ID']],
					'IBLOCK_ID' => [$property['IBLOCK_ID'] => $property['IBLOCK_ID']],
					'IBLOCK_TITLE' => [$property['IBLOCK_ID'] => $arIblockNames[$property['IBLOCK_ID']]],
					'COUNT' => 1
				];
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

		$propList = [];
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
		{
			$arColumns = array_merge($arColumns, $propList);
		}
		unset($propList);
	}
}
// end of custom columns view functions

$arComponentParameters = [
	"PARAMETERS" => [
		"ID" => [
			"NAME" => GetMessage("SPOD_ID"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => [
				"{#ORDER_ID#}" => "{#ORDER_ID#}",
				"{#ORDER_REAL_ID#}" => "{#ORDER_REAL_ID#}",
			],
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => [
				"{#ORDER_ID#}" => "{#ORDER_ID#}"
			],
			"COLS" => 25,
			"PARENT" => "BASE",
		],
		"SHOW_ORDER_BASKET" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_BASKET"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_BASE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_BASE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_USER" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_USER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_PARAMS" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_PARAMS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_BUYER" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_BUYER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_DELIVERY" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_DELIVERY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_PAYMENT" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_PAYMENT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"SHOW_ORDER_SUM" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_SUM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		"CUSTOM_SELECT_PROPS" => [
			"NAME"=>GetMessage("SPOD_COLUMNS_LIST"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"Y",
			"VALUES"=>$arColumns,
			"DEFAULT"=> ["NAME", "SUM", "QUANTITY"],
			"COLS"=>25,
			"SIZE"=>7,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "BASE",
		],
		"PATH_TO_LIST" => [
			"NAME" => GetMessage("SPOD_PATH_TO_LIST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],
		"PATH_TO_CANCEL" => [
			"NAME" => GetMessage("SPOD_PATH_TO_CANCEL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],
		"PATH_TO_PAYMENT" => [
			"NAME" => GetMessage("SPOD_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],

		"CACHE_TIME" => ["DEFAULT"=>3600],
	]
];

if(CModule::IncludeModule("iblock"))
{
	$arComponentParameters["PARAMETERS"]["ACTIVE_DATE_FORMAT"] = CIBlockParameters::GetDateFormat(GetMessage("SPOD_ACTIVE_DATE_FORMAT"), "VISUAL");

	$arComponentParameters["PARAMETERS"]["PICTURE_WIDTH"] = [
		"NAME" => GetMessage("SPOD_PARAM_PREVIEW_PICTURE_WIDTH"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "110",
		"PARENT" => "VISUAL",
	];
	$arComponentParameters["PARAMETERS"]["PICTURE_HEIGHT"] = [
		"NAME" => GetMessage("SPOD_PARAM_PREVIEW_PICTURE_HEIGHT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "110",
		"PARENT" => "VISUAL",
	];
	$arComponentParameters["PARAMETERS"]["PICTURE_RESAMPLE_TYPE"] = [
		"NAME" => GetMessage("SPOD_PARAM_RESAMPLE_TYPE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => [
			BX_RESIZE_IMAGE_EXACT => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_EXACT"),
			BX_RESIZE_IMAGE_PROPORTIONAL => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_PROPORTIONAL"),
			BX_RESIZE_IMAGE_PROPORTIONAL_ALT => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_PROPORTIONAL_ALT")
		],
		"DEFAULT" => BX_RESIZE_IMAGE_PROPORTIONAL,
		"PARENT" => "VISUAL",
	];
}

if (CModule::IncludeModule("sale"))
{
	$dbPerson = CSalePersonType::GetList([
		"SORT" => "ASC",
		"NAME" => "ASC",
	]);
	while($arPerson = $dbPerson->GetNext())
	{

		$arPers2Prop = ["" => GetMessage("SPOD_SHOW_ALL")];
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(
			[
				"SORT" => "ASC",
				"NAME" => "ASC",
			],
			[
				"PERSON_TYPE_ID" => $arPerson["ID"]
			]
		);
		while($arProp = $dbProp -> GetNext())
		{

			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}

		if($bProp)
		{
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  [
				"NAME" => GetMessage("SPOD_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")",
				"TYPE"=>"LIST",
				"MULTIPLE"=>"Y",
				"VALUES" => $arPers2Prop,
				"DEFAULT"=>"",
				"COLS"=>25,
				"ADDITIONAL_VALUES"=>"N",
				"PARENT" => "BASE",
			];
		}
	}
}
