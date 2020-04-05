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

ClearVars("l_");

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_tax_rate";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$listTax = array();
$taxQueryObject = CSaleTax::GetList(array("NAME" => "ASC"), array());
while ($taxData = $taxQueryObject->NavNext(false))
{
	$listTax[$taxData["ID"]] = $taxData["NAME"]." (".$taxData["LID"].")";
}
$listSites = array();
$sitesQueryObject = CSite::GetList($bySite = "sort", $orderSite = "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
	$listSites[$site["LID"]] = "[".$site["LID"]."] ".$site["NAME"];
}
$listPersonType = array();
$personTypeQueryObject = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"));
while ($personType = $personTypeQueryObject->fetch())
{
	$listPersonType[$personType["ID"]] = $personType["NAME"]." (".implode(", ", $personType["LIDS"]).")";
}

ob_start();
$APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
	"ID" => "LOCATION",
	"CODE" => "",
	"INPUT_NAME" => "LOCATION",
	"PROVIDE_LINK_BY" => "id",
	"SHOW_ADMIN_CONTROLS" => "N",
	"SELECT_WHEN_SINGLE" => "N",
	"FILTER_BY_SITE" => "N",
	"SHOW_DEFAULT_LOCATIONS" => "N",
	"SEARCH_BY_PRIMARY" => "Y",
	"INITIALIZE_BY_GLOBAL_EVENT" => "onAdminFilterInited",
	"GLOBAL_EVENT_SCOPE" => "window",
	"UI_FILTER" => true
),false);
$locationInput = ob_get_clean();

$filterFields = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"filterable" => "",
		"quickSearch" => ""
	),
	array(
		"id" => "TAX_ID",
		"name" => GetMessage("SALE_F_TAX"),
		"type" => "list",
		"items" => $listTax,
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "LID",
		"name" => GetMessage("SALE_F_LANG"),
		"type" => "list",
		"items" => $listSites,
		"filterable" => ""
	),
	array(
		"id" => "PERSON_TYPE_ID",
		"name" => GetMessage("SALE_F_PERSON_TYPE"),
		"type" => "list",
		"items" => $listPersonType,
		"filterable" => ""
	),
	array(
		"id" => "LOCATION",
		"name" => GetMessage("SALE_F_LOCATION"),
		"type" => "custom",
		"value" => $locationInput,
		"filterable" => ""
	),
);

$arFilter = array();

$lAdmin->AddFilter($filterFields, $arFilter);

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleTaxRate::GetList(
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

				if (!CSaleTaxRate::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SALE_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CSaleTaxRate::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_EDIT_TAX_RATE"), $ID);
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

$dbResultList = CSaleTaxRate::GetList(array($by => $order), $arFilter);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_tax_rate.php"));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("RATE_ACTIVE"), "sort"=>"ACTIVE", "default"=>true, "align" => "center",),
	array("id"=>"TIMESTAMP_X", "content"=>GetMessage("TAX_TIMESTAMP"), "sort"=>"TIMESTAMP_X", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("TAX_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"PERSON_TYPE_ID", "content"=>GetMessage("RATE_PERSON_TYPE"), "sort"=>"PERSON_TYPE_ID", "default"=>true),
	array("id"=>"VALUE", "content"=>GetMessage("RATE_VALUE"), "sort"=>"", "default"=>true),
	array("id"=>"IS_IN_PRICE", "content"=>GetMessage("RATE_IS_INPRICE"), "sort"=>"IS_IN_PRICE", "default"=>true),
	array("id"=>"APPLY_ORDER", "content"=>GetMessage("RATE_APPLY_ORDER"), "sort"=>"APPLY_ORDER", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arPersonTypeList = array();
$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
while ($arPersonType = $dbPersonType->Fetch())
{
	$arPersonTypeList[$arPersonType["ID"]] = array(
		"ID" => $arPersonType["ID"],
		"NAME" => htmlspecialcharsEx($arPersonType["NAME"]),
		"LID" => implode(", ", $arPersonType["LIDS"])
	);
}

while ($arTaxRate = $dbResultList->NavNext(false))
{
	$editUrl = $selfFolderUrl."sale_tax_rate_edit.php?ID=".$arTaxRate["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($arTaxRate["ID"], $arTaxRate, $editUrl);

	$row->AddField("ID", $arTaxRate["ID"]);

	$row->AddField("ACTIVE", ($arTaxRate["ACTIVE"]=="Y") ? GetMessage("RATE_YES") : GetMessage("RATE_NET"));

	$row->AddField("TIMESTAMP_X", $arTaxRate["TIMESTAMP_X"]);

	$fieldShow = '<a href="'.$editUrl.'" title="'.GetMessage('TAX_EDIT_DESCR').'">'.htmlspecialcharsbx($arTaxRate["NAME"]).'</a> ('.$arTaxRate["LID"].')';
	$row->AddField("NAME", $fieldShow);

	$fieldShow = "";
	if (in_array("PERSON_TYPE_ID", $arVisibleColumns))
	{
		if (IntVal($arTaxRate["PERSON_TYPE_ID"])>0)
		{
			$arPerType = $arPersonTypeList[$arTaxRate["PERSON_TYPE_ID"]];
			$fieldShow .= "[".$arPerType["ID"]."] ".$arPerType["NAME"]." (".htmlspecialcharsEx($arPerType["LID"]).")";
		}
		else
		{
			$fieldShow .= "&nbsp;";
		}
	}
	$row->AddField("PERSON_TYPE_ID", $fieldShow);

	$row->AddField("VALUE", $arTaxRate["VALUE"].(($arTaxRate["IS_PERCENT"]=="Y") ? "%" : " ".$arTaxRate["CURRENCY"]));
	$row->AddField("IS_IN_PRICE", ($arTaxRate["IS_IN_PRICE"]=="Y") ? GetMessage("RATE_YES") : GetMessage("RATE_NET"));
	$row->AddField("APPLY_ORDER", $arTaxRate["APPLY_ORDER"]);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("RATE_EDIT_DESCR"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("RATE_DELETE_DESCR"),
			"ACTION" => "if(confirm('".GetMessage('TAX_RATE_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($arTaxRate["ID"], "delete")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if ($saleModulePermissions == "W")
{
	$addUrl = $selfFolderUrl."sale_tax_rate_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("STRAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("STRAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_tax_rate.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}


$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();
?>

<?echo BeginNote();?>
	<?echo GetMessage("RATE_ORDER_NOTES")?><br>
<?echo EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>