<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

/// redirect to newer version
if(CSaleLocation::isLocationProEnabled())
	LocalRedirect('/bitrix/admin/sale_location_group_list.php');

$sTableID = "tbl_sale_location_group";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_location"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (intval($filter_location)>0) $arFilter["LOCATION"] = intval($filter_location);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CSaleLocationGroup::GetList(
				array($by => $order),
				$arFilter,
				LANG
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

				if (!CSaleLocationGroup::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DELETE"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSaleLocationGroup::GetList(
	array($by => $order),
	$arFilter,
	LANG
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SALE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SALE_SORT"), "sort"=>"SORT", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arCCard);

	$row->AddField("ID", $f_ID);
	$row->AddField("NAME", $f_NAME);
	$row->AddField("SORT", $f_SORT);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("SALE_EDIT_DESCR"),
		"ACTION" => $lAdmin->ActionRedirect("sale_location_group_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_").""),
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_DELETE_TEXT"),
			"ACTION" => "if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
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

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SLGAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_location_group_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SLGAN_ADD_NEW_ALT")
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
	array()
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_LOCATION");?>:</td>
		<td>
			<select name="filter_location">
				<option value=""><?echo GetMessage("SALE_ALL")?></option>
				<?$db_vars = CSaleLocation::GetList(Array("SORT"=>"ASC", "COUNTRY_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), array(), LANG)?>
				<?while ($vars = $db_vars->Fetch()):?>
					<option value="<?echo $vars["ID"]?>"<?if (intval($vars["ID"])==intval($filter_location)) echo " selected"?>><?echo htmlspecialcharsbx($vars["COUNTRY_NAME"]." - ".$vars["CITY_NAME"])?></option>
				<?endwhile;?>
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