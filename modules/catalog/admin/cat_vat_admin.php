<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_vat')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_vat');

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_catalog_vat";

$oSort = new CAdminUiSorting($sTableID, "C_SORT", "ASC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("CVAT_FILTER_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("CVAT_YES"),
			"N" => GetMessage("CVAT_NO")
		),
		"filterable" => ""
	),
	array(
		"id" => "NAME",
		"name" => GetMessage("CVAT_FILTER_NAME"),
		"filterable" => "%",
		"quickSearch" => "%"
	),
	array(
		"id" => "RATE",
		"name" => GetMessage("CVAT_FILTER_RATE"),
		"filterable" => ""
	)
);

$arFilter = array();

$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CCatalogVat::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_VAT")), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogVat::GetListEx(
			array($by => $order),
			$arFilter,
			false,
			false,
			array('ID')
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
				$DB->StartTransaction();
				if (!CCatalogVat::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_DELETE_VAT")), $ID);
				}
				else
				{
					$DB->Commit();
				}
				break;
			case "activate":
			case "deactivate":
				$arFields = array(
					"ACTIVE" => (($_REQUEST['action'] == "activate") ? "Y" : "N")
				);
				if (!CCatalogVat::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_VAT")), $ID);
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

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"C_SORT", "content"=>GetMessage("CVAT_SORT"), "sort"=>"C_SORT", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("CVAT_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("CVAT_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"RATE", "content"=>GetMessage("CVAT_RATE"), "sort"=>"RATE", "default"=>true),
));

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if (!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$arSelectFields = array_values($arSelectFields);

$arNavParams = (isset($_REQUEST["mode"]) && 'excel' == $_REQUEST["mode"]
	? false
	: array("nPageSize" => CAdminUiResult::GetNavSize($sTableID))
);

global $by, $order;

$dbResultList = CCatalogVat::GetListEx(
	array($by => $order),
	$arFilter,
	false,
	$arNavParams,
	$arSelectFields
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_vat_admin.php"));

while ($arVAT = $dbResultList->Fetch())
{
	$editUrl = $selfFolderUrl."cat_vat_edit.php?ID=".$arVAT["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arVAT['ID'] = (int)$arVAT['ID'];
	$row =& $lAdmin->AddRow($arVAT['ID'], $arVAT, $editUrl);

	$row->AddField("ID", $arVAT['ID']);

	if ($bReadOnly)
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddInputField("NAME", false);
		$row->AddViewField("C_SORT", false);
	}
	else
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", array("size" => 30));
		$row->AddInputField("C_SORT", array("size" => 5));
		$row->AddInputField("RATE", array("size" => 5));
	}

	$row->AddViewField("RATE", doubleval($arVAT['RATE'])." %");

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("CVAT_EDIT_ALT"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("CVAT_DELETE_ALT"),
			"ACTION" => "if(confirm('".GetMessageJS('CVAT_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arVAT['ID'], "delete")
		);
	}

	$row->AddActions($arActions);
}

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable([
		'edit' => true,
		'delete' => true,
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")
	]);
}

if (!$bReadOnly)
{
	$addUrl = $selfFolderUrl."cat_vat_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("CVAT_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("CVAT_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_vat_admin.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CVAT_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");