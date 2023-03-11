<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);

$arTransactTypes = array(
	"ORDER_PAY" => GetMessage("STA_TPAY"),
	"ORDER_PAY_PART" => GetMessage("STA_TPAY_PART"),
	"CC_CHARGE_OFF" => GetMessage("STA_TFROM_CARD"),
	"OUT_CHARGE_OFF" => GetMessage("STA_TMONEY"),
	"ORDER_UNPAY" => GetMessage("STA_TCANCEL_ORDER"),
	"ORDER_CANCEL_PART" => GetMessage("STA_TCANCEL_SEMIORDER"),
	"MANUAL" => GetMessage("STA_THAND"),
	"DEL_ACCOUNT" => GetMessage("STA_TDEL"),
	"AFFILIATE" => GetMessage("STA_AF_VIP"),
	"EXCESS_SUM_PAID" => GetMessage("STA_TTRANSF_EXCESS_SUM_PAID"),
	"ORDER_PART_RETURN" => GetMessage("STA_TRETURN")
);

$sTableID = "tbl_sale_transact";

$oSort = new CAdminUiSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$listCurrency = array();
$currencyList = Bitrix\Currency\CurrencyManager::getCurrencyList();
foreach ($currencyList as $currencyId => $currencyName)
{
	$listCurrency[$currencyId] = $currencyName;
}

