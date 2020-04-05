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

$sTableID = "tbl_sale_tax";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CSaleTax::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("ERROR_EDIT_TAX"), $ID);

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
		$dbResultList = CSaleTax::GetList(
				array($by => $order),
				$arFilter
			);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleTax::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DEL_TAX"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSaleTax::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_TAX_LIST")));
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("TAX_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("TAX_TIMESTAMP"), "sort"=>"TIMESTAMP_X", "default"=>true),
	array("id"=>"LID", "content"=>GetMessage("TAX_LID"), "sort"=>"LID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("TAX_NAME")." / ".GetMessage("TAX_DESCRIPTION"), "sort"=>"", "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage("TAX_FCODE"), "sort"=>"CODE", "default"=>true),
	array("id"=>"STAV", "content"=>GetMessage("SALE_TAX_RATE"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arLangs = array();
$dbLangsList = CSite::GetList(($b = "sort"), ($o = "asc"));
while ($arLang = $dbLangsList->Fetch())
	$arLangs[$arLang["LID"]] = "[".$arLang["LID"]."]&nbsp;".$arLang["NAME"];

while ($arTax = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arTax);

	$row->AddField("ID", $f_ID);
	$row->AddField("TIMESTAMP_X", $f_TIMESTAMP_X);
	$row->AddSelectField("LID", $arLangs, array());

	$fieldShow = $f_NAME."<br><small>".$f_DESCRIPTION."</small><br>";

	if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
	{
		$valName = $_REQUEST["FIELDS"][$f_ID]["NAME"];
		$valDescr = $_REQUEST["FIELDS"][$f_ID]["DESCRIPTION"];
	}
	else
	{
		$valName = $f_NAME;
		$valDescr = $f_DESCRIPTION;
	}

	$fieldEdit  = "<input type=\"text\" name=\"FIELDS[".$f_ID."][NAME]\" value=\"".htmlspecialcharsbx($valName)."\" size=\"30\"><br>";
	$fieldEdit .= "<input type=\"text\" name=\"FIELDS[".$f_ID."][DESCRIPTION]\" value=\"".htmlspecialcharsbx($valDescr)."\" size=\"30\">";
	$row->AddField("NAME", $fieldShow, $fieldEdit);

	$row->AddInputField("CODE");

	$fieldShow = "";
	if (in_array("STAV", $arVisibleColumns))
	{
		$num = 0;
		$dbRes = CSaleTaxRate::GetList(array(), array("TAX_ID" => $f_ID));
		while ($dbRes->Fetch())
			$num++;

		if ($num > 0)
			$fieldShow = "<a href=\"sale_tax_rate.php?lang=".LANG."&filter_tax_id=".$f_ID."&set_filter=Y\" title=\"".GetMessage("TAX_RATE_DESCR")."\">".$num."</a>";
		else
			$fieldShow = "0";
	}
	$row->AddField("STAV", $fieldShow);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("TAX_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_tax_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('TAX_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
			"TEXT" => GetMessage("STAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_tax_edit.php?lang=".LANG,
			"TITLE" => GetMessage("STAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("TAX_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>