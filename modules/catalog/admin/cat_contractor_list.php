<?
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$publicMode = $adminPage->publicMode;

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_store');

IncludeModuleLangFile(__FILE__);

$bExport = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel');

$typeList = array(
	CONTRACTOR_INDIVIDUAL => GetMessage('CONTRACTOR_INDIVIDUAL'),
	CONTRACTOR_JURIDICAL => GetMessage('CONTRACTOR_JURIDICAL')
);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($ex->GetString());
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "b_catalog_contractor";
$oSort = new CAdminUiSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "PERSON_TYPE",
		"name" => "ID",
		"type" => "list",
		"items" => $typeList,
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "PERSON_NAME",
		"name" => GetMessage("CONTRACTOR_PERSON_NAME"),
		"filterable" => "%",
		"quickSearch" => "%"
	),
	array(
		"id" => "COMPANY",
		"name" => GetMessage("CONTRACTOR_COMPANY"),
		"filterable" => "%"
	),
	array(
		"id" => "PHONE",
		"name" => GetMessage("CONTRACTOR_PHONE"),
		"filterable" => ""
	),
	array(
		"id" => "EMAIL",
		"name" => GetMessage("CONTRACTOR_EMAIL"),
		"filterable" => ""
	),
	array(
		"id" => "INN",
		"name" => GetMessage("CONTRACTOR_INN"),
		"filterable" => ""
	),
	array(
		"id" => "KPP",
		"name" => GetMessage("CONTRACTOR_KPP"),
		"filterable" => "%"
	),
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
		if (!CCatalogContractor::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("ERROR_UPDATING_REC")." (".$arFields["ID"].", ".$arFields["TITLE"].", ".$arFields["SORT"].")", $ID);

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
		$dbResultList = CCatalogContractor::GetList(array(), $arFilter, false, false, array('ID'));
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
				if (!CCatalogContractor::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DELETING_TYPE"), $ID);
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

$arSelect = array(
	"ID",
	"PERSON_TYPE",
	"PERSON_NAME",
	"EMAIL",
	"PHONE",
	"POST_INDEX",
	"COUNTRY",
	"CITY",
	"COMPANY",
	"INN",
	"KPP",
	"ADDRESS",
);

$arNavParams = (
	isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel'
	? false
	: array("nPageSize" => CAdminUiResult::GetNavSize($sTableID))
);
global $by, $order;
if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$dbResultList = CCatalogContractor::GetList(
	array($by => $order),
	$arFilter,
	false,
	$arNavParams,
	$arSelect
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_contractor_list.php"));

$arHeaders = array(
	array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
	array('id' => 'PERSON_TYPE', 'content' => GetMessage('CONTRACTOR_TYPE'), 'sort' => 'PERSON_TYPE', 'default' => true),
	array('id' => 'PERSON_NAME', 'content' => GetMessage('CONTRACTOR_PERSON_TITLE'), 'sort' => 'PERSON_NAME', 'default' => true),
	array("id" => "COMPANY", "content" => GetMessage("CONTRACTOR_COMPANY"),  "sort" => "COMPANY", "default" => true),
	array("id" => "EMAIL", "content" => GetMessage("CONTRACTOR_EMAIL"),  "sort" => "EMAIL", "default" => true),
	array("id" => "PHONE", "content" => GetMessage("CONTRACTOR_PHONE"),  "sort" => "PHONE", "default" => false),
	array("id" => "POST_INDEX", "content" => GetMessage("CONTRACTOR_POST_INDEX"),  "sort" => "POST_INDEX", "default" => false),
	array("id" => "INN", "content" => GetMessage("CONTRACTOR_INN"),  "sort" => "INN", "default" => false),
);
if(trim(GetMessage("CONTRACTOR_KPP")) != '')
	$arHeaders[] = array("id" => "KPP", "content" => GetMessage("CONTRACTOR_KPP"),  "sort" => "KPP", "default" => false);

$arHeaders[] = 	array("id" => "ADDRESS", "content" => GetMessage("CONTRACTOR_ADDRESS"),  "sort" => "ADDRESS", "default" => true);

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
while ($arResultContractor = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($arResultContractor['ID'], $arResultContractor, "cat_contractor_edit.php?ID=".$arResultContractor['ID']."&lang=".LANGUAGE_ID);
	$row->AddField('ID', $arResultContractor['ID']);
	$row->AddViewField('PERSON_TYPE', $typeList[$arResultContractor['PERSON_TYPE']]);
	$row->AddInputField('PERSON_NAME', false);
	$row->AddInputField('COMPANY', false);
	if($bReadOnly)
	{
		$row->AddInputField('EMAIL', false);
		$row->AddInputField('PHONE', false);
		$row->AddInputField('ADDRESS', false);
	}
	else
	{
		$row->AddInputField('EMAIL', array('size' => 30));
		$row->AddInputField('PHONE', array('size' => 25));
		$row->AddInputField('ADDRESS', array('size' => 40));
	}

	$arActions = array();
	$editUrl = $selfFolderUrl."cat_contractor_edit.php?ID=".$arResultContractor['ID']."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("EDIT_CONTRACTOR_ALT"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("DELETE_CONTRACTOR_ALT"),
			"ACTION" => "if(confirm('".GetMessage('DELETE_CONTRACTOR_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arResultContractor['ID'], "delete")
		);
	}

	$row->AddActions($arActions);
}

if(!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

if (!$bReadOnly)
{
	$addUrl = $selfFolderUrl."cat_contractor_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("CONTRACTOR_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("CONTRACTOR_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_contractor_list.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CONTRACTOR_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");