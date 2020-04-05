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

if(!CBXFeatures::IsFeatureEnabled('SaleRecurring'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sale_recurring";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_user_id",
	"filter_login",
	"filter_user",
	"filter_canceled",
	"filter_prior_date_from",
	"filter_prior_date_to",
	"filter_next_date_from",
	"filter_next_date_to",
	"filter_order_id",
	"filter_success_payment"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_user_id) > 0) $arFilter["USER_ID"] = IntVal($filter_user_id);
if (strlen($filter_login) > 0) $arFilter["USER_LOGIN"] = $filter_login;
if (strlen($filter_user) > 0) $arFilter["%USER_USER"] = $filter_user;
if (strlen($filter_canceled) > 0) $arFilter["CANCELED"] = $filter_canceled;
if (strlen($filter_prior_date_from)>0) $arFilter[">=PRIOR_DATE"] = Trim($filter_prior_date_from);
if (strlen($filter_prior_date_to)>0) $arFilter["<=PRIOR_DATE"] = Trim($filter_prior_date_to);
if (strlen($filter_next_date_from)>0) $arFilter[">=NEXT_DATE"] = Trim($filter_next_date_from);
if (strlen($filter_next_date_to)>0) $arFilter["<=NEXT_DATE"] = Trim($filter_next_date_to);
if (IntVal($filter_order_id) > 0) $arFilter["ORDER_ID"] = IntVal($filter_order_id);
if (strlen($filter_success_payment)>0) $arFilter["SUCCESS_PAYMENT"] = Trim($filter_success_payment);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "U")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();

		$dbResultList = CSaleRecurring::GetList(
			array($by => $order),
			$arFilter,
			false,
			false,
			array("ID")
		);
		while ($arRecurringList = $dbResultList->Fetch())
			$arID[] = $arRecurringList['ID'];
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

					if (!CSaleRecurring::Delete($ID))
					{
						$DB->Rollback();

						if ($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError($ex->GetString(), $ID);
						else
							$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SRA_ERROR_DELETE")), $ID);
					}

					$DB->Commit();
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("SRA_NO_PERMS2DELETE"), $ID);
				}

				break;

			case "cancel":
			case "uncancel":
				$arFields = array(
						"CANCELED" => (($_REQUEST['action']=="cancel") ? "Y" : "N")
					);
				if ($_REQUEST['action'] != "cancel")
					$arFields["REMAINING_ATTEMPTS"] = (Defined("SALE_PROC_REC_ATTEMPTS") ? SALE_PROC_REC_ATTEMPTS : 3);

				if (!CSaleRecurring::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $id, GetMessage("SRA_ERROR_UPDATE")), $ID);
				}

				break;
		}
	}
}


$dbResultList = CSaleRecurring::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("*")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SRA_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"id", "default"=>true),
	array("id"=>"USER_ID","content"=>GetMessage("SRA_USER1"), "sort"=>"user_id", "default"=>true),
	array("id"=>"CANCELED", "content"=>GetMessage('SRA_CANC'),	"sort"=>"canceled", "default"=>true),
	array("id"=>"PRIOR_DATE", "content"=>GetMessage("SRA_LAST_RENEW"),  "sort"=>"prior_date", "default"=>true),
	array("id"=>"NEXT_DATE", "content"=>GetMessage("SRA_NEXT_RENEW"),  "sort"=>"next_date", "default"=>true),
	array("id"=>"SUCCESS_PAYMENT", "content"=>GetMessage("SRA_SUCCESS_PAY"),  "sort"=>"success_payment", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arRecurring = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRecurring);

	$row->AddField("ID", $f_ID);

	$fieldValue  = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=".LANG."\">".$f_USER_ID."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arRecurring["USER_NAME"].((strlen($arRecurring["USER_NAME"])<=0 || strlen($arRecurring["USER_LAST_NAME"])<=0) ? "" : " ").$arRecurring["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arRecurring["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arRecurring["USER_EMAIL"])."\">".htmlspecialcharsEx($arRecurring["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("CANCELED", (($arRecurring["CANCELED"]=="Y") ? GetMessage("SRA_YES") : GetMessage("SRA_NO")));
	$row->AddField("PRIOR_DATE", $arRecurring["PRIOR_DATE"]."&nbsp;");
	$row->AddField("NEXT_DATE", $arRecurring["NEXT_DATE"]."&nbsp;");

	if ($arRecurring["SUCCESS_PAYMENT"] == "Y")
		$fieldValue = GetMessage("SRA_YES");
	else
		$fieldValue = GetMessage("SRA_UNSECCESS").$arRecurring["REMAINING_ATTEMPTS"]."";
	$row->AddField("SUCCESS_PAYMENT", $fieldValue);
	
	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SRA_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_recurring_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SRA_DELETE_ALT1"), "ACTION"=>"if(confirm('".GetMessage('SRA_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"cancel" => GetMessage("SRAN_CANCEL_REC"),
		"uncancel" => GetMessage("SRAN_UNCANCEL_REC")
	)
);

if ($saleModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SRAN_ADD_NEW"),
			"LINK" => "sale_recurring_edit.php?lang=".LANG,
			"ICON" => "btn_new",
			"TITLE" => GetMessage("SRAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SRA_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SRA_USER_ID"),
		GetMessage("SRA_USER_LOGIN"),
		GetMessage("SRA_CANCELED"),
		GetMessage("SRA_LAST_UPDATE"),
		GetMessage("SRA_NEXT_UPDATE"),
		GetMessage("SRA_BASE_ORDER"),
		GetMessage("SRA_SUCCESSFULL"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SRA_USER")?></td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_USER_ID")?></td>
		<td>
			<input type="text" name="filter_user_id" size="5" value="<?= htmlspecialcharsbx($filter_user_id) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_USER_LOGIN")?></td>
		<td>
			<input type="text" name="filter_login" size="50" value="<?= htmlspecialcharsbx($filter_login) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_CANCELED")?></td>
		<td>
			<select name="filter_canceled">
				<option value=""><?= htmlspecialcharsex(GetMessage("SRA_ALL")); ?></option>
				<option value="Y"<?if ($filter_canceled=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SRA_YES")) ?></option>
				<option value="N"<?if ($filter_canceled=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SRA_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_LAST_UPDATE")?></td>
		<td>
			<?echo CalendarPeriod("filter_prior_date_from", $filter_prior_date_from, "filter_prior_date_to", $filter_prior_date_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_NEXT_UPDATE")?></td>
		<td>
			<?echo CalendarPeriod("filter_next_date_from", $filter_next_date_from, "filter_next_date_to", $filter_next_date_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_BASE_ORDER")?></td>
		<td>
			<input type="text" name="filter_order_id" size="50" value="<?= htmlspecialcharsbx($filter_order_id) ?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SRA_SUCCESSFULL")?></td>
		<td>
			<select name="filter_success_payment">
				<option value=""><?= htmlspecialcharsex(GetMessage("SRA_ALL")); ?></option>
				<option value="Y"<?if ($filter_success_payment=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SRA_YES")) ?></option>
				<option value="N"<?if ($filter_success_payment=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SRA_NO")) ?></option>
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

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>