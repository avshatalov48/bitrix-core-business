<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_tax";

$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilter = array();

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();

		if (!CSaleTax::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("ERROR_EDIT_TAX"), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
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
		if ($ID == '')
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
				else
				{
					$DB->Commit();
				}

				break;
		}
	}
	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

global $by, $order;

$dbResultList = CSaleTax::GetList(
	array($by => $order),
	$arFilter
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_tax.php"));
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
$dbLangsList = CSite::GetList();
while ($arLang = $dbLangsList->Fetch())
	$arLangs[$arLang["LID"]] = "[".$arLang["LID"]."]&nbsp;".$arLang["NAME"];

while ($arTax = $dbResultList->NavNext(false))
{
	$editUrl = $selfFolderUrl."sale_tax_edit.php?ID=".$arTax["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($arTax["ID"], $arTax, $editUrl);

	$row->AddField("ID", $arTax["ID"]);
	$row->AddField("TIMESTAMP_X", $arTax["TIMESTAMP_X"]);
	$row->AddSelectField("LID", $arLangs, array());

	$fieldShow = $arTax["NAME"]."<br><small>".$arTax["DESCRIPTION"]."</small><br>";

	if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
	{
		$valName = $_REQUEST["FIELDS"][$arTax["ID"]]["NAME"];
		$valDescr = $_REQUEST["FIELDS"][$arTax["ID"]]["DESCRIPTION"];
	}
	else
	{
		$valName = $arTax["NAME"];
		$valDescr = $arTax["DESCRIPTION"];
	}

	$fieldEdit  = "<input type=\"text\" name=\"FIELDS[".$arTax["ID"]."][NAME]\" value=\"".htmlspecialcharsbx($valName)."\" size=\"30\"><br>";
	$fieldEdit .= "<input type=\"text\" name=\"FIELDS[".$arTax["ID"]."][DESCRIPTION]\" value=\"".htmlspecialcharsbx($valDescr)."\" size=\"30\">";
	$row->AddField("NAME", $fieldShow, $fieldEdit, false);

	$row->AddInputField("CODE");

	$fieldShow = "";
	if (in_array("STAV", $arVisibleColumns))
	{
		$num = 0;
		$dbRes = CSaleTaxRate::GetList(array(), array("TAX_ID" => $arTax["ID"]));
		while ($dbRes->Fetch())
			$num++;

		if ($num > 0)
		{
			$taxRateUrl = $selfFolderUrl."sale_tax_rate.php?lang=".LANGUAGE_ID."&TAX_ID=".$arTax["ID"]."&apply_filter=Y";
			$taxRateUrl = $adminSidePanelHelper->editUrlToPublicPage($taxRateUrl);
			$fieldShow = "<a href=\"".$taxRateUrl."\" title=\"".GetMessage("TAX_RATE_DESCR")."\">".$num."</a>";
		}
		else
		{
			$fieldShow = "0";
		}
	}
	$row->AddField("STAV", $fieldShow);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("TAX_EDIT_DESCR"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_DELETE_DESCR"),
			"ACTION" => "if(confirm('".GetMessage('TAX_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($arTax["ID"], "delete")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(array("delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")));

if ($saleModulePermissions == "W")
{
	$addUrl = $selfFolderUrl."sale_tax_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("STAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("STAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_tax.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("TAX_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$lAdmin->DisplayList();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>