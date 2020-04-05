<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");


$sTableID = "tbl_sale_tax_exempt";


$oSort = new CAdminSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminList($sTableID, $oSort);


$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

$dbResultList = CGroup::GetList($by, $order, array());

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();


$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("TAX_NAV")));


$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("TAX_ID"), "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("TAX_TIMESTAMP"), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("EXEMPT_NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("EXEMPT_DESCR"), "sort"=>"description", "default"=>true),
	array("id"=>"COUNT", "content"=>GetMessage("EXEMPT_COUNT"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();


while ($arGroup = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arGroup);

	$row->AddField("ID", $f_ID);
	$row->AddField("TIMESTAMP_X", $f_TIMESTAMP_X);
	$row->AddField("NAME", "<a href=\"group_admin.php?find_id=".$f_ID."&lang=".LANG."&set_filter=Y\">".$f_NAME."</a>");
	$row->AddField("DESCRIPTION", $f_DESCRIPTION);

	$fieldShow = "";
	if (in_array("COUNT", $arVisibleColumns))
	{
		$dbRes = CSaleTax::GetExemptList(Array("GROUP_ID" => $f_ID));
		while ($arRes = $dbRes->Fetch())
		{
			if($arTax = CSaleTax::GetByID($arRes["TAX_ID"]))
			{
				if (strlen($fieldShow) > 0)
					$fieldShow .= ", ";

				$fieldShow .= "<a href=\"sale_tax_edit.php?ID=".$arRes["TAX_ID"]."&lang=".LANG."\">".htmlspecialcharsbx($arTax["NAME"])."</a>";
			}
		}
	}
	if (strlen($fieldShow) <= 0)
		$fieldShow = "&nbsp;";
	$row->AddField("COUNT", $fieldShow);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("EXEMPT_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_tax_exempt_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);

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

$lAdmin->AddAdminContextMenu();

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("TAX_EXEMPT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>