<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError('module "mobileapp" not installed');
	return;
}

if (isset($arParams['FILTER_ID']))
	$arResult["FILTER_ID"] = $arParams['FILTER_ID'];
elseif (isset($_REQUEST['filter_id']))
	$arResult["FILTER_ID"] = $_REQUEST['filter_id'];
else
{
	ShowError("Undefined FILTER_ID.");
	return;
}

if (isset($_REQUEST['show_fields_list']))
	$arResult["SHOW_FIELDS_LIST"] = $_REQUEST['show_fields_list'] == 'Y' ? true : false;
else
	$arResult["SHOW_FIELDS_LIST"] = false;

if($arResult["SHOW_FIELDS_LIST"])
{
	foreach ($arParams["FIELDS"] as $fieldId => $arField)
		$arResult["FIELDS_LIST"][$fieldId] = $arField["NAME"];
}

$arVisibleFields = CAdminMobileFilter::getFields($arResult["FILTER_ID"]);

if(!empty($arVisibleFields))
	$arResult["VISIBLE_FIELDS"] = array_keys($arVisibleFields);
elseif(isset($arParams["VISIBLE_FIELDS"]) && is_array($arParams["VISIBLE_FIELDS"]))
	$arResult["VISIBLE_FIELDS"] = $arParams["VISIBLE_FIELDS"];
else
	$arResult["VISIBLE_FIELDS"] = array_keys($arParams['FIELDS']);

$arResult['AJAX_URL'] = $componentPath."/ajax.php";

CJSCore::Init('ajax');

$this->IncludeComponentTemplate();
?>