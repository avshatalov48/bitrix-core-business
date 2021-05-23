<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage("SMPL_SALE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("SMPL_MOBILEAPP_NOT_INSTALLED");
	return;
}

if (!CModule::IncludeModule('iblock'))
{
	ShowError("SMPL_IBLOCK_NOT_INSTALLED");
	return;
}

$arResult = array(
	"CURRENT_PAGE_PARAMS" => $APPLICATION->GetCurPageParam(),
	"AJAX_URL" => $componentPath."/ajax.php"
);

$arResult["STORE_PAGE"] = CHTTP::urlAddParams(
	$arResult["CURRENT_PAGE_PARAMS"],
	array(
		"set_store" => 'Y'
	),
	array(
		"encode" => true,
	)
);

$arResult["BARCODE_PAGE"] = CHTTP::urlAddParams(
	$arResult["CURRENT_PAGE_PARAMS"],
	array(
		"set_barcode" => 'Y'
	),
	array(
		"encode" => true,
	)
);

if(isset($_REQUEST["product_id"]))
	$arResult["PRODUCT_ID"];

if(isset($_REQUEST["set_store"]))
	$templatePage = 'store';
elseif(isset($_REQUEST["set_barcode"]))
	$templatePage = 'barcode';
else
	$templatePage = 'template';

$bXmlId = COption::GetOptionString("sale", "show_order_product_xml_id", "N");

$rsSites = CSite::GetList($by="id", $order="asc", array("ACTIVE" => "Y", "DEF" => "Y"));
$arSite = $rsSites->Fetch();
$LID = $arSite["ID"];

$arResult["LID"] = $LID;

$dbBasket = CSaleBasket::GetList(
	array("NAME" => "ASC"),
	array("ORDER_ID" => $arParams["ORDER_ID"]),
	false,
	false,
	array("ID", "PRODUCT_ID", "QUANTITY", "NAME", "MODULE", "PRODUCT_PROVIDER_CLASS", "BARCODE_MULTI")
);

$weight = 0;
$price =0;
$price_total = 0;
$arProdIds = array(); //http://jabber.bx/view.php?id=37744
$arProdIdsPrIds = array();
$useStores = false;

while ($arBasket = $dbBasket->Fetch())
{
	$arProdIds[] = $arBasket["PRODUCT_ID"];
	$arProdIdsPrIds[$arBasket["PRODUCT_ID"]] = $arBasket["ID"];
	$arBasket["BALANCE"] = "0";
	$arBasket["STORES"] = array();
	$arBasket["HAS_SAVED_QUANTITY"] = "N";
	$arBasket["HAS_SAVED_BARCODES"] = false;

	/** @var $productProvider IBXSaleProductProvider */
	if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
	{
		$storeCount = $productProvider::GetStoresCount(array("SITE_ID" => $LID));

		if ($storeCount > 0)
		{
			if ($arProductStore = $productProvider::GetProductStores(
					array(
						"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
						"SITE_ID" => $LID,
						'BASKET_ID' => $arBasket['ID']
					)
				))
			{

				foreach ($arProductStore as $arStore)
					$arBasket["STORES"][$arStore["STORE_ID"]] = $arStore;

				if (!$useStores && $storeCount != -1) // -1 means store control is not used
				{
					$useStores = true;
				}

				// if barcodes/store quantity are already saved for this product,
				// then check if barcodes are still valid and save them to the store array
				$ind = 0;
				$dbres = CSaleStoreBarcode::GetList(
					array(),
					array("BASKET_ID" => $arBasket["ID"]),
					false,
					false,
					array("ID", "BASKET_ID", "BARCODE", "STORE_ID", "ORDER_ID", "QUANTITY", "DEDUCTED")
				);
				while ($arRes = $dbres->GetNext())
				{
					$arCheckBarcodeFields = array(
						"BARCODE"    => $arRes["BARCODE"],
						"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
						"ORDER_ID"   => $arParams["ORDER_ID"]
					);

					if ($arBasket["BARCODE_MULTI"] == "Y")
						$arCheckBarcodeFields["STORE_ID"] = $arRes["STORE_ID"];

					if ($arRes["BARCODE"] == "")
						$res = true;
					else if ($arRes["DEDUCTED"] == "Y")
						$res = false;
					else
						$res = $productProvider::CheckProductBarcode($arCheckBarcodeFields);

					if(is_array($arBasket["STORES"]))
					{
						foreach ($arBasket["STORES"] as $storeId => $arStoreInfo)
						{
							if ($arStoreInfo["STORE_ID"] == $arRes["STORE_ID"])
							{
								if ($arBasket["BARCODE_MULTI"] == "Y")
								{
									$arBasket["STORES"][$storeId]["QUANTITY"] += $arRes["QUANTITY"];

									if ($arRes["DEDUCTED"] == "Y")
										$val = "D";
									else
										$val = ($res) ? "Y" : "N";

									$arBasket["STORES"][$storeId]["BARCODE"][] = $arRes["BARCODE"];
									$arBasket["STORES"][$storeId]["BARCODE_FOUND"][] = $val;
								}
								else
								{
									$arBasket["STORES"][$storeId]["QUANTITY"] = $arRes["QUANTITY"];

									$arBasket["STORES"][$storeId]["QUANTITY_DEDUCTED"] = ($arRes["DEDUCTED"] == "Y") ? "Y" : "N";
								}
							}
						}
					}

					$arBasket["HAS_SAVED_QUANTITY"] = "Y";
					$arBasket["HAS_SAVED_BARCODES"] = true;

					$ind++;
				}
			}
		}
	}

	$arResult["BASKET"][$arBasket["ID"]] = $arBasket;
}

if(CModule::IncludeModule('catalog'))
{
	$rsCatProd = CCatalogProduct::GetList(
									array(),
									array("ID" => $arProdIds),
									false,
									false,
									array("ID", "QUANTITY")
	);

	while($arCatProd = $rsCatProd->Fetch())
		if ($arResult["BASKET"][$arProdIdsPrIds[$arCatProd["ID"]]]["MODULE"] == "catalog")
			$arResult["BASKET"][$arProdIdsPrIds[$arCatProd["ID"]]]["BALANCE"] = FloatVal($arCatProd["QUANTITY"]);
}

$arResult["USE_STORES"] = $useStores;

$this->IncludeComponentTemplate($templatePage);
?>
