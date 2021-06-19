<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

define("SALE_ORDERS_INIT_COUNT", 20); //how mutch orders we must load initially
define("SALE_ORDERS_LIST_PACK_SIZE", 20); //how much orders we must load, when user will reach bottom
define("SALE_ORDERS_LIST_PRELOAD_START", 2); //when start loading orders not loaded yet
define("SALE_ORDERS_LIST_CHECK_TIMEOUT", 120000); //how often we must check the updated orders (msec)

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage("SMOL_SALE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("SMOL_MOBILEAPP_NOT_INSTALLED");
	return;
}

if (isset($_REQUEST['action']))
	$arResult["ACTION"] = $_REQUEST['action'];
else
	$arResult["ACTION"] = "";

$arFilter = array();
$arResult["FILTER_ID"] = "maCustomFilter";

$filterFields = CSaleMobileOrderFilter::buildFieldsParams();
$arCustomFilter = CAdminMobileFilter::getNonemptyFields($arResult["FILTER_ID"], $filterFields);
$arCustomFilter = CSaleMobileOrderFilter::adaptFields($arCustomFilter);

if(isset($_REQUEST['filtered']) && $_REQUEST['filtered'] == "Y")
{
	if (isset($_REQUEST['filter_name']))
		$arResult["FILTER_NAME"] = $_REQUEST['filter_name'];
	else
		$arResult["FILTER_NAME"] = "";

	switch ($arResult["FILTER_NAME"])
	{
		case "waiting_for_pay":
			$arFilter["STATUS_ID"] = "N";
			break;

		case "waiting_for_delivery":
			$arFilter["STATUS_ID"] = "P";
			break;

		case "custom":
			$arFilter = $arCustomFilter;
			break;
	}
}

if(isset($arParams["FILTER"]))
	foreach ($arParams["FILTER"] as $key => $fltVal)
			$arFilter[$key] = $fltVal;

if(isset($arFilter["CUSTOM_SUBQUERY"]))
	unset($arFilter["CUSTOM_SUBQUERY"]);

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

