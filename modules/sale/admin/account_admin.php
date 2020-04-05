<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sale_account";


$oSort = new CAdminSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminList($sTableID, $oSort);


$arFilterFields = array(
	"filter_user_id",
	"filter_login",
	"filter_user",
	"filter_currency",
	"filter_locked"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_user_id) > 0) $arFilter["USER_ID"] = IntVal($filter_user_id);
if (strlen($filter_login) > 0) $arFilter["USER_LOGIN"] = $filter_login;
if (strlen($filter_user) > 0) $arFilter["%USER_USER"] = $filter_user;
if (strlen($filter_currency) > 0) $arFilter["CURRENCY"] = $filter_currency;
if (strlen($filter_locked) > 0) $arFilter["LOCKED"] = $filter_locked;


if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "U")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleUserAccount::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arAccountList = $dbResultList->Fetch())
			$arID[] = $arAccountList['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				if ($saleModulePermissions >= "W")
				{
					$DB->StartTransaction();

					if ($arDelAccount = CSaleUserAccount::GetByID($ID))
					{
						if (CSaleUserAccount::UpdateAccount($arDelAccount["USER_ID"], -$arDelAccount["CURRENT_BUDGET"], $arDelAccount["CURRENCY"], "DEL_ACCOUNT", 0))
						{
							if (!CSaleUserAccount::Delete($ID))
							{
								$DB->Rollback();

								if ($ex = $APPLICATION->GetException())
									$lAdmin->AddGroupError($ex->GetString(), $ID);
								else
									$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_DELETE")), $ID);
							}
						}
						else
						{
							$DB->Rollback();

							if ($ex = $APPLICATION->GetException())
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							else
								$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_MONEY")), $ID);
						}
					}
					else
					{
						$DB->Rollback();

						if ($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError($ex->GetString(), $ID);
						else
							$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_GET")), $ID);
					}

					$DB->Commit();
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("SAA_NO_PERMS2DELETE"), $ID);
				}

				break;
			case "unlock":

				if (!CSaleUserAccount::UnLockByID($ID))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SAA_ERROR_UNLOCK")), $ID);
				}

				break;
		}
	}
}

$dbResultList = CSaleUserAccount::GetList(
	array($by => $order),
	$arFilter,
	false,
	array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
	array("*")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();


$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAA_NAV")));


$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"id", "default"=>true),
	array("id"=>"USER_ID","content"=>GetMessage("SAA_USER1"), "sort"=>"user_id", "default"=>true),
	array("id"=>"CURRENT_BUDGET", "content"=>GetMessage('SAA_SUM'),	"sort"=>"current_budget", "default"=>true),
	array("id"=>"LOCKED", "content"=>GetMessage("SAAN_LOCK_ACCT"),  "sort"=>"locked", "default"=>true),
	array("id"=>"TRANSACT", "content"=>GetMessage("SAAN_TRANSACT"),  "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();


while ($arAccount = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arAccount, "sale_account_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"), GetMessage("SAA_UPDATE_ALT"));

	$row->AddField("ID", $f_ID);

	$fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=".LANG."\" title=\"".GetMessage("SAA_USER_INFO")."\">".$f_USER_ID."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arAccount["USER_NAME"].((strlen($arAccount["USER_NAME"])<=0 || strlen($arAccount["USER_LAST_NAME"])<=0) ? "" : " ").$arAccount["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arAccount["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arAccount["USER_EMAIL"])."\" title=\"".GetMessage("SAA_MAILTO")."\">".htmlspecialcharsEx($arAccount["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("CURRENT_BUDGET", SaleFormatCurrency($arAccount["CURRENT_BUDGET"], $arAccount["CURRENCY"]));
	$row->AddField("LOCKED", (($arAccount["LOCKED"] != "Y") ? GetMessage("SAA_NO") : GetMessage("SAA_YES")));

	$fieldValue = "";
	if (in_array("TRANSACT", $arVisibleColumns))
	{
		$numTrans = CSaleUserTransact::GetList(
			array(),
			array(
				"USER_ID" => $f_USER_ID,
				"CURRENCY" => $f_CURRENCY
			),
			array()
		);
		if (IntVal($numTrans) > 0)
		{
			$fieldValue .= "<a href=\"sale_transact_admin.php?lang=".LANG."&filter_user_id=".$f_USER_ID."&filter_currency=".$f_CURRENCY."&set_filter=Y\" title=\"".GetMessage("SAA_TRANS_TITLE")."\">";
			$fieldValue .= IntVal($numTrans);
			$fieldValue .= "</a>";
		}
		else
		{
			$fieldValue .= 0;
		}
	}
	$row->AddField("TRANSACT", $fieldValue);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SAA_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_account_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SAA_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('SAA_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}


$arFooterArray = array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $dbResultList->SelectedRowsCount()
	),
);

$dbAccountCurrency = CSaleUserAccount::GetList(
		array("CURRENCY" => "ASC"),
		$arFilter,
		array("CURRENCY", "SUM" => "CURRENT_BUDGET"),
		false,
		array("CURRENCY", "SUM" => "CURRENT_BUDGET")
	);
while ($arAccountCurrency = $dbAccountCurrency->Fetch())
{
	$arFooterArray[] = array(
		"title" => GetMessage("SAA_ITOG")." ".$arAccountCurrency["CURRENCY"].":",
		"value" => SaleFormatCurrency($arAccountCurrency["CURRENT_BUDGET"], $arAccountCurrency["CURRENCY"])
	);
}

$order_sum = "";
foreach($arFooterArray as $val)
{
	$order_sum .= $val["title"]." ".$val["value"]."<br />";
}
$lAdmin->sEpilogContent = "<script>setTimeout(function(){if (document.getElementById('order_sum'))document.getElementById('order_sum').innerHTML = '".CUtil::JSEscape($order_sum)."';}, 10);</script>";


$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"unlock" => GetMessage("SAAN_UNLOCK_DO")
	)
);

$aContext = Array();
if ($saleModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SAAN_ADD_NEW"),
			"LINK" => "sale_account_edit.php?lang=".LANG.GetFilterParams("filter_"),
			"TITLE" => GetMessage("SAAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
}
$lAdmin->AddAdminContextMenu($aContext);


$lAdmin->CheckListMode();


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SAA_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SAA_USER_ID"),
		GetMessage("SAA_USER_LOGIN"),
		GetMessage("SAA_CURRENCY"),
		GetMessage("SAA_LOCKED"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SAA_USER")?>:</td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAA_USER_ID")?>:</td>
		<td>
			<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAA_USER_LOGIN")?>:</td>
		<td>
			<input type="text" name="filter_login" size="50" value="<?= htmlspecialcharsbx($filter_login) ?>">
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("SAA_CURRENCY")?>:</td>
		<td align="left" nowrap>
			<?= CCurrency::SelectBox("filter_currency", $filter_currency, GetMessage("SAA_ALL"), true, "", ""); ?>
		</td>
	</tr>
	<tr>
		<td nowrap><?echo GetMessage("SAA_LOCKED")?>:</td>
		<td>
			<select name="filter_locked">
				<option value=""><?echo GetMessage("SAA_ALL")?></option>
				<option value="Y"<?if ($filter_locked=="Y") echo " selected"?>><?= htmlspecialcharsEx(GetMessage("SAA_YES")) ?></option>
				<option value="N"<?if ($filter_locked=="N") echo " selected"?>><?= htmlspecialcharsEx(GetMessage("SAA_NO")) ?></option>
			</select>
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

echo BeginNote();
?><span id="order_sum"><?print_r($order_sum);?></span><?
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");