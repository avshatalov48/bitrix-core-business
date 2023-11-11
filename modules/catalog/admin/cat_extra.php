<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

Loader::includeModule('catalog');
use \Bitrix\Catalog\Access\AccessController;
use \Bitrix\Catalog\Access\ActionDictionary;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$accessController = AccessController::getCurrent();
if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_PRICE_EDIT)))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bReadOnly = !$accessController->check(ActionDictionary::ACTION_PRODUCT_PRICE_EXTRA_EDIT);

IncludeModuleLangFile(__FILE__);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "tbl_catalog_extra";

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

$filterFields = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"type" => "number",
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "NAME",
		"name" => GetMessage("EXTRA_NAME"),
		"filterable" => "~",
		"quickSearch" => "%"
	),
	array(
		"id" => "PERCENTAGE",
		"name" => GetMessage("EXTRA_PERCENTAGE"),
		"type" => "number",
		"filterable" => ""
	),
);

$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)($ID);

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CExtra::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("CEN_ERROR_UPDATE"), $ID);

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
		$arID = array();
		$dbResultList = CExtra::GetList(array(), $arFilter, false, false, array('ID'));
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	$action = $lAdmin->GetAction();
	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($action)
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!CExtra::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("EXTRA_DELETE_ERROR"), $ID);
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

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("EXTRA_NAME"),
		"sort" => "NAME",
		"default" => true
	),
	array(
		"id" => "PERCENTAGE",
		"content" => GetMessage('EXTRA_PERCENTAGE'),
		"sort" => "PERCENTAGE",
		"default" => true
	),
);

if (!$bReadOnly)
{
	$arHeaders[] = array(
		"id" => "RECALCULATE",
		"content" => GetMessage("EXTRA_RECALCULATE"),
		"default" => true
	);
}

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$dbResultList = CExtra::GetList(
	$listOrder,
	$arFilter,
	false,
	false
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_extra.php"));

while ($arExtra = $dbResultList->NavNext(false))
{
	$editUrl = $selfFolderUrl."cat_extra_edit.php?ID=".$arExtra['ID']."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($arExtra["ID"], $arExtra, $editUrl);

	$row->AddField("ID", $arExtra["ID"]);

	if ($bReadOnly)
	{
		$row->AddViewField("NAME", $arExtra["NAME"]);
		$row->AddViewField("PERCENTAGE", $arExtra["PERCENTAGE"]);
	}
	else
	{
		$row->AddInputField("NAME", array("size" => "35"));
		$row->AddInputField("PERCENTAGE", array("size" => "10"));
		$row->AddCheckField("RECALCULATE");
		$row->AddViewField("RECALCULATE", '');
	}

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => $bReadOnly ? GetMessage('CEN_VIEW_ALT') : GetMessage('CEN_UPDATE_ALT'),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("CEN_DELETE_ALT"),
			"ACTION"=>"if(confirm('".GetMessage('CEN_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arExtra["ID"], "delete")
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

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable([
		'edit' => true,
		'delete' => true
	]);
}

if (!$bReadOnly)
{
	$addUrl = $selfFolderUrl."cat_extra_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("CEN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("CEN_ADD_NEW_ALT")
		),
	);
	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_extra.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("EXTRA_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

echo BeginNote();
echo GetMessage("EXTRA_NOTES");
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");