//set rights to orders
if(!isset($arFilter["USER_ID"]))
{
	if ($saleModulePermissions == "D")
		$arFilter["USER_ID"] = intval($USER->GetID());
	elseif ($saleModulePermissions != "W")
	{
		$arFilter["STATUS_PERMS_GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
		$arFilter[">=STATUS_PERMS_PERM_VIEW"] = "Y";

		$arUserGroups = $USER->GetUserGroupArray();
		$arAccessibleSites = array();
		$dbAccessibleSites = CSaleGroupAccessToSite::GetList(
				array(),
				array("GROUP_ID" => $arUserGroups),
				false,
				false,
				array("SITE_ID")
		);

		while ($arAccessibleSite = $dbAccessibleSites->Fetch())
		{
			if(!in_array($arAccessibleSite["SITE_ID"], $arAccessibleSites))
				$arAccessibleSites[] = $arAccessibleSite["SITE_ID"];
		}

		if(count($arAccessibleSites) > 0)
			$arFilter["LID"] = $arAccessibleSites;
	}
}

if (isset($_REQUEST['dialog_name']))
	$arResult["DIALOG_NAME"] = $_REQUEST['dialog_name'];
else
	$arResult["DIALOG_NAME"] = "";

//return array (needed by ajax)
if(isset($arParams['RETURN_AS_ARRAY']) && $arParams['RETURN_AS_ARRAY'] == 'Y')
	$arResult['RETURN_AS_ARRAY'] = true;
else
	$arResult['RETURN_AS_ARRAY'] = false;

if(isset($arParams["ORDER_DETAIL_PATH"]))
	$arResult["ORDER_DETAIL_PATH"] = $arParams["ORDER_DETAIL_PATH"];
else
	$arResult["ORDER_DETAIL_PATH"] = "#";

$arResult["FIELDS"] = array(
	"USER_ID" => GetMessage("SMOL_FN_USER_ID"),
	"DELIVERY" => GetMessage("SMOL_FN_DELIVERY"),
	"PAYED" => GetMessage("SMOL_FN_PAYED"),
	"PAY_VOUCHER_NUM" => GetMessage("SMOL_FN_PAY_VOUCHER_NUM"),
	"PAY_VOUCHER_DATE" => GetMessage("SMOL_FN_PAY_VOUCHER_DATE"),
	"DELIVERY_DOC_NUM" => GetMessage("SMOL_FN_DELIVERY_DOC_NUM"),
	"DELIVERY_DOC_DATE" => GetMessage("SMOL_FN_DELIVERY_DOC_DATE"),
	"CANCELED" => GetMessage("SMOL_FN_CANCELED"),
	"DEDUCTED" => GetMessage("SMOL_FN_DEDUCTED"),
	"STATUS_NAME" => GetMessage("SMOL_FN_STATUS_ID"),
	"PRICE_DELIVERY" => GetMessage("SMOL_FN_PRICE_DELIVERY"),
	"ALLOW_DELIVERY" => GetMessage("SMOL_FN_ALLOW_DELIVERY"),
	"SUM_PAID" => GetMessage("SMOL_FN_SUM_PAID"),
	"DATE_UPDATE" => GetMessage("SMOL_FN_DATE_UPDATE"),
	"PS_STATUS" => GetMessage("SMOL_FN_PS_STATUS"),
	"PS_STATUS_DESCRIPTION" => GetMessage("SMOL_FN_PS_STATUS_DESCRIPTION"),
	"PS_SUM" => GetMessage("SMOL_FN_PS_SUM"),
	"TAX_VALUE" => GetMessage("SMOL_FN_TAX_VALUE"),
	"REASON_CANCELED" => GetMessage("SMOL_FN_REASON_CANCELED"),
	"REASON_UNDO_DEDUCTED" => GetMessage("SMOL_FN_REASON_UNDO_DEDUCTED"),
	);

if($saleModulePermissions > "D")
{
	$arResult["FIELDS"]["MARKED"] = GetMessage("SMOL_FN_MARKED");
	$arResult["FIELDS"]["COMMENTS"] = GetMessage("SMOL_FN_COMMENTS");
	$arResult["FIELDS"]["REASON_MARKED"] = GetMessage("SMOL_FN_REASON_MARKED");
	$arResult["FIELDS"]["USER_EMAIL"] = GetMessage("SMOL_FN_USER_EMAIL");
	$arResult["FIELDS"]["USER_DESCRIPTION"] = GetMessage("SMOL_FN_USER_DESCRIPTION");
	$arResult["FIELDS"]["PERSON_TYPE"] = GetMessage("SMOL_FN_PERSON_TYPE");
}

$arVisibleFields = CUserOptions::GetOption("sale", "maListVisibleFields", array(), $USER->GetID());

if(!empty($arVisibleFields))
	$arResult['VISIBLE_FIELDS'] = $arVisibleFields;
else
	$arResult['VISIBLE_FIELDS'] = array("USER_ID", "DELIVERY", "PAYED");

$arSort = array("ID" => "DESC");
$arResult["ORDERS"] = array();
$ordersCount = 0;
$ordersIds = array();
$deliveryIds = array();
$personTypesIds = array();
$paySysIds = array();
//$statIds = array();

$select = array(
	'*',
	'PS_STATUS', 'PS_STATUS_CODE', 'PS_STATUS_DESCRIPTION',
	'PS_STATUS_MESSAGE', 'PS_SUM', 'PS_CURRENCY', 'PS_RESPONSE_DATE',
	'PAY_VOUCHER_NUM', 'PAY_VOUCHER_DATE', 'DATE_PAY_BEFORE',
	'DATE_BILL', 'PAY_SYSTEM_NAME', 'PAY_SYSTEM_ID',
	'DATE_PAYED', 'EMP_PAYED_ID'
);

$dbOrderList = CSaleOrder::GetList($arSort, $arFilter, false, array("nTopCount" =>SALE_ORDERS_INIT_COUNT), $select);

while ($arOrderItem = $dbOrderList->GetNext())
{
	$ordersIds[] = $arOrderItem["ID"];
	$deliveryIds[$arOrderItem["ID"]] = $arOrderItem["DELIVERY_ID"];
	$personTypesIds[$arOrderItem["ID"]] = $arOrderItem["PERSON_TYPE_ID"];
	$paySysIds[$arOrderItem["ID"]] = $arOrderItem["PAY_SYSTEM_ID"];
//	$statIds[$arOrderItem["ID"]] = $arOrderItem["STATUS_ID"];

	if($arOrderItem["ALLOW_DELIVERY"] == 'Y' && $arOrderItem["PAYED"] == 'Y')
		$arOrderItem["ADD_ORDER_STEP"] = 'step3';
	elseif($arOrderItem["ALLOW_DELIVERY"] == 'Y' || $arOrderItem["PAYED"] == 'Y')
		$arOrderItem["ADD_ORDER_STEP"] = 'step2';
	else
		$arOrderItem["ADD_ORDER_STEP"] = 'step1';

	$arOrderItem["ORDER_DETAIL_LINK"] = $arResult["ORDER_DETAIL_PATH"]."?id=".(int)$arOrderItem["ID"];
	$arOrderItem["DATE_UPDATE"] = CSaleMobileOrderUtils::getDateTime($arOrderItem["DATE_UPDATE"]);
	$arOrderItem["PRICE_DELIVERY"] = SaleFormatCurrency($arOrderItem["PRICE_DELIVERY"], $arOrderItem["CURRENCY"]);
	$arOrderItem["SUM_PAID"] = SaleFormatCurrency($arOrderItem["SUM_PAID"], $arOrderItem["CURRENCY"]);
	$arOrderItem["PS_SUM"] = SaleFormatCurrency($arOrderItem["PS_SUM"], $arOrderItem["CURRENCY"]);
	$arOrderItem["TAX_VALUE"] = SaleFormatCurrency($arOrderItem["TAX_VALUE"], $arOrderItem["CURRENCY"]);
	$arOrderItem["DATE_INSERT"] = CSaleMobileOrderUtils::getDateTime($arOrderItem["DATE_INSERT"]);
	$arOrderItem["DATE_INSERT_SHORT"] = CSaleMobileOrderUtils::getDate($arOrderItem["DATE_INSERT"]);
	$arOrderItem["TMPL_DELIVERY_ALLOWED"] = $arOrderItem["ALLOW_DELIVERY"] == 'Y' ? 'allowed' : 'notallowed';
	$arOrderItem["ADD_ALLOW_DELIVERY_PHRASE"] = $arOrderItem["ALLOW_DELIVERY"] == 'Y' ? GetMessage("SMOL_ALLOWED") : GetMessage("SMOL_DISALLOWED");
	$arOrderItem["ADD_ALLOW_PAYED_PHRASE"] = $arOrderItem["PAYED"] == 'Y' ? GetMessage("SMOB_PAYED") : GetMessage("SMOB_NOT_PAYED");
	$arOrderItem["TMPL_PAYED"] = $arOrderItem["PAYED"] == 'Y' ? '' : 'notallowed';
	$arOrderItem["CANCELED"] = $arOrderItem["CANCELED"] == 'Y' ? GetMessage("SMOL_YES") : GetMessage("SMOL_NO");
	$arOrderItem["DEDUCTED"] = $arOrderItem["DEDUCTED"] == 'Y' ? GetMessage("SMOL_YES") : GetMessage("SMOL_NO");
	$arOrderItem["MARKED"] = $arOrderItem["MARKED"] == 'Y' ? GetMessage("SMOL_YES") : GetMessage("SMOL_NO");
	$arOrderItem["ALLOW_DELIVERY"] = $arOrderItem["ALLOW_DELIVERY"] == 'Y' ? GetMessage("SMOL_YES") : GetMessage("SMOL_NO");
	$arOrderItem["ADD_FIO"] = $arOrderItem["USER_NAME"]." ".$arOrderItem["USER_LAST_NAME"];
	$arOrderItem["ADD_PRICE"] = SaleFormatCurrency($arOrderItem["PRICE"], $arOrderItem["CURRENCY"]);

	if(trim($arOrderItem["ADD_FIO"]) == '')
		$arOrderItem["ADD_FIO"] = $arOrderItem["USER_LOGIN"];

	$arResult["ORDERS"][$arOrderItem["ID"]] = $arOrderItem;
	$ordersCount++;
}

if(!empty($arResult["ORDERS"]))
{
	$dbProdCount = CSaleBasket::GetList(
										array(),
										array("ORDER_ID" => $ordersIds),
										array("ORDER_ID"),
										false,
										array("ORDER_ID","CNT")
										);

	while($arProdCount = $dbProdCount->Fetch())
		$arResult["ORDERS"][$arProdCount["ORDER_ID"]]["ADD_PRODUCT_COUNT"] = $arProdCount["CNT"];

	$arDeliveries = array();
	if(in_array("DELIVERY", $arResult['VISIBLE_FIELDS']))
		$arDeliveries = CSaleMobileOrderUtils::getDeliveriesInfo($deliveryIds);

	$arPersonTypes = array();
	if(in_array("PERSON_TYPE", $arResult['VISIBLE_FIELDS']))
		$arPersonTypes = CSaleMobileOrderUtils::getPersonTypesNames($personTypesIds);

	$arPaySysNames = array();
	if(in_array("PAYED", $arResult['VISIBLE_FIELDS']))
		$arPaySysNames = CSaleMobileOrderUtils::getPaySystemsNames($paySysIds);

	/*
	$arStatNames = array();
	if(in_array("STATUS_NAME", $arResult['VISIBLE_FIELDS']))
		$arStatNames = CSaleMobileOrderUtils::getStatusesNames($statIds);
	*/

	$arStatNames = CSaleMobileOrderUtils::getStatusesNames();
	foreach ($arResult["ORDERS"] as $orderId => &$arOrder)
	{
		if(!is_null($arOrder["DELIVERY_ID"]) && isset($arDeliveries[$arOrder["DELIVERY_ID"]]))
			$arOrder["ADD_DELIVERY_NAME"] = $arDeliveries[$arOrder["DELIVERY_ID"]];
		else
			$arOrder["ADD_DELIVERY_NAME"] = GetMessage("SMOL_NONE");

		if(isset($arPersonTypes[$arOrder["PERSON_TYPE_ID"]]))
			$arOrder["PERSON_TYPE"] = $arPersonTypes[$arOrder["PERSON_TYPE_ID"]];

		if(isset($arPaySysNames[$arOrder["PAY_SYSTEM_ID"]]))
			$arOrder["ADD_PAY_SYSTEM_NAME"] = $arPaySysNames[$arOrder["PAY_SYSTEM_ID"]].' /';
		else
			$arOrder["ADD_PAY_SYSTEM_NAME"] = GetMessage("SMOL_NONE").' /';

		if(isset($arStatNames[$arOrder["STATUS_ID"]]))
			$arOrder["STATUS_NAME"] = $arStatNames[$arOrder["STATUS_ID"]];
	}
}

if(empty($arResult["ORDERS"]) || $ordersCount < SALE_ORDERS_INIT_COUNT)
	$arResult['BOTTOM_REACHED'] = true;
else
	$arResult['BOTTOM_REACHED'] = false;

$arResult["FILTER"] = $arFilter;
$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPage();
$arResult['AJAX_URL'] = $componentPath."/ajax.php";

$sitesCount = 0;
$rsSites = CSite::GetList();
while($arSite = $rsSites->GetNext())
	if(COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], ""))
		$sitesCount++;

$arResult["SHOP_SITES_COUNT"] = $sitesCount;

if(!$arResult['RETURN_AS_ARRAY'])
	CSaleMobileOrderPull::InitEventHandlers();

if($arResult["ACTION"] == "filter_edit")
{
	$arResult["CUSTOM_FILTER"] = CAdminMobileFilter::getFields($arResult["FILTER_ID"]);

	$filterFields = CSaleMobileOrderFilter::setFieldsValues($filterFields, $arResult["CUSTOM_FILTER"]);

	$arResult["FILTER_PARAMS"] = array(
		"TITLE" => GetMessage("SMOL_FILTER_TUNE"),
		"JS_EVENT_APPLY" => "onAfterFilterApply",
		"FILTER_ID" => $arResult["FILTER_ID"],
		"VISIBLE_FIELDS" => $arResult['VISIBLE_FIELDS'],
		"FIELDS" => $filterFields
		);
}

$this->IncludeComponentTemplate();
?>
