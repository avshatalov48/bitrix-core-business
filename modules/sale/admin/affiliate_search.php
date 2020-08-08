<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$CATALOG_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($CATALOG_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_affiliate_search";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_user",
	"filter_plan_id",
	"filter_active",
	"filter_last_calculate_from",
	"filter_last_calculate_to",
	"filter_date_create_from",
	"filter_date_create_to",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
$func_name = preg_replace("/[^a-zA-Z0-9_-]/is", "", $func_name);

if ($filter_site_id != "NOT_REF" && $filter_site_id <> '')
	$arFilter["SITE_ID"] = $filter_site_id;
else
	Unset($arFilter["SITE_ID"]);

if ($filter_user <> '')
	$arFilter["%USER_USER"] = $filter_user;
if (intval($filter_plan_id) > 0)
	$arFilter["PLAN_ID"] = intval($filter_plan_id);
if ($filter_active <> '')
	$arFilter["ACTIVE"] = (($filter_active == "Y") ? "Y" : "N");
if ($filter_last_calculate_from <> '')
	$arFilter[">=LAST_CALCULATE"] = $filter_last_calculate_from;
if ($filter_last_calculate_to <> '')
	$arFilter["<=LAST_CALCULATE"] = $filter_last_calculate_to;
if ($filter_date_create_from <> '')
	$arFilter[">=DATE_CREATE"] = $filter_date_create_from;
if ($filter_date_create_to <> '')
	$arFilter["<=DATE_CREATE"] = $filter_date_create_to;

$dbResultList = CSaleAffiliate::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SITE_ID", "USER_ID", "AFFILIATE_ID", "PLAN_ID", "ACTIVE", "TIMESTAMP_X", "DATE_CREATE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "ITEMS_NUMBER", "ITEMS_SUM", "LAST_CALCULATE", "PLAN_NAME", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAS1_AFF")));

$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage("SAS1_SITE"), "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"USER_ID", "content" => GetMessage("SAS1_USER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"PLAN_ID", "content"=>GetMessage("SAS1_PLAN"), "sort"=>"PLAN_ID", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SAS1_ACTIVE"), "sort"=>"ACTIVE", "default"=>false),
	array("id"=>"DATE_CREATE", "content"=>GetMessage("SAS1_DATE_CREATE"), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"PAID_SUM", "content"=>GetMessage("SAS1_PAID_SUM"), "sort"=>"PAID_SUM", "default"=>false),
	array("id"=>"PENDING_SUM", "content"=>GetMessage("SAS1_PENDING_SUM"), "sort"=>"PENDING_SUM", "default"=>false),
	array("id"=>"LAST_CALCULATE", "content"=>GetMessage("SAS1_LAST_CALC"), "sort"=>"LAST_CALCULATE", "default"=>false),
	array("id"=>"ACT", "content"=>"&nbsp;", "default"=>true),
);

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arItems = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arItems);

	$row->AddField("ID", $f_ID);
	$row->AddField("SITE_ID", $f_SITE_ID);

	$fieldValue = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=".LANG."\" target=\"_blank\" title=\"".GetMessage("SAS1_GOTO_USER")."\">".$f_USER_ID."</a>] ";
	$fieldValue .= $f_USER_NAME.(($f_USER_NAME == '' || $f_USER_LAST_NAME == '') ? "" : " ").$f_USER_LAST_NAME."<br>";
	$fieldValue .= $f_USER_LOGIN."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".$f_USER_EMAIL."\" title=\"".GetMessage("SAS1_GOTO_USER_EMAIL")."\">".$f_USER_EMAIL."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddField("PLAN_ID", "[".$f_PLAN_ID."] ".$f_PLAN_NAME);
	$row->AddField("ACTIVE", $f_ACTIVE);
	$row->AddField("DATE_CREATE", $f_DATE_CREATE);
	$row->AddField("PAID_SUM", $f_PAID_SUM);
	$row->AddField("PENDING_SUM", $f_PENDING_SUM);
	$row->AddField("LAST_CALCULATE", $f_LAST_CALCULATE);

	$row->AddField("ACT", "<a href=\"javascript:void(0)\" onClick=\"SelEl(".$f_ID.")\">".GetMessage("SAS1_SELECT")."</a>");
	$arActions = array();
	$arActions[] = array(
		"ICON"=>"",
		"TEXT"=>GetMessage("SAS1_SELECT"),
		"DEFAULT"=>true,
		"ACTION"=>"SelEl(".$f_ID.");",

	);
	$row->AddActions($arActions);

}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
	)
);

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SAS1_SEARCH"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>

<script language="JavaScript">
<!--
function SelEl(id)
{
	window.opener.<?= $func_name ?>(id);
	window.close();
}
//-->
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="func_name" value="<?echo htmlspecialcharsbx($func_name)?>">
	<?
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
			GetMessage("SAS1_USER"),
			GetMessage("SAS1_PLAN"),
			GetMessage("SAS1_ACTIVE"),
			GetMessage("SAS1_LAST_CALC"),
			GetMessage("SAS1_DATE_REG"),
		)
	);

	$oFilter->Begin();
	?>
	<tr>
		<td><?echo GetMessage("SAS1_SITE1")?></td>
		<td><?echo CSite::SelectBox("filter_site_id", $filter_site_id, GetMessage("SAS1_ALL")) ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAS1_USER1")?></td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAS1_PLAN1")?></td>
		<td>
			<select name="filter_plan_id">
				<option value=""><?= htmlspecialcharsex(GetMessage("SAS1_ALL")); ?></option>
				<?
				$dbPlan = CSaleAffiliatePlan::GetList(array("NAME" => "ASC"), array(), false, false, array("ID", "NAME", "SITE_ID"));
				while ($arPlan = $dbPlan->Fetch())
				{
					?><option value="<?= $arPlan["ID"] ?>"<?if ($filter_plan_id == $arPlan["ID"]) echo " selected"?>><?= htmlspecialcharsex("[".$arPlan["ID"]."] ".$arPlan["NAME"]." (".$arPlan["SITE_ID"].")") ?></option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAS1_ACTIVE1")?></td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex(GetMessage("SAS1_ALL")); ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SAS1_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SAS1_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAS1_LAST_CALC1")?></td>
		<td>
			<?echo CalendarPeriod("filter_last_calculate_from", $filter_last_calculate_from, "filter_last_calculate_to", $filter_last_calculate_to, "find_form", "Y")?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAS1_DATE_REG1")?></td>
		<td>
			<?echo CalendarPeriod("filter_date_create_from", $filter_date_create_from, "filter_date_create_to", $filter_date_create_to, "find_form", "Y")?>
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
?>
<br>
<input type="button" class="typebutton" value="<?echo GetMessage("SAS1_CLOSE")?>" onClick="window.close();">
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>