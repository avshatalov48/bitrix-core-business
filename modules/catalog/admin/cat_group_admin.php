<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

use Bitrix\Main\Loader,
	Bitrix\Catalog\Access\ActionDictionary,
	Bitrix\Catalog\Access\AccessController,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

Loader::includeModule('catalog');

$accessController = AccessController::getCurrent();
if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bReadOnly = !$accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_catalog_group";

$oSort = new CAdminUiSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CCatalogGroup::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("ERROR_UPDATING_REC")." (".$arFields["ID"].", ".$arFields["NAME"].", ".$arFields["SORT"].")", $ID);

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
		$arID = Array();
		$dbResultList = CCatalogGroup::GetListEx(array(), $arFilter, false, false, array('ID'));
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
				if (!CCatalogGroup::Delete($ID))
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
			case 'setbase':
				$DB->StartTransaction();
				if (!CCatalogGroup::Update($ID, ['BASE' => 'Y']))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_UPDATING_REC"), $ID);
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

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("CODE"),
		"sort" => "NAME",
		"default" => true
	),
	array(
		"id" => "NAME_LID",
		"content" => GetMessage('NAME'),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("SORT"),
		"sort" => "SORT",
		"default" => true
	),
	array(
		"id" => "BASE",
		"content" => GetMessage("BASE"),
		"sort" => "BASE",
		"default" => true
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("BT_CAT_GROUP_ADM_TITLE_XML_ID"),
		"sort" => "XML_ID",
		"default" => false
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage('BT_CAT_GROUP_ADM_TITLE_MODIFIED_BY'),
		"sort" => "MODIFIED_BY",
		"default" => true
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage('BT_CAT_GROUP_ADM_TITLE_TIMESTAMP_X'),
		"sort" => "TIMESTAMP_X",
		"default" => true
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage('BT_CAT_GROUP_ADM_TITLE_CREATED_BY'),
		"sort" => "CREATED_BY",
		"default" => false
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage('BT_CAT_GROUP_ADM_TITLE_DATE_CREATE'),
		"sort" => "DATE_CREATE",
		"default" => false
	),
));

$arSelectFieldsMap = array(
	"ID" => false,
	"NAME" => false,
	"NAME_LID" => false,
	"SORT" => false,
	"BASE" => false,
	"XML_ID" => false,
	"MODIFIED_BY" => false,
	"TIMESTAMP_X" => false,
	"CREATED_BY" => false,
	"DATE_CREATE" => false,
);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();

$arSelectFields = array_values($arSelectFields);
$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

$mxKey = array_search('NAME_LID', $arSelectFields);
if (false !== $mxKey)
{
	unset($arSelectFields[$mxKey]);
	$arSelectFields = array_values($arSelectFields);
}

$arLangList = array();
$arLangDefList = array();
if ($arSelectFieldsMap['NAME_LID'])
{
	$rsPriceLangs = CLangAdmin::GetList();
	while ($arPriceLang = $rsPriceLangs->Fetch())
	{
		$arLangList[$arPriceLang['LID']] = true;
		$arLangDefList[$arPriceLang['LID']] = str_replace('#LANG#', htmlspecialcharsbx($arPriceLang['NAME']), GetMessage('BT_CAT_GROUP_ADM_LANG_MESS'));
	}
	unset($arPriceLang, $rsPriceLangs);
}

if (!in_array('ID', $arSelectFields))
{
	$arSelectFields[] = 'ID';
}
if (!in_array('BASE', $arSelectFields))
{
	$arSelectFields[] = 'BASE';
}

$dbResultList = CCatalogGroup::GetList(
	array($by => $order),
	array(),
	false,
	false,
	$arSelectFields
);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_group_admin.php"));

$arUserList = array();
$arUserID = array();
$strNameFormat = CSite::GetNameFormat(true);

$arRows = array();

