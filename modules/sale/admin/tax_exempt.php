<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_tax_exempt";

$oSort = new CAdminSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilter = array();

global $by, $order;

$dbResultList = CGroup::GetList($by, $order, array());

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_tax_exempt.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("TAX_ID"), "sort"=>"id", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("TAX_TIMESTAMP"), "sort"=>"timestamp_x", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("EXEMPT_NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("EXEMPT_DESCR"), "sort"=>"description", "default"=>true),
	array("id"=>"COUNT", "content"=>GetMessage("EXEMPT_COUNT"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();


while ($arGroup = $dbResultList->NavNext(false))
{
	$editUrl = $selfFolderUrl."sale_tax_exempt_edit.php?ID=".$arGroup["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($arGroup["ID"], $arGroup, $editUrl);

	$row->AddField("ID", $arGroup["ID"]);
	$row->AddField("TIMESTAMP_X", $arGroup["TIMESTAMP_X"]);
	$textForName = "<a href=\"".$selfFolderUrl."group_admin.php?find_id=".$arGroup["ID"]."&lang=".LANGUAGE_ID.
		"&set_filter=Y\">".$arGroup["NAME"]."</a>";
	if ($publicMode)
	{
		$textForName = $arGroup["NAME"];
	}
	$row->AddField("NAME", $textForName, false, false);
	$row->AddField("DESCRIPTION", $arGroup["DESCRIPTION"], false, false);

	$fieldShow = "";
	if (in_array("COUNT", $arVisibleColumns))
	{
		$dbRes = CSaleTax::GetExemptList(Array("GROUP_ID" => $arGroup["ID"]));
		while ($arRes = $dbRes->Fetch())
		{
			if($arTax = CSaleTax::GetByID($arRes["TAX_ID"]))
			{
				if (strlen($fieldShow) > 0)
					$fieldShow .= ", ";

				$fieldShow .= "<a href=\"/bitrix/admin/sale_tax_edit.php?ID=".$arRes["TAX_ID"]."&lang=".
					LANGUAGE_ID."\">".htmlspecialcharsbx($arTax["NAME"])."</a>";
			}
		}
	}
	if (strlen($fieldShow) <= 0)
		$fieldShow = "&nbsp;";
	$row->AddField("COUNT", $fieldShow);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("EXEMPT_EDIT_DESCR"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	$row->AddActions($arActions);
}

$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_tax_exempt.php"));
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