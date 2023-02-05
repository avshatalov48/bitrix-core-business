<?php
/** @global CWizardBase $wizard */

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$allowModifyStoreControl = $wizard->GetDefaultVar('allowModifyStoreControl');
if ($allowModifyStoreControl !== 'Y')
{
	return;
}

$catalogSubscribe = $wizard->GetVar("catalogSubscribe");
$curSiteSubscribe = ($catalogSubscribe == "Y") ? array("use" => "Y", "del_after" => "100") : array("del_after" => "100");
$subscribe = Option::get("sale", "subscribe_prod", "");
$arSubscribe = unserialize($subscribe, ["allowed_classes" => false]);
$arSubscribe[WIZARD_SITE_ID] = $curSiteSubscribe;
Option::set("sale", "subscribe_prod", serialize($arSubscribe));

$useStoreControl = $wizard->GetVar("useStoreControl");
$useStoreControl = ($useStoreControl == "Y") ? "Y" : "N";
$curUseStoreControl = Option::get("catalog", "default_use_store_control", "N");
Option::set("catalog", "default_use_store_control", $useStoreControl);
if ($useStoreControl === 'Y')
{
	Option::set('catalog', 'enable_reservation', 'Y');
}

$productReserveCondition = $wizard->GetVar("productReserveCondition");
$productReserveCondition = (in_array($productReserveCondition, array("O", "P", "D", "S"))) ? $productReserveCondition : "P";
Option::set("sale", "product_reserve_condition", $productReserveCondition);

if (Loader::includeModule('catalog'))
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

			$arStoreFields = [
				"TITLE" => GetMessage("CAT_STORE_NAME"),
				"ADDRESS" => GetMessage("STORE_ADR_1"),
				"DESCRIPTION" => GetMessage("STORE_DESCR_1"),
				"GPS_N" => GetMessage("STORE_GPS_N_1"),
				"GPS_S" => GetMessage("STORE_GPS_S_1"),
				"PHONE" => GetMessage("STORE_PHONE_1"),
				"SCHEDULE" => GetMessage("STORE_PHONE_SCHEDULE"),
				"IMAGE_ID" => $storeImageId,
				"IS_DEFAULT" => "Y",
			];
			$newStoreId = CCatalogStore::Add($arStoreFields);
			if($newStoreId)
			{
				$_SESSION['NEW_STORE_ID'] = $newStoreId;
			}
		}
	}
}

if (
	Option::get('eshop', 'wizard_installed', 'N', WIZARD_SITE_ID) === 'Y'
	&& !WIZARD_INSTALL_DEMO_DATA
)
{
	return;
}

if (Loader::includeModule('crm'))
{
	Option::set('catalog', 'allow_negative_amount', 'Y');
	Option::set('catalog', 'default_can_buy_zero', 'Y');
	Option::set('catalog', 'default_quantity_trace', $useStoreControl);
}
else
{
	Option::set('catalog', 'allow_negative_amount', 'N');
	Option::set('catalog', 'default_can_buy_zero', 'N');
	Option::set('catalog', 'default_quantity_trace', 'Y');
}
