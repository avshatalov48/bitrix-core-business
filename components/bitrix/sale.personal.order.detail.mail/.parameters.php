<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SPOD_DESC_YES"),
	"N" => GetMessage("SPOD_DESC_NO"),
);

$arColumns = array(
	"PICTURE" => GetMessage("SPOD_BPICTURE"),
	"NAME" => GetMessage("SPOD_BNAME"),
	"DISCOUNT_PRICE_PERCENT_FORMATED" => GetMessage("SPOD_BDISCOUNT"),
	"WEIGHT_FORMATED" => GetMessage("SPOD_BWEIGHT"),
	"PROPS" => GetMessage("SPOD_BPROPS"),
	"TYPE" => GetMessage("SPOD_BTYPE"),
	"PRICE_FORMATED" => GetMessage("SPOD_BPRICE"),
	"QUANTITY" => GetMessage("SPOD_BQUANTITY"),
);

if (CModule::IncludeModule("catalog"))
{
	// get iblock props from all catalog iblocks including sku iblocks
	$arIblockIDs = array();
	$arIblockNames = array();
	$catalogFilter = array();

	if (array_key_exists('src_site', $_REQUEST))
	{
		$siteID = $_REQUEST['src_site'];
		if($siteID !== '' && preg_match('/^[a-z0-9_]{2}$/i', $siteID) === 1)
		{
			$catalogFilter = array('LID' => $siteID);
		}

	}

	$dbCatalog = CCatalog::GetList(array(), $catalogFilter);
	while ($arCatalog = $dbCatalog->GetNext())
	{
		$arIblockIDs[] = $arCatalog["IBLOCK_ID"];
		$arIblockNames[$arCatalog["IBLOCK_ID"]] = $arCatalog["NAME"];
	}

	// iblock props
	$arProps = array();
	$arPropNameCodeCount = array();
	foreach ($arIblockIDs as $iblockID)
	{
		$dbProps = CIBlockProperty::GetList(
			array(
				"SORT"=>"ASC",
				"NAME"=>"ASC"
			),
			array(
				"IBLOCK_ID" => $iblockID,
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "N",
			)
		);

		while ($arProp = $dbProps->GetNext())
		{
			$arProps[] = $arProp;
			if (isset($arProp["NAME"]))
				$arPropNameCodeCount[$arProp["NAME"]][$arProp["CODE"]]++;
		}
	}

	// create properties array where properties with the same codes are considered the same (TODO: use property IDs instead)
	$arTmpProperty2Iblock = array();
	foreach ($arProps as $id => $arProperty)
	{
		$arTmpProperty2Iblock["PROPERTY_".$arProperty["CODE"]][] = $arProperty["IBLOCK_ID"];

		if (
			isset($arProperty["NAME"])
			&& count($arPropNameCodeCount[$arProperty["NAME"]]) > 1
		)
			$name = $arProperty["NAME"]." [".$arProperty["CODE"]."] ";
		else
			$name = $arProperty["NAME"];

		if (array_key_exists("PROPERTY_".$arProperty["CODE"], $arColumns))
		{
			$iblockNames = array();
			foreach ($arTmpProperty2Iblock["PROPERTY_".$arProperty["CODE"]] as $iblockID)
			{
				if(count($iblockNames) == 2)
				{
					$iblockNames[] = "... ";
					break;
				}

				$iblockNames[] = '"' . $arIblockNames[$iblockID] . '"';
			}
			$iblockNames = implode(", ", $iblockNames);
			$arColumns["PROPERTY_".$arProperty["CODE"]] = $name." (".$iblockNames.")";
		}
		else
		{
			$arColumns["PROPERTY_".$arProperty["CODE"]] = $name;
		}
	}
}
// end of custom columns view functions

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"ID" => Array(
			"NAME" => GetMessage("SPOD_ID"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				"{#ORDER_ID#}" => "{#ORDER_ID#}",
				"{#ORDER_REAL_ID#}" => "{#ORDER_REAL_ID#}",
			),
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => Array(
				"{#ORDER_ID#}" => "{#ORDER_ID#}"
			),
			"COLS" => 25,
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_BASKET" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_BASKET"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_BASE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_BASE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_USER" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_USER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_PARAMS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_PARAMS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_BUYER" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_BUYER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_DELIVERY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_DELIVERY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_PAYMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_PAYMENT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_ORDER_SUM" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SPOD_SHOW_ORDER_SUM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"CUSTOM_SELECT_PROPS" => Array(
			"NAME"=>GetMessage("SPOD_COLUMNS_LIST"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"Y",
			"VALUES"=>$arColumns,
			"DEFAULT"=>array("NAME", "SUM", "QUANTITY"),
			"COLS"=>25,
			"SIZE"=>7,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "BASE",
		),
		"PATH_TO_LIST" => Array(
			"NAME" => GetMessage("SPOD_PATH_TO_LIST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_CANCEL" => Array(
			"NAME" => GetMessage("SPOD_PATH_TO_CANCEL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => Array(
			"NAME" => GetMessage("SPOD_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	)
);

if(CModule::IncludeModule("iblock"))
{
	$arComponentParameters["PARAMETERS"]["ACTIVE_DATE_FORMAT"] = CIBlockParameters::GetDateFormat(GetMessage("SPOD_ACTIVE_DATE_FORMAT"), "VISUAL");

	$arComponentParameters["PARAMETERS"]["PICTURE_WIDTH"] = array(
		"NAME" => GetMessage("SPOD_PARAM_PREVIEW_PICTURE_WIDTH"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "110",
		"PARENT" => "VISUAL",
	);
	$arComponentParameters["PARAMETERS"]["PICTURE_HEIGHT"] = array(
		"NAME" => GetMessage("SPOD_PARAM_PREVIEW_PICTURE_HEIGHT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "110",
		"PARENT" => "VISUAL",
	);
	$arComponentParameters["PARAMETERS"]["PICTURE_RESAMPLE_TYPE"] = array(
		"NAME" => GetMessage("SPOD_PARAM_RESAMPLE_TYPE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => array(
			BX_RESIZE_IMAGE_EXACT => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_EXACT"),
			BX_RESIZE_IMAGE_PROPORTIONAL => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_PROPORTIONAL"), 
			BX_RESIZE_IMAGE_PROPORTIONAL_ALT => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_PROPORTIONAL_ALT")
		),
		"DEFAULT" => BX_RESIZE_IMAGE_PROPORTIONAL,
		"PARENT" => "VISUAL",
	);
}

if(CModule::IncludeModule("sale"))
{
	$dbPerson = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"));
	while($arPerson = $dbPerson->GetNext())
	{

		$arPers2Prop = Array("" => GetMessage("SPOD_SHOW_ALL"));
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PERSON_TYPE_ID" => $arPerson["ID"]));
		while($arProp = $dbProp -> GetNext())
		{

			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}

		if($bProp)
		{
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  Array(
					"NAME" => GetMessage("SPOD_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")",
					"TYPE"=>"LIST", "MULTIPLE"=>"Y",
					"VALUES" => $arPers2Prop,
					"DEFAULT"=>"",
					"COLS"=>25,
					"ADDITIONAL_VALUES"=>"N",
					"PARENT" => "BASE",
				);
		}
	}
}

?>