<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (isset($_REQUEST["barcode"]))
	$barcode =  $_REQUEST["barcode"];

if (CModule::IncludeModule("catalog"))
{
	$arFilter = array(
		"BARCODE" => $barcode,
	);

	$cache = new CPHPCache;
	$cache_path = '/eshopapp_cache/'.$action;
	$cache_time = 3600*24*365;
	$cache_id = 'barcode_'.$barcode;

	if($cache->InitCache($cache_time, $cache_id, $cache_path))
	{
		$arProduct = $cache->GetVars();
	}
	else
	{
		$arProduct = array();
		$dbProduct = CCatalogStoreBarCode::GetList(array(), array("BARCODE" => $barcode), false, false, array("PRODUCT_ID"));

		if ($arProduct = $dbProduct->GetNext())
		{
			$cache->StartDataCache($cache_time, $cache_id, $cache_path);
			$cache->EndDataCache($arProduct);
		}
	}

	if ($arProduct)
	{
		$data["product_id"] = $arProduct["PRODUCT_ID"];
	}
	else
	{
		$data["error"] = "empty";
	}
}