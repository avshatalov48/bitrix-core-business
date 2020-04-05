<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
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

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_user_id",
	"filter_login",
	"filter_user",
	"filter_transact_date_from",
	"filter_transact_date_to",
	"filter_order_id",
	"filter_currency"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_user_id) > 0) $arFilter["USER_ID"] = IntVal($filter_user_id);
if (strlen($filter_login) > 0) $arFilter["USER_LOGIN"] = $filter_login;
if (strlen($filter_user) > 0) $arFilter["%USER_USER"] = $filter_user;
if (strlen($filter_currency) > 0) $arFilter["CURRENCY"] = $filter_currency;
if (strlen($filter_transact_date_from)>0) $arFilter[">=TRANSACT_DATE"] = Trim($filter_transact_date_from);
if (IntVal($filter_order_id) > 0) $arFilter["ORDER_ID"] = IntVal($filter_order_id);
if (strlen($filter_transact_date_to)>0)
{
	if ($arDate = ParseDateTime($filter_transact_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (StrLen($filter_transact_date_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_transact_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=TRANSACT_DATE"] = $filter_transact_date_to;
	}
	else
	{
		$filter_transact_date_to = "";
	}
}

$nPageSize = CAdminResult::GetNavSize($sTableID);
$dbTransactList = CSaleUserTransact::GetList(
		array($by => $order),
		$arFilter,
		false,
		array("nPageSize"=>$nPageSize),
		array("*")
	);

$dbTransactList = new CAdminResult($dbTransactList, $sTableID);
$dbTransactList->NavStart();
$lAdmin->NavText($dbTransactList->GetNavPrint(GetMessage("STA_NAV")));


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
			array("nPageSize"=>$nPageSize),
			array("ID", "EMPLOYEE_ID")
		);

	$arTrUsers = array();
	while ($arTransact = $dbTransactList1->Fetch())
	{
		$tmpTrans[] = $arTransact;
		if(IntVal($arTransact["EMPLOYEE_ID"]) > 0 && !in_array($arTransact["EMPLOYEE_ID"], $arTrUsers))
			$arTrUsers[] = $arTransact["EMPLOYEE_ID"];
	}

	if(!empty($arTrUsers))
	{
		$dbUser = CUser::GetList($by = "ID", $or = "ASC", array("ID" => implode(' || ', array_keys($arTrUsers))), array("FIELDS" => array("ID", "LOGIN", "NAME", "LAST_NAME")));
		while($arUser = $dbUser->Fetch())
		{
			$LOCAL_TRANS_USER_CACHE[$arUser["ID"]] = htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
		}
	}
}

while ($arTransact = $dbTransactList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arTransact);

	$row->AddField("ID", $f_ID);
	$row->AddField("TRANSACT_DATE", $f_TRANSACT_DATE);

	$fieldValue  = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=".LANG."\" title=\"".GetMessage("STA_USER_INFO")."\">".$f_USER_ID."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_NAME"].((strlen($arTransact["USER_NAME"])<=0 || strlen($arTransact["USER_LAST_NAME"])<=0) ? "" : " ").$arTransact["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arTransact["USER_EMAIL"])."\" title=\"".GetMessage("STA_MAILTO")."\">".htmlspecialcharsEx($arTransact["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("AMOUNT", (($arTransact["DEBIT"] == "Y") ? "+" : "-").SaleFormatCurrency($arTransact["AMOUNT"], $arTransact["CURRENCY"])."<br><small>".(($arTransact["DEBIT"] == "Y") ? GetMessage("STA_TO_ACCOUNT") : GetMessage("STA_FROM_ACCOUNT"))."</small>");

	if (IntVal($arTransact["ORDER_ID"]) > 0)
		$fieldValue = "<a href=\"/bitrix/admin/sale_order_view.php?ID=".$arTransact["ORDER_ID"]."&lang=".LANG."\" title=\"".GetMessage("STA_ORDER_VIEW")."\">".$arTransact["ORDER_ID"]."</a>";
	else
		$fieldValue = "&nbsp;";
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
		if (IntVal($arTransact["EMPLOYEE_ID"]) > 0)
		{
			if (isset($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]])
				&& !empty($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]]))
			{
				$fieldValue .= "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arTransact["EMPLOYEE_ID"]."&lang=".LANG."\" title=\"".GetMessage("STA_USER_INFO")."\">".$arTransact["EMPLOYEE_ID"]."</a>] ";
				$fieldValue .= $LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]];
				$fieldValue .= "<br />";
			}
		}
		$fieldValue .= htmlspecialcharsEx($arTransact["NOTES"]);
		$fieldValue .= "</small>";
	}
	$row->AddField("DESCR", $fieldValue);

	/*
	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SAA_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_account_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""));
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SAA_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('SAA_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
	*/
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbTransactList->SelectedRowsCount()
		)
	)
);


if ($saleModulePermissions >= "U")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("STAN_ADD_NEW"),
			"LINK" => "sale_transact_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE" => GetMessage("STAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();



require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("STA_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STA_USER_ID"),
		GetMessage("STA_USER_LOGIN"),
		GetMessage("STA_CURRENCY"),
		GetMessage("STA_TRANS_DATE"),
		GetMessage("STA_ORDER_ID"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("STA_USER")?>:</td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_USER_ID")?>:</td>
		<td>
			<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_USER_LOGIN")?>:</td>
		<td>
			<input type="text" name="filter_login" size="50" value="<?= htmlspecialcharsbx($filter_login) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_CURRENCY")?>:</td>
		<td>
			<?= CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("STA_ALL"), True, "", ""); ?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("STA_TRANS_DATE")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_transact_date_from", $filter_transact_date_from, "filter_transact_date_to", $filter_transact_date_to, "bfilter", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("STA_ORDER_ID")?>:</td>
		<td>
			<input type="text" name="filter_order_id" size="5" value="<?= htmlspecialcharsbx($filter_order_id) ?>">
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>