<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

CModule::IncludeModule("catalog");

$accessController = AccessController::getCurrent();
if (
	!$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
	&& !$accessController->check(ActionDictionary::ACTION_MEASURE_EDIT)
)
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bReadOnly = !$accessController->check(ActionDictionary::ACTION_MEASURE_EDIT);

IncludeModuleLangFile(__FILE__);

$bCanAdd = true;

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

$sTableID = "b_catalog_measure";
$oSort = new CAdminUiSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$listOrder = [
	$by => $order,
];
if ($by !== 'ID')
{
	$listOrder['ID'] = 'ASC';
}

$arFilter = array();

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CCatalogMeasure::update($ID, $arFields))
		{
			if($ex = $APPLICATION->GetException())
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

$arID = $lAdmin->GroupAction();
if (!$bReadOnly && !empty($arID) && is_array($arID))
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$arID = Array();
		$dbResultList = CCatalogMeasure::getList(array($_REQUEST["by"] => $_REQUEST["order"]), $arFilter, false, false, array('ID'));
		while($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	$action = $lAdmin->GetAction();
	foreach ($arID as $ID)
	{
		if($ID == '')
			continue;

		switch ($action)
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!CCatalogMeasure::delete($ID))
				{
					$DB->Rollback();

					if($ex = $APPLICATION->GetException())
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
	"CODE",
	"MEASURE_TITLE",
	"SYMBOL_RUS",
	"SYMBOL_INTL",
	"SYMBOL_LETTER_INTL",
	"IS_DEFAULT",
);

$arNavParams = (
	$lAdmin->isExportMode()
		? false
		: ["nPageSize" => CAdminUiResult::GetNavSize($sTableID)]
);

$dbResultList = CCatalogMeasure::getList(
	$listOrder,
	array(),
	false,
	$arNavParams,
	$arSelect
);
$dbResultList = new CCatalogMeasureAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_measure_list.php"));

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "CODE",
		"content" => GetMessage("CAT_MEASURE_CODE_MSGVER_1"),
		"sort" => "CODE",
		"default" => true
	),
	array(
		"id" => "MEASURE_TITLE",
		"content" => GetMessage("CAT_MEASURE_MEASURE_TITLE"),
		"sort" => "MEASURE_TITLE",
		"default" => true
	),
	array(
		"id" => "SYMBOL_RUS",
		"content" => GetMessage("CAT_MEASURE_SYMBOL_RUS"),
		"sort" => "SYMBOL_RUS",
		"default" => true
	),
	array(
		"id" => "SYMBOL_INTL",
		"content" => GetMessage("CAT_MEASURE_SYMBOL_INTL"),
		"sort" => "SYMBOL_INTL",
		"default" => true
	),
	array(
		"id" => "SYMBOL_LETTER_INTL",
		"content" => GetMessage("CAT_MEASURE_SYMBOL_LETTER_INTL"),
		"sort" => "SYMBOL_LETTER_INTL",
		"default" => false
	),
	array(
		"id" => "IS_DEFAULT",
		"content" => GetMessage("CAT_MEASURE_IS_DEFAULT"),
		"sort" => "IS_DEFAULT",
		"default" => true
	),
));

$arSelectFieldsMap = array(
	"ID" => false,
	"CODE" => false,
	"MEASURE_TITLE" => false,
	"SYMBOL_RUS" => false,
	"SYMBOL_INTL" => false,
	"SYMBOL_LETTER_INTL" => false,
	"IS_DEFAULT" => false,
);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if(!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

$arUserList = array();
$arUserID = array();
$strNameFormat = CSite::GetNameFormat();

$arRows = array();

while($arRes = $dbResultList->Fetch())
{
	$arRes['ID'] = (int)$arRes['ID'];

	$editUrl = $selfFolderUrl."cat_measure_edit.php?ID=".$arRes["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arRows[$arRes['ID']] = $row =& $lAdmin->AddRow($arRes['ID'], $arRes, $editUrl);
	$row->AddField("ID", $arRes['ID']);
	if ($arSelectFieldsMap['CODE'])
	{
		$row->AddInputField('CODE', false);
	}
	if($bReadOnly)
	{
		if($arSelectFieldsMap['MEASURE_TITLE'])
			$row->AddInputField("MEASURE_TITLE", false);
		if($arSelectFieldsMap['SYMBOL_RUS'])
			$row->AddInputField("SYMBOL_RUS", false);
		if($arSelectFieldsMap['SYMBOL_INTL'])
			$row->AddInputField("SYMBOL_INTL", false);
		if($arSelectFieldsMap['SYMBOL_LETTER_INTL'])
			$row->AddInputField("SYMBOL_LETTER_INTL", false);
	}
	else
	{
		if($arSelectFieldsMap['MEASURE_TITLE'])
			$row->AddInputField("MEASURE_TITLE", array("size" => 30));
		if($arSelectFieldsMap['SYMBOL_RUS'])
			$row->AddInputField("SYMBOL_RUS", array("size" => 8));
		if($arSelectFieldsMap['SYMBOL_INTL'])
			$row->AddInputField("SYMBOL_INTL", array("size" => 8));
		if($arSelectFieldsMap['SYMBOL_LETTER_INTL'])
			$row->AddInputField("SYMBOL_LETTER_INTL", array("size" => 8));
	}
	if($arSelectFieldsMap['IS_DEFAULT'])
		$row->AddCheckField("IS_DEFAULT", false);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => $bReadOnly ? Loc::getMessage('CAT_MEASURE_VIEW_ALT') : Loc::getMessage("CAT_MEASURE_EDIT_ALT"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if(!$bReadOnly)
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("CAT_MEASURE_DELETE_ALT"),
			"ACTION" => "if(confirm('".GetMessageJS('CAT_MEASURE_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arRes['ID'], "delete")
		);
	}

	$row->AddActions($arActions);
}
unset($row);

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable([
		'edit' => true,
		'delete' => true
	]);
}

if(!$bReadOnly && $bCanAdd)
{
	$addUrl = $selfFolderUrl."cat_measure_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("CAT_MEASURE_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("CAT_MEASURE_ADD_NEW_ALT")
		),
		array(
			"TEXT" => GetMessage("CAT_MEASURE_ADD_NEW_OKEI"),
			"ICON" => "btn_new",
			"LINK" => $addUrl."&OKEI=Y",
			"TITLE" => GetMessage("CAT_MEASURE_ADD_NEW_OKEI_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_measure_list.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CAT_MEASURE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$listParams = [
	'USE_CHECKBOX_LIST_FOR_SETTINGS_POPUP' => \Bitrix\Main\ModuleManager::isModuleInstalled('ui'),
	'ENABLE_FIELDS_SEARCH' => 'Y',
];
$lAdmin->DisplayList($listParams);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
