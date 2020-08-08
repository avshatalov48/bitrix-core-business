<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_affiliate_plan";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_active",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($filter_site_id != "NOT_REF" && $filter_site_id <> '')
	$arFilter["SITE_ID"] = $filter_site_id;
else
	Unset($arFilter["SITE_ID"]);

if ($filter_active <> '')
	$arFilter["ACTIVE"] = (($filter_active == "Y") ? "Y" : "N");

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CSaleAffiliatePlan::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SAP1_ERROR_UPDATE_PLAN"), $ID);

			$DB->Rollback();
		}

		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleAffiliatePlan::GetList(array(), $arFilter, false, false, array("ID"));
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleAffiliatePlan::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAP1_ERROR_DELETE_PLAN"), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CSaleAffiliatePlan::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAP1_ERROR_ACTIVE_PLAN"), $ID);
				}

				break;
		}
	}
}

$dbResultList = CSaleAffiliatePlan::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SITE_ID", "NAME", "DESCRIPTION", "TIMESTAMP_X", "ACTIVE", "BASE_RATE", "BASE_RATE_TYPE", "BASE_RATE_CURRENCY", "MIN_PAY", "MIN_PLAN_VALUE")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAP1_PLANS")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage("SAP1_SITE"), "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"ACTIVE", "content" => GetMessage("SAP1_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SAP1_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"RATE", "content"=>GetMessage("SAP1_RATE"), "sort"=>"", "default"=>true),
	array("id"=>"MIN_PLAN_VALUE", "content"=>GetMessage("SAP1_NOT_LESS"), "sort"=>"MIN_PLAN_VALUE", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSites = array();
$dbSiteList = CSite::GetList(($b = "sort"), ($o = "asc"));
while ($arSite = $dbSiteList->Fetch())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."]&nbsp;".$arSite["NAME"];

$arCurrencies = array("P" => "%");
$dbCurrencyList = CCurrency::GetList(($b = "currency"), ($o = "asc"));
while ($arCurrency = $dbCurrencyList->Fetch())
	$arCurrencies[$arCurrency["CURRENCY"]] = "[".$arCurrency["CURRENCY"]."]&nbsp;".$arCurrency["FULL_NAME"];

$affiliatePlanType = COption::GetOptionString("sale", "affiliate_plan_type", "N");
$arBaseLangCurrencies = array();

while ($arPlan = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arPlan, "sale_affiliate_plan_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"), GetMessage("SAP1_UPDATE_PLAN"));

	$row->AddField("ID", $f_ID);
	$row->AddSelectField("SITE_ID", $arSites, array());
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME", array("size" => "20"));

	
	if ($f_BASE_RATE_TYPE == "P")
		$fieldValue = $f_BASE_RATE."%";
	else
		$fieldValue = SaleFormatCurrency($f_BASE_RATE, $f_BASE_RATE_CURRENCY);

	if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
		$val = $_REQUEST["FIELDS"][$f_ID]["BASE_RATE"];
	else
		$val = $f_BASE_RATE;

	$fieldEdit = "<input type=\"text\" name=\"FIELDS[".$f_ID."][BASE_RATE]\" value=\"".htmlspecialcharsbx($val)."\" size=\"7\"> ";

	if ($f_BASE_RATE_TYPE == "P")
	{
		$val = "P";
	}
	else
	{
		if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
			$val = $_REQUEST["FIELDS"][$f_ID]["BASE_RATE_CURRENCY"];
		else
			$val = $f_BASE_RATE_CURRENCY;
	}

	$fieldEdit .= "<select name=\"FIELDS[".$f_ID."][BASE_RATE_CURRENCY]\">";
	foreach ($arCurrencies as $key => $value)
		$fieldEdit .= "<option value=\"".$key."\"".(($key == $val) ? " selected" : "").">".$value."</option>";
	$fieldEdit .= "</select>";

	$row->AddField("RATE", $fieldValue, $fieldEdit);

	if ($affiliatePlanType == "N")
	{
		$fieldValue = intval($f_MIN_PLAN_VALUE)."&nbsp;".GetMessage("SAP1_SHT");
	}
	else
	{
		if (!array_key_exists($f_SITE_ID, $arBaseLangCurrencies))
			$arBaseLangCurrencies[$f_SITE_ID] = CSaleLang::GetLangCurrency($f_SITE_ID);

		$fieldValue = SaleFormatCurrency($f_MIN_PLAN_VALUE, $arBaseLangCurrencies[$f_SITE_ID]);
	}

	if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
		$val = $_REQUEST["FIELDS"][$f_ID]["MIN_PLAN_VALUE"];
	else
		$val = $f_MIN_PLAN_VALUE;

	$fieldEdit = "<input type=\"text\" name=\"FIELDS[".$f_ID."][MIN_PLAN_VALUE]\" value=\"".htmlspecialcharsbx($val)."\" size=\"7\"> ";

	$row->AddField("MIN_PLAN_VALUE", $fieldValue, $fieldEdit);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SAP1_UPDATE"), "ACTION"=>$lAdmin->ActionRedirect("sale_affiliate_plan_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SAP1_DELETE"), "ACTION"=>"if(confirm('".GetMessage("SAP1_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if ($saleModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SAP1_ADD_PLAN"),
			"LINK" => "sale_affiliate_plan_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SAP1_ADD_PLAN_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage("SAP1_RATE_PLANS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SAP1_ACTIVE")
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SAP1_SITE1")?></td>
		<td><?echo CSite::SelectBox("filter_site_id", $filter_site_id, GetMessage("SAP1_ALL")) ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SAP1_ACTIVE1")?></td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex(GetMessage("SAP1_ALL")); ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SAP1_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SAP1_NO")) ?></option>
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
?>
<?echo GetMessage("SAP1_NOTE1")?>
<?
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
