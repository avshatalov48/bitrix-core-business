<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage("SMOB_SALE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("SMOB_MOBILEAPP_NOT_INSTALLED");
	return;
}

$arResult['AJAX_URL'] = $componentPath."/ajax.php";
$arResult['FORM_ACTION'] = $APPLICATION->GetCurPageParam();
$arResult['STORE_IDS'] = array();

if (isset($arParams['PRODUCT_DATA']['STORES']) && is_array($arParams['PRODUCT_DATA']['STORES']))
{
	foreach ($arParams['PRODUCT_DATA']['STORES'] as $storeId => $tmp)
	{
		$arResult['STORE_IDS'][] = $storeId;
	}
}

$this->IncludeComponentTemplate();
?>