<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

/// redirect to newer version
if(CSaleLocation::isLocationProEnabled())
	LocalRedirect('/bitrix/admin/sale_location_node_list.php?id=0');

$sTableID = "tbl_sale_location";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_country_name",
	"filter_region_name",
	"filter_city_name",
	"filter_country"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($filter_country_name <> '' && $filter_country_name!="%" && $filter_country_name!="%%")
	$arFilter["COUNTRY"] = Trim($filter_country_name);
if ($filter_region_name <> '' && $filter_region_name!="%" && $filter_region_name!="%%")
	$arFilter["REGION"] = Trim($filter_region_name);
if ($filter_city_name <> '' && $filter_city_name!="%" && $filter_city_name!="%%")
	$arFilter["CITY"] = Trim($filter_city_name);
if (intval($filter_country)>0)
	$arFilter["COUNTRY_ID"] = intval($filter_country);
$arFilter["LID"] = LANGUAGE_ID;

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleLocation::GetList(
				array($by => $order),
				$arFilter,
				false,
				false,
				array("ID")
			);
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

				if (!CSaleLocation::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DELETING"), $ID);
				}
				else
				{
					$DB->Commit();
				}

				break;
		}
	}
}

$dbResultList = CSaleLocation::GetList(
	array($by => $order),
	$arFilter,
	false,
	array("nPageSize"=>CAdminResult::GetNavSize($sTableID))
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"COUNTRY_NAME", "content"=>GetMessage("SALE_COUNTRY"), "sort"=>"COUNTRY_NAME", "default"=>true),
	array("id"=>"REGION_NAME", "content"=>GetMessage("SALE_REGION"), "sort"=>"REGION_NAME", "default"=>true),
	array("id"=>"CITY_NAME", "content"=>GetMessage("SALE_CITY"), "sort"=>"CITY_NAME", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SALE_SORT"), "sort"=>"SORT", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arLocation = $dbResultList->NavNext(true, "f_"))
{
	$editUrl = "sale_location_edit.php?ID=".$f_ID."&lang=" . LANGUAGE_ID . GetFilterParams("filter_");
	$row =& $lAdmin->AddRow($f_ID, $arLocation, $editUrl, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("ID", "<b><a href='".$editUrl."' title='".GetMessage("SALE_EDIT_DESCR")."'>".$f_ID."</a>");
	$row->AddField("COUNTRY_NAME", $f_COUNTRY_NAME_ORIG.(((string)$f_COUNTRY_NAME != "")?" <small>[".$f_COUNTRY_NAME."]</small>":""));
	$row->AddField("REGION_NAME", $f_REGION_NAME_ORIG.((intval($f_REGION_ID)>0) ? " <small>[".$f_REGION_NAME."]</small>" : ""));
	$row->AddField("CITY_NAME", $f_CITY_NAME_ORIG.((intval($f_CITY_ID)>0) ? " <small>[".$f_CITY_NAME."]</small>" : ""));
	$row->AddField("SORT", $f_SORT);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect($editUrl), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SLAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_location_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SLAN_ADD_NEW_ALT")
		),

		array(
			"TEXT" => GetMessage("SLAN_IMPORT"),
			//"ICON" => "btn_settings",
			"LINK" => "sale_location_import.php?lang=".LANG,
			"TITLE" => GetMessage("SLAN_IMPORT_ALT")
		),

	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SALE_F_COUNTRY"),
		GetMessage("SALE_F_REGION"),
		GetMessage("SALE_F_CITY"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_COUNTRY");?>:</td>
		<td>
			<select name="filter_country">
				<option value=""><?echo GetMessage("SALE_ALL")?></option>
				<?
				$db_contList = CSaleLocation::GetCountryList(Array("NAME_LANG"=>"ASC"), Array(), LANG);
				while ($arContList = $db_contList->Fetch())
				{
					?><option value="<?echo $arContList["ID"] ?>"<?if (intval($arContList["ID"])==intval($filter_country)) echo " selected";?>><?echo htmlspecialcharsbx($arContList["NAME"]) ?> [<?echo htmlspecialcharsbx($arContList["NAME_LANG"]) ?>]</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_COUNTRY")?>:</td>
		<td>
			<input type="text" name="filter_country_name" value="<?echo htmlspecialcharsbx($filter_country_name) ?>" size="30"><?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_REGION")?>:</td>
		<td>
			<input type="text" name="filter_region_name" value="<?echo htmlspecialcharsbx($filter_region_name) ?>" size="30"><?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_CITY")?>:</td>
		<td>
			<input type="text" name="filter_city_name" value="<?echo htmlspecialcharsbx($filter_city_name) ?>" size="30"><?=ShowFilterLogicHelp()?>
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