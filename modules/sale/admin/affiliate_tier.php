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

$sTableID = "tbl_sale_affiliate_tier";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($filter_site_id != "NOT_REF" && $filter_site_id <> '')
	$arFilter["SITE_ID"] = $filter_site_id;
else
	Unset($arFilter["SITE_ID"]);

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CSaleAffiliateTier::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SAT1_ERROR_UPDATE"), $ID);

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
		$dbResultList = CSaleAffiliateTier::GetList(array(), $arFilter, false, false, array("ID"));
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

				if (!CSaleAffiliateTier::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SAT1_ERROR_DELETE"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSaleAffiliateTier::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SITE_ID", "RATE1", "RATE2", "RATE3", "RATE4", "RATE5")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SAT1_AFF")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage("SAT1_SITE"), "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"RATE1", "content"=>GetMessage("SAT1_RATE1"), "sort"=>"RATE1", "default"=>true),
	array("id"=>"RATE2", "content"=>GetMessage("SAT1_RATE2"), "sort"=>"RATE2", "default"=>true),
	array("id"=>"RATE3", "content"=>GetMessage("SAT1_RATE3"), "sort"=>"RATE3", "default"=>true),
	array("id"=>"RATE4", "content"=>GetMessage("SAT1_RATE4"), "sort"=>"RATE4", "default"=>true),
	array("id"=>"RATE5", "content"=>GetMessage("SAT1_RATE5"), "sort"=>"RATE5", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSites = array();
$dbSiteList = CSite::GetList(($b = "sort"), ($o = "asc"));
while ($arSite = $dbSiteList->Fetch())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."]&nbsp;".$arSite["NAME"];

while ($arAffiliateTier = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arAffiliateTier, "sale_affiliate_tier_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"), GetMessage("SAT1_UPDATE_TIER"));

	$row->AddField("ID", $f_ID);
	$row->AddSelectField("SITE_ID", $arSites, array());
	$row->AddInputField("RATE1", array("size" => "10"));
	$row->AddInputField("RATE2", array("size" => "10"));
	$row->AddInputField("RATE3", array("size" => "10"));
	$row->AddInputField("RATE4", array("size" => "10"));
	$row->AddInputField("RATE5", array("size" => "10"));

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SAT1_UPDATE"), "ACTION"=>$lAdmin->ActionRedirect("sale_affiliate_tier_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SAT1_DELETE"), "ACTION"=>"if(confirm('".GetMessage("SAT1_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
	)
);

if ($saleModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SAT1_ADD"),
			"LINK" => "sale_affiliate_tier_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SAT1_ADD_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage("SAT1_TIERS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SAT1_SITE1")?></td>
		<td><?echo CSite::SelectBox("filter_site_id", $filter_site_id, GetMessage("SAT1_ALL")) ?></td>
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
<?echo GetMessage("SAT1_NOTE1")?><br><br>
<?echo GetMessage("SAT1_NOTE2")?><br><br>
<?echo GetMessage("SAT1_NOTE3")?>
<?
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>