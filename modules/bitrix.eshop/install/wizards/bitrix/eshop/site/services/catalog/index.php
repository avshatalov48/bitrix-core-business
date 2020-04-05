<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$catalogSubscribe = $wizard->GetVar("catalogSubscribe");
$curSiteSubscribe = ($catalogSubscribe == "Y") ? array("use" => "Y", "del_after" => "100") : array("del_after" => "100");
$subscribe = COption::GetOptionString("sale", "subscribe_prod", "");
$arSubscribe = unserialize($subscribe);
$arSubscribe[WIZARD_SITE_ID] = $curSiteSubscribe;
COption::SetOptionString("sale", "subscribe_prod", serialize($arSubscribe));

$useStoreControl = $wizard->GetVar("useStoreControl");
$useStoreControl = ($useStoreControl == "Y") ? "Y" : "N";
$curUseStoreControl = COption::GetOptionString("catalog", "default_use_store_control", "N");
COption::SetOptionString("catalog", "default_use_store_control", $useStoreControl);

$productReserveCondition = $wizard->GetVar("productReserveCondition");
$productReserveCondition = (in_array($productReserveCondition, array("O", "P", "D", "S"))) ? $productReserveCondition : "P";
COption::SetOptionString("sale", "product_reserve_condition", $productReserveCondition);

if (CModule::IncludeModule("catalog"))
{
	if($useStoreControl == "Y" && $curUseStoreControl == "N")
	{
		$dbStores = CCatalogStore::GetList(array(), array("ACTIVE" => 'Y'));
		if(!$dbStores->Fetch())
		{
			$storeImageId = 0;
			$storeImage = CFile::MakeFileArray(WIZARD_SERVICE_RELATIVE_PATH.'/images/storepoint.jpg');
			if (!empty($storeImage) && is_array($storeImage))
			{
				$storeImage['MODULE_ID'] = 'catalog';
				$storeImageId =  CFile::SaveFile($storeImage, 'catalog');
			}

			$arStoreFields = array(
				"TITLE" => GetMessage("CAT_STORE_NAME"),
				"ADDRESS" => GetMessage("STORE_ADR_1"),
				"DESCRIPTION" => GetMessage("STORE_DESCR_1"),
				"GPS_N" => GetMessage("STORE_GPS_N_1"),
				"GPS_S" => GetMessage("STORE_GPS_S_1"),
				"PHONE" => GetMessage("STORE_PHONE_1"),
				"SCHEDULE" => GetMessage("STORE_PHONE_SCHEDULE"),
				"IMAGE_ID" => $storeImageId
			);
			$newStoreId = CCatalogStore::Add($arStoreFields);
			if($newStoreId)
			{
				$_SESSION['NEW_STORE_ID'] = $newStoreId;
			}
		}
	}
}

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

COption::SetOptionString("catalog", "allow_negative_amount", "N");
COption::SetOptionString("catalog", "default_can_buy_zero", "N");
COption::SetOptionString("catalog", "default_quantity_trace", "Y");