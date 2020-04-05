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

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$arTransactTypes = array(
	"AFFILIATE_IN" => GetMessage("SAT2_AFF_IN"),
	"AFFILIATE_ACCT" => GetMessage("SAT2_AFF_ACCT"),
	"AFFILIATE_CLEAR" => GetMessage("SAT2_AFFILIATE_CLEAR"),
);

$sTableID = "tbl_sale_affiliate_transact";

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_affiliate_id",
	"filter_user",
	"filter_transact_date_from",
	"filter_transact_date_to",
	"filter_currency"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_affiliate_id) > 0) $arFilter["AFFILIATE_ID"] = IntVal($filter_affiliate_id);
if (strlen($filter_user) > 0) $arFilter["%USER_USER"] = $filter_user;
if (strlen($filter_currency) > 0) $arFilter["CURRENCY"] = $filter_currency;
if (strlen($filter_transact_date_from)>0) $arFilter[">=TRANSACT_DATE"] = Trim($filter_transact_date_from);
if (strlen($filter_transact_date_to)>0) $arFilter["<=TRANSACT_DATE"] = Trim($filter_transact_date_to);


$dbTransactList = CSaleAffiliateTransact::GetList(
		array($by => $order),
		$arFilter,
		false,
		array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
		array("ID", "AFFILIATE_ID", "TIMESTAMP_X", "TRANSACT_DATE", "AMOUNT", "CURRENCY", "DEBIT", "DESCRIPTION", "EMPLOYEE_ID", "AFFILIATE_SITE_ID", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL")
	);

$dbTransactList = new CAdminResult($dbTransactList, $sTableID);
$dbTransactList->NavStart();

$lAdmin->NavText($dbTransactList->GetNavPrint(GetMessage("STA_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"TRANSACT_DATE","content"=>GetMessage("SAT2_TRANSACT_DATE"), "sort"=>"TRANSACT_DATE", "default"=>true),
	array("id"=>"AFFILIATE_ID", "content"=>GetMessage("SAT2_AFFILIATE"), "sort"=>"AFFILIATE_ID", "default"=>true),
	array("id"=>"AMOUNT", "content"=>GetMessage("SAT2_SUM"), "sort"=>"AMOUNT", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("SAT2_TYPE"), "sort"=>"DESCRIPTION", "default"=>true),
	array("id"=>"DESCR", "content"=>GetMessage("SAT2_DESCR"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arTransact = $dbTransactList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arTransact);

	$row->AddField("ID", $f_ID);
	$row->AddField("TRANSACT_DATE", $f_TRANSACT_DATE);

	$fieldValue  = "[<a href=\"/bitrix/admin/sale_affiliate_edit.php?ID=".$f_AFFILIATE_ID."&lang=".LANG."\" title=\"".GetMessage("SAT2_AFF_PROFILE")."\">".$f_AFFILIATE_ID."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_NAME"].((strlen($arTransact["USER_NAME"])<=0 || strlen($arTransact["USER_LAST_NAME"])<=0) ? "" : " ").$arTransact["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arTransact["AFFILIATE_SITE_ID"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= htmlspecialcharsEx($arTransact["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arTransact["USER_EMAIL"])."\" title=\"".GetMessage("SAT2_MAIL")."\">".htmlspecialcharsEx($arTransact["USER_EMAIL"])."</a>";
	$row->AddField("AFFILIATE_ID", $fieldValue);

	$row->AddField("AMOUNT", (($arTransact["DEBIT"] == "Y") ? "+" : "-").SaleFormatCurrency($arTransact["AMOUNT"], $arTransact["CURRENCY"])."<br><small>".(($arTransact["DEBIT"] == "Y") ? GetMessage("SAT2_TO_ACCT") : GetMessage("SAT2_FROM_ACCT"))."</small>");

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
			if (!isset($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]])
				|| !is_array($LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]]))
			{
				$dbUser = CUser::GetByID($arTransact["EMPLOYEE_ID"]);
				if ($arUser = $dbUser->Fetch())
					$LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]] = htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
			}
			$fieldValue .= "[<a href=\"/bitrix/admin/user_edit.php?ID=".$arTransact["EMPLOYEE_ID"]."&lang=".LANG."\" title=\"".GetMessage("SAT2_USER_PROFILE")."\">".$arTransact["EMPLOYEE_ID"]."</a>] ";
			$fieldValue .= $LOCAL_TRANS_USER_CACHE[$arTransact["EMPLOYEE_ID"]];
		}
		$fieldValue .= "</small>";
	}
	$row->AddField("DESCR", $fieldValue);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbTransactList->SelectedRowsCount()
		)
	)
);

$lAdmin->CheckListMode();



require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SAT2_AFF_TRANSACTIONS"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(

		GetMessage("SAT2_USER"),
		GetMessage("SAT2_TRANSACT_DATE"),
		GetMessage("SAT2_CURRENCY"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SAT2_AFFILIATE1")?></td>
		<td>
			<input type="text" name="filter_affiliate_id" value="<?= htmlspecialcharsbx($filter_affiliate_id) ?>" size="10" maxlength="10">
			<IFRAME name="hiddenframe_affiliate" id="id_hiddenframe_affiliate" src="" width="0" height="0" style="width:0px; height:0px; border: 0px"></IFRAME>
			<input type="button" class="button" name="FindAffiliate" OnClick="window.open('/bitrix/admin/sale_affiliate_search.php?func_name=SetAffiliateID', '', 'scrollbars=yes,resizable=yes,width=800,height=500,top='+Math.floor((screen.height - 500)/2-14)+',left='+Math.floor((screen.width - 400)/2-5));" value="...">
			<span id="div_affiliate_name"></span>
			<SCRIPT LANGUAGE=javascript>
			<!--
			function SetAffiliateID(id)
			{
				document.find_form.filter_affiliate_id.value = id;
			}

			function SetAffiliateName(val)
			{
				if (val != "NA")
					document.getElementById('div_affiliate_name').innerHTML = val;
				else
					document.getElementById('div_affiliate_name').innerHTML = '<?= GetMessage("SAT2_NO_AFFILIATE") ?>';
			}

			var affiliateID = '';
			function ChangeAffiliateName()
			{
				if (affiliateID != document.find_form.filter_affiliate_id.value)
				{
					affiliateID = document.find_form.filter_affiliate_id.value;
					if (affiliateID != '' && !isNaN(parseInt(affiliateID, 10)))
					{
						document.getElementById('div_affiliate_name').innerHTML = '<i><?= GetMessage("SAT2_WAIT") ?></i>';
						window.frames["hiddenframe_affiliate"].location.replace('/bitrix/admin/sale_affiliate_get.php?ID=' + affiliateID + '&func_name=SetAffiliateName');
					}
					else
						document.getElementById('div_affiliate_name').innerHTML = '';
				}
				timerID = setTimeout('ChangeAffiliateName()',2000);
			}
			ChangeAffiliateName();
			//-->
			</SCRIPT>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAT2_USER1")?></td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("SAT2_DATE_TRANSACT")?></td>
		<td>
			<?echo CalendarPeriod("filter_transact_date_from", $filter_transact_date_from, "filter_transact_date_to", $filter_transact_date_to, "bfilter", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAT2_CURRENCY1")?></td>
		<td>
			<?= CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("SAT2_ALL"), True, "", ""); ?>
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