while ($arRes = $dbResultList->Fetch())
{
	$arRes['ID'] = (int)$arRes['ID'];
	if ($arSelectFieldsMap['CREATED_BY'])
	{
		$arRes['CREATED_BY'] = (int)$arRes['CREATED_BY'];
		if (0 < $arRes['CREATED_BY'])
			$arUserID[$arRes['CREATED_BY']] = true;
	}
	if ($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arRes['MODIFIED_BY'] = (int)$arRes['MODIFIED_BY'];
		if (0 < $arRes['MODIFIED_BY'])
			$arUserID[$arRes['MODIFIED_BY']] = true;
	}

	$editUrl = $selfFolderUrl."cat_group_edit.php?ID=".$arRes["ID"]."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arRows[$arRes['ID']] = $row = &$lAdmin->AddRow($arRes['ID'], $arRes, $editUrl);
	$row->AddViewField("ID", '<a href="'.$editUrl.'">'.$arRes["ID"].'</a>');

	if (!$bReadOnly)
	{
		if ($arSelectFieldsMap['NAME'])
			$row->AddInputField("NAME", array("size" => 30));
		if ($arSelectFieldsMap['SORT'])
			$row->AddInputField("SORT", array("size" => 4));
		if ($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", array("size" => 30));
	}
	else
	{
		if ($arSelectFieldsMap['NAME'])
			$row->AddViewField("NAME", '<a href="'.$editUrl.'">'.htmlspecialcharsbx($arRes['NAME']).'</a>');
		if ($arSelectFieldsMap['SORT'])
			$row->AddInputField('SORT', false);
		if ($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", false);
	}

	if ($arSelectFieldsMap['BASE'])
	{
		$row->AddViewField("BASE", ("Y" == $arRes['BASE'] ? GetMessage("BASE_YES") : GetMessage("BASE_NO")));
	}

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => $bReadOnly ? GetMessage('VIEW') : GetMessage('EDIT_STATUS_ALT'),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		if ('Y' != $arRes['BASE'])
		{
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("DELETE_STATUS_ALT"),
				"ACTION" => "if(confirm('".GetMessageJS('DELETE_STATUS_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arRes['ID'], "delete")
			);
			$arActions[] = [
				'ICON' => 'edit',
				'TEXT' => GetMessage('BT_CAT_GROUP_ADM_ACTION_SET_BASE_PRICE'),
				'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'setbase'),
				'ONCLICK' => '',
			];
		}
	}

	$row->AddActions($arActions);
}

if ($arSelectFieldsMap['NAME_LID'])
{
	$arGroupIDS = array_keys($arRows);
	if (!empty($arGroupIDS))
	{
		$arLangResult = array();
		$arLangResult = array_fill_keys($arGroupIDS, $arLangDefList);
		$rsLangs = CCatalogGroup::GetLangList(array("CATALOG_GROUP_ID" => $arGroupIDS));
		while ($arLang = $rsLangs->Fetch())
		{
			$arLang['CATALOG_GROUP_ID'] = (int)$arLang['CATALOG_GROUP_ID'];
			if (isset($arLangList[$arLang['LID']]))
			{
				$arLangResult[$arLang['CATALOG_GROUP_ID']][$arLang['LID']] = str_replace('#VALUE#', htmlspecialcharsbx($arLang["NAME"]), $arLangResult[$arLang['CATALOG_GROUP_ID']][$arLang['LID']]);
			}
		}

		foreach ($arGroupIDS as &$intGroupID)
		{
			$strLang = str_replace('#VALUE#', '', implode('<br>', $arLangResult[$intGroupID]));
			$arRows[$intGroupID]->AddViewField("NAME_LID", $strLang);
		}
		if (isset($intGroupID))
			unset($intGroupID);
	}
}

if ($arSelectFieldsMap['CREATED_BY'] || $arSelectFieldsMap['MODIFIED_BY'])
{
	if (!empty($arUserID))
	{
		$rsUsers = CUser::GetList(
			'ID',
			'ASC',
			array('ID' => implode(' | ', array_keys($arUserID))),
			array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
		);
		while ($arOneUser = $rsUsers->Fetch())
		{
			$arOneUser['ID'] = (int)$arOneUser['ID'];
			$userEdit = $selfFolderUrl."user_edit.php?lang=".LANGUAGE_ID."&ID=".$arOneUser["ID"];
			if ($publicMode)
				$arUserList[$arOneUser['ID']] = CUser::FormatName($strNameFormat, $arOneUser);
			else
				$arUserList[$arOneUser['ID']] = '<a href="'.$userEdit.'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
		}
	}

	foreach ($arRows as &$row)
	{
		if ($arSelectFieldsMap['CREATED_BY'])
		{
			$strCreatedBy = '';
			if (0 < $row->arRes['CREATED_BY'] && isset($arUserList[$row->arRes['CREATED_BY']]))
			{
				$strCreatedBy = $arUserList[$row->arRes['CREATED_BY']];
			}
			$row->AddViewField("CREATED_BY", $strCreatedBy);
		}
		if ($arSelectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if (0 < $row->arRes['MODIFIED_BY'] && isset($arUserList[$row->arRes['MODIFIED_BY']]))
			{
				$strModifiedBy = $arUserList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if (isset($row))
		unset($row);
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
	$actions = [];
	if (!Catalog\Config\State::isExceededPriceTypeLimit())
	{
		$actions['edit'] = true;
	}
	$actions['delete'] = true;
	$lAdmin->AddGroupActionTable($actions);
	unset($actions);
}

$aContext = array();
if (!$bReadOnly)
{
	if (Catalog\Config\State::isAllowedNewPriceType())
	{
		$addUrl = $selfFolderUrl."cat_group_edit.php?lang=".LANGUAGE_ID;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		$aContext[] = [
			"TEXT" => GetMessage("CGAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => GetMessage("CGAN_ADD_NEW_ALT")
		];
	}
	else
	{
		$helpLink = Catalog\Config\Feature::getMultiPriceTypesHelpLink();
		if (!empty($helpLink))
		{
			$aContext[] = [
				'TEXT' => GetMessage('CGAN_ADD_NEW'),
				'ICON' => 'btn_lock',
				$helpLink['TYPE'] => $helpLink['LINK'],
				'TITLE' => GetMessage('CGAN_ADD_NEW_ALT')
			];
		}
		unset($helpLink);
	}
}
$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_group_admin.php"));
$lAdmin->AddAdminContextMenu($aContext);
unset($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("GROUP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");