$filterFields = array(
	array(
		"id" => "USER_USER",
		"name" => GetMessage('STA_USER'),
		"filterable" => "%",
		"quickSearch" => "%",
		"default" => true
	),
	array(
		"id" => "USER_ID",
		"name" => GetMessage('STA_USER_ID'),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "USER_LOGIN",
		"name" => GetMessage("STA_USER_LOGIN"),
		"filterable" => ""
	),
	array(
		"id" => "CURRENCY",
		"name" => GetMessage("STA_CURRENCY"),
		"type" => "list",
		"items" => $listCurrency,
		"filterable" => ""
	),
	array(
		"id" => "TRANSACT_DATE",
		"name" => GetMessage("STA_TRANS_DATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "ORDER_ID",
		"name" => GetMessage("STA_ORDER_ID"),
		"filterable" => ""
	),
);

$arFilter = array();

if (!empty($_GET["filter_user_id"]))
{
	$arFilter["USER_ID"] = intval($_GET["filter_user_id"]);
}
if (!empty($_GET["USER_ID"]))
{
	$arFilter["USER_ID"] = intval($_GET["USER_ID"]);
}
if (!empty($_GET["filter_currency"]))
{
	$arFilter["CURRENCY"] = $_GET["filter_currency"];
}

$lAdmin->AddFilter($filterFields, $arFilter);

global $by, $order;

$dbTransactList = CSaleUserTransact::GetList(array($by => $order), $arFilter, false, false, array("*"));

$dbTransactList = new CAdminUiResult($dbTransactList, $sTableID);
$dbTransactList->NavStart();
$lAdmin->SetNavigationParams($dbTransactList, array("BASE_LINK" => $selfFolderUrl."sale_transact_admin.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"TRANSACT_DATE","content"=>GetMessage("STA_TRANS_DATE1"), "sort"=>"transact_date", "default"=>true),
	array("id"=>"USER_ID", "content"=>GetMessage('STA_USER1'),"sort"=>"user_id", "default"=>true),
	array("id"=>"AMOUNT", "content"=>GetMessage("STA_SUM"), "sort"=>"amount", "default"=>true),
	array("id"=>"ORDER_ID", "content"=>GetMessage("STA_ORDER"), "sort"=>"order_id", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("STA_TYPE"), "sort"=>"description", "default"=>true),
	array("id"=>"DESCR", "content"=>GetMessage("STA_DESCR"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$LOCAL_TRANS_USER_CACHE = array();

if (in_array("DESCR", $arVisibleColumns))
{
	$dbTransactList1 = CSaleUserTransact::GetList(
			array($by => $order),
			$arFilter,
			false,
			array("nPageSize" => CAdminUiResult::GetNavSize($sTableID)),
			array("ID", "EMPLOYEE_ID")
		);

	$arTrUsers = array();
	while ($arTransact = $dbTransactList1->Fetch())
	{
		$tmpTrans[] = $arTransact;
		if(intval($arTransact["EMPLOYEE_ID"]) > 0 && !in_array($arTransact["EMPLOYEE_ID"], $arTrUsers))
			$arTrUsers[] = $arTransact["EMPLOYEE_ID"];
	}

	if(!empty($arTrUsers))
	{
		$dbUser = CUser::GetList("ID", "ASC", array("ID" => implode(' || ', array_keys($arTrUsers))), array("FIELDS" => array("ID", "LOGIN", "NAME", "LAST_NAME")));
		while($arUser = $dbUser->Fetch())
		{
			$LOCAL_TRANS_USER_CACHE[$arUser["ID"]] = htmlspecialcharsEx($arUser["NAME"].(($arUser["NAME"] == '' || $arUser["LAST_NAME"] == '') ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
		}
	}
}

while ($arTransact = $dbTransactList->NavNext(false))
{
	$row =& $lAdmin->AddRow($arTransact["ID"], $arTransact);

	$row->AddField("ID", $arTransact["ID"]);
	$row->AddField("TRANSACT_DATE", $arTransact["TRANSACT_DATE"]);

	$urlToUser = $selfFolderUrl."user_edit.php?ID=".$arTransact["USER_ID"]."&lang=".LANGUAGE_ID;
	if ($publicMode)
	{
		$urlToUser = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$arTransact["USER_ID"]."&lang=".LANGUAGE_ID;
		$urlToUser = $adminSidePanelHelper->editUrlToPublicPage($urlToUser);
	}
	$fieldValue  = "[<a href=\"".$urlToUser."\" title=\"".GetMessage("STA_USER_INFO")."\">".$arTransact["USER_ID"]."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_NAME"].(($arTransact["USER_NAME"] == '' ||
			$arTransact["USER_LAST_NAME"] == '') ? "" : " ").$arTransact["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arTransact["USER_EMAIL"])."\" title=\"".
		GetMessage("STA_MAILTO")."\">".htmlspecialcharsEx($arTransact["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("AMOUNT", (($arTransact["DEBIT"] == "Y") ? "+" : "-").SaleFormatCurrency($arTransact["AMOUNT"],
			$arTransact["CURRENCY"])."<br><small>".(($arTransact["DEBIT"] == "Y") ? GetMessage("STA_TO_ACCOUNT") : GetMessage("STA_FROM_ACCOUNT"))."</small>");

	if (intval($arTransact["ORDER_ID"]) > 0)
	{
		$orderViewUrl = $selfFolderUrl."sale_order_view.php?ID=".$arTransact["ORDER_ID"]."&lang=".LANGUAGE_ID;
		if ($publicMode)
		{
			$orderViewUrl = "/shop/orders/details/".$arTransact["ORDER_ID"]."/";
		}
		$fieldValue = "<a href=\"".$orderViewUrl."\" title=\"".GetMessage("STA_ORDER_VIEW")."\">".$arTransact["ORDER_ID"]."</a>";
	}
	else
	{
		$fieldValue = "&nbsp;";
	}
	$row->AddField("ORDER_ID", $fieldValue);

	if (array_key_exists($arTransact["DESCRIPTION"], $arTransactTypes))
		$fieldValue = htmlspecialcharsEx($arTransactTypes[$arTransact["DESCRIPTION"]]);
	else
		$fieldValue = htmlspecialcharsEx($arTransact["DESCRIPTION"]);
	$row->AddField("TYPE", $fieldValue);

	$fieldValue = "&nbsp;";
	if (in_array("DESCR", $arVisibleColumns))
	{
		$fieldValue .= "<small>";
		if (intval($arTransact["EMPLOYEE_ID"]) > 0)
		{
			if (isset($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]])
				&& !empty($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]]))
			{
				$urlToUser = $selfFolderUrl."user_edit.php?ID=".$arTransact["EMPLOYEE_ID"]."&lang=".LANGUAGE_ID;
				if ($publicMode)
				{
					$urlToUser = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$arTransact["EMPLOYEE_ID"]."&lang=".LANGUAGE_ID;
					$urlToUser = $adminSidePanelHelper->editUrlToPublicPage($urlToUser);
				}
				$fieldValue .= "[<a href=\"".$urlToUser."\" title=\"".GetMessage("STA_USER_INFO")."\">".$arTransact["EMPLOYEE_ID"]."</a>] ";
				$fieldValue .= $LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]];
				$fieldValue .= "<br />";
			}
		}
		$fieldValue .= htmlspecialcharsEx($arTransact["NOTES"]);
		$fieldValue .= "</small>";
	}
	$row->AddField("DESCR", $fieldValue);
}

if ($saleModulePermissions >= "U")
{
	$addUrl = $selfFolderUrl."sale_transact_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("STAN_ADD_NEW"),
			"LINK" => $addUrl,
			"TITLE" => GetMessage("STAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_transact_admin.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("STA_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$lAdmin->DisplayFilter($filterFields);
	$lAdmin->DisplayList();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
