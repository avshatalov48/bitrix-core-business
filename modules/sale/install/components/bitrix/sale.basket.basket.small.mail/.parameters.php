<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


// functions for custom columns view
if (!function_exists("getIblockNames"))
{
	function getIblockNames($arIblockIDs, $arIblockNames)
	{
		$str = "";
		foreach ($arIblockIDs as $iblockID)
		{
			$str .= "\"".$arIblockNames[$iblockID]."\", ";
		}
		$str .= "#";

		return str_replace(", #", "", $str);
	}
}

$arColumns = array(
	"NAME" => GetMessage("SBB_BNAME"),
	"DISCOUNT_FORMATED" => GetMessage("SBB_BDISCOUNT"),
	"WEIGHT_FORMATED" => GetMessage("SBB_BWEIGHT"),
	#"PROPS" => GetMessage("SBB_BPROPS"),
	"TYPE" => GetMessage("SBB_BTYPE"),
	"PRICE_FORMATED" => GetMessage("SBB_BPRICE"),
	"QUANTITY_FORMATED" => GetMessage("SBB_BQUANTITY"),
	"SUM" => GetMessage("SBB_BSUM"),
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
			$arColumns["PROPERTY_".$arProperty["CODE"]] = $name." (".getIblockNames($arTmpProperty2Iblock["PROPERTY_".$arProperty["CODE"]], $arIblockNames).")";
		else
			$arColumns["PROPERTY_".$arProperty["CODE"]] = $name;
	}
}
// end of custom columns view functions



$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"COLUMNS_LIST" => Array(
			"NAME"=>GetMessage("SBB_COLUMNS_LIST"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"Y",
			"VALUES"=>$arColumns,
			"DEFAULT"=>array("NAME", "SUM", "QUANTITY"),
			"COLS"=>25,
			"SIZE"=>7,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "VISUAL",
		),
		"USER_ID" => Array(
			"NAME" => GetMessage("SBBS_USER_ID"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				"{#USER_ID#}" => "={#USER_ID#}",
				"{#ORDER_USER_ID#}" => "={#ORDER_USER_ID#}",
				"{#ID#}" => "={#ID#}",
			),
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => Array(
				"{#USER_ID#}" => "{#USER_ID#}"
			),
			#"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_BASKET" => Array(
			"NAME" => GetMessage("SBBS_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/basket.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_ORDER" => Array(
			"NAME" => GetMessage("SBBS_PATH_TO_ORDER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SHOW_DELAY" => array(
			"NAME" => GetMessage('SBBS_SHOW_DELAY'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"MULTIPLE" => "N",
		),
		"SHOW_NOTAVAIL" => array(
			"NAME" => GetMessage('SBBS_SHOW_NOTAVAIL'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"MULTIPLE" => "N",
		),
		"SHOW_SUBSCRIBE" => array(
			"NAME" => GetMessage('SBBS_SHOW_SUBSCRIBE'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"MULTIPLE" => "N",
		),
	)
);
?>