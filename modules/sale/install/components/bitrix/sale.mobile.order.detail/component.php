<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage('SMOD_SALE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage('SMOD_MOBILEAPP_NOT_INSTALLED'));
	return;
}

$bUseAccountNumber = (COption::GetOptionString("sale", "account_number_template", "") !== "") ? true : false;

if (isset($_REQUEST['id']))
{
	$orderId = $_REQUEST['id'];

	if ($bUseAccountNumber) // supporting ACCOUNT_NUMBER in the request
	{
		$dbOrder = CSaleOrder::GetList(
			array("DATE_UPDATE" => "DESC"),
			array(
				"ACCOUNT_NUMBER" => urldecode(urldecode($_REQUEST['id']))
			)
		);
		if ($arOrder = $dbOrder->GetNext())
			$orderId = $arOrder["ID"];
	}
}
else
	$orderId = false;

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($orderId, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());


if(!$bUserCanViewOrder)
{
	echo ShowError(GetMessage("SMOD_NO_PERMS2VIEW"));
	return;
}

$arResult['ORDER'] = CSaleMobileOrderUtils::getOrderInfoDetail($orderId);

if(!$orderId || !$arResult['ORDER'])
{
	if(isset($arParams['ORDERS_LIST_PATH']))
		LocalRedirect($arParams['ORDERS_LIST_PATH']);
	else
		return;
}

if (isset($_REQUEST['action']))
	$arResult['ACTION'] = $_REQUEST['action'];
else
	$arResult['ACTION'] = '';

//prepare data for payment dialog's checkboxes set
if($arResult["ACTION"] == 'get_payment_dialog')
{
	if($arResult['ORDER']['PAYED'] == 'Y')
	{
		$arPayParams["TITLE"] = GetMessage('SMOD_PAY_CANCEL');
		$arPayParams["ITEMS"] = array(
			'pay_from_account_back' => GetMessage('SMOD_PAY_BACK'),
			'pay_cancel' => GetMessage('SMOD_PAY_CANCEL')
		);
	}
	else
	{
		$arPayParams["ITEMS"] = array('pay_from_account' => GetMessage('SMOD_PAY_CONFIRM'));
		$arPayParams["TITLE"] = GetMessage('SMOD_ACCOUNT');
	}

	$arResult["DAILOG"]["PAY_CB"] = $arPayParams;
}

CJSCore::Init('ajax');

$arResult['CURRENT_PAGE_PARAM'] = $APPLICATION->GetCurPageParam();
$arResult['CURRENT_PAGE'] = $APPLICATION->GetCurPage();
$arResult['AJAX_URL'] = $componentPath."/ajax.php";

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

$arStatusList = False;
$arStatFilter = array("LID" => LANG);
$arGroupByTmp = false;
if ($saleModulePermissions < "W")
{
	$arStatFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
	$arStatFilter["PERM_STATUS_FROM"] = "Y";
	$arStatFilter["ID"] = $arResult['ORDER']['STATUS_ID'];
	$arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS_FROM");
}
$dbStatusList = CSaleStatus::GetList(
		array(),
		$arStatFilter,
		$arGroupByTmp,
		false,
		array("ID", "NAME", "PERM_STATUS_FROM")
	);

$arStatusList = $dbStatusList->GetNext();

if ($arStatusList)
{
	$arFilter = array("LID" => LANG);
	$arGroupByTmp = false;
	if ($saleModulePermissions < "W")
	{
		$arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
		$arFilter["PERM_STATUS"] = "Y";
	}
	$dbStatusListTmp = CSaleStatus::GetList(
			array("SORT" => "ASC"),
			$arFilter,
			$arGroupByTmp,
			false,
			array("ID", "NAME", "SORT")
		);

	while($arStatusListTmp = $dbStatusListTmp->GetNext())
		$arResult['STATUSES'][] = $arStatusListTmp;

	if(!isset($arParams['MENU_ITEMS']['STATUS_CHANGE']) || $arParams['MENU_ITEMS']['STATUS_CHANGE'] != false)
		$arResult['MENU_ITEMS'] = array("STATUS_CHANGE");

	$arStatusesData = array();
	foreach ($arResult['STATUSES'] as $key => $status)
		$arStatusesData[$status["ID"]] = $status["~NAME"];

	$arResult["DAILOG"]["STATUSES"] = array(
		"ITEMS" => $arStatusesData,
		"TITLE" => GetMessage('SMOD_STATUS'),
		"RADIO_NAME" => "radio_statuses",
		"SELECTED" => $arResult['ORDER']['STATUS_ID']
	);
}

$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($orderId, "PERM_PAYMENT", $GLOBALS["USER"]->GetUserGroupArray());
if($bUserCanPayOrder && (!isset($arParams['MENU_ITEMS']['PAYMENT']) || $arParams['MENU_ITEMS']['PAYMENT'] != false))
	$arResult['MENU_ITEMS'][] = "PAYMENT";

$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($orderId, "PERM_DELIVERY", $GLOBALS["USER"]->GetUserGroupArray());
if($bUserCanDeliverOrder && (!isset($arParams['MENU_ITEMS']['DELIVERY']) || $arParams['MENU_ITEMS']['DELIVERY'] != false))
	$arResult['MENU_ITEMS'][] = "DELIVERY";

$bUserCanDeductOrder = CSaleOrder::CanUserChangeOrderFlag($orderId, "PERM_DEDUCTION", $GLOBALS["USER"]->GetUserGroupArray());
if($bUserCanDeductOrder && (!isset($arParams['MENU_ITEMS']['DEDUCTION']) || $arParams['MENU_ITEMS']['DEDUCTION'] != false))
	$arResult['MENU_ITEMS'][] = "DEDUCTION";

$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($orderId, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
if($bUserCanCancelOrder && (!isset($arParams['MENU_ITEMS']['ORDER_CANCEL']) || $arParams['MENU_ITEMS']['ORDER_CANCEL'] != false))
	$arResult['MENU_ITEMS'][] = "ORDER_CANCEL";

if(isset($arParams["SHOW_UPPER_BUTTONS"]) && $arParams["SHOW_UPPER_BUTTONS"] != 'Y')
	$arResult["SHOW_UPPER_BUTTONS"] = false;
else
	$arResult["SHOW_UPPER_BUTTONS"] = true;

$this->IncludeComponentTemplate();
?>