<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;

Loc::loadMessages(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

Loader::includeModule('catalog');
if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW))
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

if (
	!$publicMode
	&& Loader::includeModule('sale')
	&& Catalog\v2\Contractor\Provider\Manager::isActiveProviderExists()
)
{
	$APPLICATION->SetTitle(Loc::getMessage("CAT_DOCS_MSGVER_1"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", "");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "b_catalog_store_docs";

$oSort = new CAdminUiSorting($sTableID, "DATE_MODIFY", "DESC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$errorMessage = "";

$ID = (int)($_REQUEST['ID'] ?? 0);

$userId = $USER->GetID();

/** For a given contractor ID, issues generated title.
 * @param $contractorId
 * @return int
 */
function getContractorTitle($contractorId)
{
	static $dbContractors = '';
	static $arContractors = array();
	$contractorId = $contractorTitle = intval($contractorId);

	if($dbContractors === '')
	{
		$dbContractors = CCatalogContractor::GetList(array());
		while($arContractor = $dbContractors -> Fetch())
			$arContractors[] = $arContractor;
	}

	foreach($arContractors as $arContractor)
	{
		if($arContractor["ID"] == $contractorId)
		{
			$contractorTitle = ($arContractor["PERSON_TYPE"] == CONTRACTOR_INDIVIDUAL) ? $arContractor["PERSON_NAME"] : $arContractor["COMPANY"]." (".$arContractor["PERSON_NAME"].")";
		}
	}
	return $contractorTitle;
}

/** For a given site ID, issues generated site title.
 * @param string|null $siteId
 * @return string
 */

function getSiteTitle(?string $siteId): string
{
	static $arSitesShop = null;

	$siteId = (string)$siteId;
	$siteTitle = $siteId;

	if ($arSitesShop === null)
	{
		$arSitesShop = [];
		$rsSites = CSite::GetList("id", "asc", ["ACTIVE" => "Y"]);
		while($arSite = $rsSites->GetNext())
		{
			$arSitesShop[] = [
				"ID" => $arSite["ID"],
				"NAME" => $arSite["NAME"],
			];
		}
		unset($rsSites);
	}

	foreach($arSitesShop as $arSite)
	{
		if($arSite["ID"] === $siteId)
		{
			$siteTitle = $arSite["NAME"]." (".$arSite["ID"].")";
		}
	}

	return $siteTitle;
}

$arSiteMenu = array();

$listDocType = Catalog\StoreDocumentTable::getTypeList(true);
foreach ($listDocType as $type => $title)
{
	$addUrl = $selfFolderUrl."cat_store_document_edit.php?lang=".LANGUAGE_ID."&DOCUMENT_TYPE=".$type;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	if ($publicMode)
	{
		$action = "BX.adminSidePanel.onOpenPage('".$addUrl."');";
	}
	else
	{
		$action = "window.location = '".$addUrl."';";
	}
	$arSiteMenu[] = array(
		"TEXT" => $title,
		"ACTION" => $action
	);
}

$aContext = array(
	array(
		"TEXT" => Loc::getMessage("CAT_DOC_ADD_MSGVER_1"),
		"ICON" => "btn_new",
		"TITLE" =>  Loc::getMessage("CAT_DOC_ADD_TITLE_MSGVER_1"),
		"DISABLE" => true,
		"MENU" => $arSiteMenu
	),
);

$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_store_document_list.php"));
$lAdmin->AddAdminContextMenu($aContext);

$listSite = array();
$sitesQueryObject = CSite::getList("sort", "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
	$listSite[$site["LID"]] = "[".$site["LID"]."] ".$site["NAME"];
}
$listContractors = array();
$dbContractors = CCatalogContractor::getList(array());
while($arContractorRes = $dbContractors->fetch())
{
	$listContractors[$arContractorRes["ID"]] = getContractorTitle($arContractorRes["ID"]);
}

$statusList = Catalog\StoreDocumentTable::getStatusList();
$filterFields = [
	[
		"id" => "ID",
		"name" => "ID",
		"filterable" => "",
		"quickSearch" => ""
	],
	[
		"id" => "SITE_ID",
		"name" => Loc::getMessage("CAT_DOC_SITE_ID"),
		"type" => "list",
		"items" => $listSite,
		"filterable" => "",
		"default" => true
	],
	[
		"id" => "DOC_TYPE",
		"name" => Loc::getMessage("CAT_DOC_TYPE_MSGVER_1"),
		"type" => "list",
		"items" => $listDocType,
		"filterable" => ""
	],
	[
		"id" => "DATE_DOCUMENT",
		"name" => Loc::getMessage("CAT_DOC_DATE"),
		"type" => "date",
		"filterable" => ""
	],
	[
		"id" => "CONTRACTOR_ID",
		"name" => Loc::getMessage("CAT_DOC_CONTRACTOR"),
		"type" => "list",
		"items" => $listContractors,
		"filterable" => ""
	],
	[
		"id" => "STATUS",
		"name" => Loc::getMessage("CAT_DOC_STATUS"),
		"type" => "list",
		"items" => Catalog\StoreDocumentTable::getStatusList(),
		"filterable" => "",
	],
	[
		"id" => "PRODUCT",
		"name" => Loc::getMessage('CAT_DOC_PRODUCT'),
		"type" => "custom_entity",
		"selector" => ["type" => "product"],
	],
];

$arFilter = [];

$lAdmin->AddFilter($filterFields, $arFilter);
if (isset($arFilter['STATUS']))
{
	$statusFilter = Catalog\StoreDocumentTable::getOrmFilterByStatus($arFilter['STATUS']);
	unset($arFilter['STATUS']);
	if (!empty($statusFilter))
	{
		$arFilter = array_merge(
			$arFilter,
			$statusFilter
		);
	}
	unset($statusFilter);
}

$docsOrder = match ($by)
{
	'STATUS' => [
		'STATUS' => $order,
		'WAS_CANCELLED' => $order,
		'ID' => 'DESC',
	],
	default => [
		$by => $order,
		'ID' => 'DESC',
	],
};

$arID = $lAdmin->GroupAction();
if (!empty($arID) && is_array($arID))
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$arID = array();
		$filteredProduct = 0;
		if (isset($arFilter['PRODUCT']))
		{
			$filteredProduct = (int)$arFilter['PRODUCT'];
			unset($arFilter['PRODUCT']);
		}
		$query = Catalog\StoreDocumentTable::query()
			->setFilter($arFilter)
			->setSelect(['ID']);
		if ($filteredProduct > 0)
		{
			$query->withProduct($filteredProduct);
		}
		while($arResult = $query->fetch())
		{
			$arID[] = $arResult['ID'];
		}
	}

	$action = $lAdmin->GetAction();
	$blockedList = array();
	$arID = array_filter($arID);
	if (!empty($arID))
	{
		if (
			$action === 'delete'
			|| $action === 'conduct'
			|| $action === 'cancellation'
		)
		{
			$filteredID = array();
			$subFilter = array('=ID' => $arID, 'STATUS' => ($_REQUEST['action'] == 'cancellation' ? 'Y' : 'N'));
			$docsIterator = CCatalogDocs::getList(array(), $subFilter, false, false, array('ID'));
			while ($oneDoc = $docsIterator->Fetch())
			{
				$key = array_search($oneDoc['ID'], $arID);
				if ($key !== false)
					unset($arID[$key]);
				$filteredID[] = (int)$oneDoc['ID'];
			}
			if (!empty($arID))
				$blockedList = $arID;
			$arID = $filteredID;
		}
	}

	if (!empty($arID))
	{
		foreach($arID as $ID)
		{
			switch($action)
			{
				case "delete":
					@set_time_limit(0);
					$DB->StartTransaction();
					if (!CCatalogDocs::delete($ID))
					{
						$DB->Rollback();

						if($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError($ex->GetString(), $ID);
						else
							$lAdmin->AddGroupError(Loc::getMessage("ERROR_DELETING_TYPE"), $ID);
					}
					else
					{
						$DB->Commit();
					}
					break;
				case "conduct":
					$DB->StartTransaction();
					$result = CCatalogDocs::conductDocument($ID, $userId);
					if($result)
						$DB->Commit();
					else
						$DB->Rollback();

					if($ex = $APPLICATION->GetException())
					{
						$strError = $ex->GetString();
						if(!empty($result) && is_array($result))
						{
							$strError .= CCatalogStoreControlUtil::showErrorProduct($result);
						}
						$lAdmin->AddGroupError($strError, $ID);
					}
					break;
				case "cancellation":
					$DB->StartTransaction();
					$result = CCatalogDocs::cancellationDocument($ID, $userId);
					if($result)
						$DB->Commit();
					else
						$DB->Rollback();

					if($ex = $APPLICATION->GetException())
					{
						$strError = $ex->GetString();
						if(!empty($result) && is_array($result))
						{
							$strError .= CCatalogStoreControlUtil::showErrorProduct($result);
						}
						$lAdmin->AddGroupError($strError, $ID);
					}
					break;
				case "copy":
					$arResult = array();
					$DB->StartTransaction();
					$dbDocument = CCatalogDocs::getList(array(), array("ID" => $ID), false, false, array("DOC_TYPE", "SITE_ID", "CONTRACTOR_ID", "CURRENCY", "TOTAL", "RESPONSIBLE_ID"));
					if($arDocument = $dbDocument->Fetch())
					{
						foreach($arDocument as $key => $value)
						{
							$arResult[$key] = $value;
						}
						$arResult["DATE_DOCUMENT"] = 'now';
						$arResult["CREATED_BY"] = $arResult["MODIFIED_BY"] = $USER->GetID();
						if (empty($arResult["RESPONSIBLE_ID"]))
						{
							$arResult["RESPONSIBLE_ID"] = $USER->GetID();
						}
						$dbDocumentElement = CCatalogStoreDocsElement::getList(
							array('ID' => 'ASC'),
							array("DOC_ID" => $ID),
							false,
							false,
							array("ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "IS_MULTIPLY_BARCODE")
						);
						while($arDocumentElement = $dbDocumentElement->Fetch())
						{
							$arElement = array();
							foreach($arDocumentElement as $key => $value)
							{
								if ($key == 'ID')
									continue;
								$arElement[$key] = $value;
							}
							if($arDocumentElement['IS_MULTIPLY_BARCODE'] == 'N')
							{
								$dbDocumentElementBarcode = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocumentElement["ID"]), false, false, array("BARCODE"));
								while($arDocumentElementBarcode = $dbDocumentElementBarcode->Fetch())
								{
									$arElement["BARCODE"][] = $arDocumentElementBarcode["BARCODE"];
								}
							}

							$arResult["ELEMENT"][] = $arElement;
						}
					}
					$result = CCatalogDocs::add($arResult);
					if($result)
						$DB->Commit();
					else
						$DB->Rollback();

					if($ex = $APPLICATION->GetException())
					{
						$strError = $ex->GetString();
						$lAdmin->AddGroupError($strError, $ID);
					}
					break;
			}
		}
	}
	if (!empty($blockedList))
	{
		$strError = '';
		switch($action)
		{
			case 'delete':
				$strError = Loc::getMessage('CAT_DOC_GROUP_ERR_DELETE');
				break;
			case 'conduct':
				$strError = Loc::getMessage('CAT_DOC_GROUP_ERR_CONDUCT');
				break;
			case 'cancellation':
				$strError = Loc::getMessage('CAT_DOC_GROUP_ERR_CANCEL');
				break;
		}
		foreach ($blockedList as &$ID)
		{
			$lAdmin->AddGroupError(str_replace('#ID#', $ID, $strError), $ID);
		}
		unset($ID, $strError);
	}
	unset($blockedList);

	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$headers = [];

$headers[] = [
	'id' => 'ID',
	'content' => 'ID',
	'sort' => 'ID',
	'default' => true,
];
$headers[] = [
	"id" => "DOC_TYPE",
	"content" => Loc::getMessage("CAT_DOC_TYPE_MSGVER_1"),
	"sort" => "DOC_TYPE",
	"default" => true,
];
$headers[] = [
	'id' => 'TITLE',
	'content' => Loc::getMessage('CAT_DOC_TITLE'),
	'sort' => 'TITLE',
	'default' => true,
];
$headers[] = [
	"id" => "STATUS",
	"content" => Loc::getMessage("CAT_DOC_STATUS"),
	"sort" => "STATUS",
	"default" => true,
];
$headers[] = [
	'id' => 'DOC_NUMBER',
	'content' => Loc::getMessage('CAT_DOC_DOC_NUMBER'),
	'sort' => 'DOC_NUMBER',
	'default' => true,
];
$headers[] = [
	"id" => "DATE_DOCUMENT",
	"content" => Loc::getMessage("CAT_DOC_DATE_DOCUMENT_EXT"),
	"sort" => "DATE_DOCUMENT",
	"default" => true,
];
$headers[] = [
	'id' => 'ITEMS_ORDER_DATE',
	'content' => Loc::getMessage('CAT_DOC_ITEMS_ORDER_DATE'),
	'sort' => 'ITEMS_ORDER_DATE',
	'default' => false,
];
$headers[] = [
	'id' => 'ITEMS_RECEIVED_DATE',
	'content' => Loc::getMessage('CAT_DOC_ITEMS_RECEIVED_DATE'),
	'sort' => 'ITEMS_RECEIVED_DATE',
	'default' => false,
];
$headers[] = [
	"id" => "CREATED_BY",
	"content" => Loc::getMessage("CAT_DOC_CREATOR"),
	"sort" => "CREATED_BY",
	"default" => true,
];
$headers[] = [
	"id" => "DATE_CREATE",
	"content" => Loc::getMessage("CAT_DOC_DATE_CREATE"),
	"sort" => "DATE_CREATE",
	"default" => false,
];
$headers[] = [
	"id" => "MODIFIED_BY",
	"content" => Loc::getMessage("CAT_DOC_MODIFIER"),
	"sort" => "MODIFIED_BY",
	"default" => true,
];
$headers[] = [
	"id" => "DATE_MODIFY",
	"content" => Loc::getMessage("CAT_DOC_DATE_MODIFY"),
	"sort" => "DATE_MODIFY",
	"default" => true,
];
$headers[] = [
	'id' => 'RESPONSIBLE_ID',
	'content' => Loc::getMessage('CAT_DOC_RESPONSIBLE_ID'),
	'sort' => 'RESPONSIBLE_ID',
	'default' => false,
];
$headers[] = [
	"id" => "CONTRACTOR_ID",
	"content" => Loc::getMessage("CAT_DOC_CONTRACTOR"),
	"sort" => "CONTRACTOR_ID",
	"default" => true,
];
$headers[] = [
	"id" => "SITE_ID",
	"content" => Loc::getMessage("CAT_DOC_SITE_ID"),
	"sort" => "SITE_ID",
	"default" => true,
];
$headers[] = [
	"id" => "CURRENCY",
	"content" => Loc::getMessage("CAT_DOC_CURRENCY"),
	"sort" => "CURRENCY",
	"default" => true,
];
$headers[] = [
	"id" => "TOTAL",
	"content" => Loc::getMessage("CAT_DOC_TOTAL"),
	"default" => true,
];
$headers[] = [
	"id" => "COMMENTARY",
	"content" => Loc::getMessage("CAT_DOC_COMMENT"),
	"default" => false,
];

$lAdmin->AddHeaders($headers);

$arSelectFieldsMap = [];
foreach ($headers as $item)
{
	$arSelectFieldsMap[$item['id']] = false;
}
unset($item, $headers);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();

$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

if (in_array('TOTAL', $arSelectFields))
{
	$arSelectFields[] = 'CURRENCY';
}
if (in_array('TITLE', $arSelectFields))
{
	$arSelectFields[] = 'DATE_CREATE';
}
$arReqFileds = [
	'ID',
	'STATUS',
	'WAS_CANCELLED',
];
$arSelectFields = array_unique(array_merge($arSelectFields, $arReqFileds));

$arUserList = array();
$strNameFormat = CSite::GetNameFormat(true);
$arRows = array();

$showCancel = false;
$showConduct = false;
$showDelete = false;

$filteredProduct = 0;
if (isset($arFilter['PRODUCT']))
{
	$filteredProduct = (int)$arFilter['PRODUCT'];
	unset($arFilter['PRODUCT']);
}

$dbResultList = $query = Catalog\StoreDocumentTable::query()
	->setFilter($arFilter)
	->setOrder($docsOrder)
	->setSelect($arSelectFields);
if ($filteredProduct)
{
	$query->withProduct($filteredProduct);
}
$dbResultList = $dbResultList->exec();
$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_store_document_list.php"));

$arUserID = [];
while($arRes = $dbResultList->Fetch())
{
	$arRes['ID'] = (int)$arRes['ID'];
	if($arSelectFieldsMap['CREATED_BY'])
	{
		$arRes['CREATED_BY'] = (int)$arRes['CREATED_BY'];
		if($arRes['CREATED_BY'] > 0)
			$arUserID[$arRes['CREATED_BY']] = true;
	}
	if($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arRes['MODIFIED_BY'] = (int)$arRes['MODIFIED_BY'];
		if($arRes['MODIFIED_BY'] > 0)
			$arUserID[$arRes['MODIFIED_BY']] = true;
	}

	$bAllowForEdit = true;
	$strForAction = "EDIT";

	if ($arRes['STATUS'] === 'Y')
	{
		$arRes['COMPILE_STATUS'] = Catalog\StoreDocumentTable::STATUS_CONDUCTED;
		$strForAction = "VIEW";
		$bAllowForEdit = false;
		$showCancel = true;
	}
	else
	{
		$arRes['COMPILE_STATUS'] = $arRes['WAS_CANCELLED'] === 'Y'
			? Catalog\StoreDocumentTable::STATUS_CANCELLED
			: Catalog\StoreDocumentTable::STATUS_DRAFT
		;
		$showConduct = true;
		$showDelete = true;
	}

	$arRows[$arRes['ID']] = $row = &$lAdmin->AddRow($arRes['ID'], $arRes, $selfFolderUrl."cat_store_document_edit.php?ID=".$arRes['ID']."&lang=".LANGUAGE_ID);
	$row->AddField("ID", $arRes['ID']);
	if($arSelectFieldsMap['DOC_TYPE'])
		$row->AddViewField('DOC_TYPE', $listDocType[$arRes['DOC_TYPE']]);
	if ($arSelectFieldsMap['STATUS'])
	{
		$row->AddViewField('STATUS', $statusList[$arRes['COMPILE_STATUS']]);
	}
	if ($arSelectFieldsMap['TITLE'])
	{
		$title = $arRes['TITLE'];
		$title .= '<div>' . Loc::getMessage(
			'CAT_DOC_TITLE_DOCUMENT_DATE',
			[
				'#DATE#' => FormatDateFromDB($arRes['DATE_CREATE'], 'SHORT'),
			]
		) . '</div>';
		$row->AddViewField('TITLE', $title);
	}
	if($arSelectFieldsMap['DATE_DOCUMENT'])
		$row->AddCalendarField('DATE_DOCUMENT', false);
	if($arSelectFieldsMap['DATE_CREATE'])
		$row->AddCalendarField('DATE_CREATE', false);
	if($arSelectFieldsMap['DATE_MODIFY'])
		$row->AddCalendarField('DATE_MODIFY', false);
	if($arSelectFieldsMap['CONTRACTOR_ID'])
	{
		$contractorTitle = '';
		if(0 < intval($arRes['CONTRACTOR_ID']))
		{
			$contractorEditUrl = $selfFolderUrl.'cat_contractor_edit.php?lang='.LANGUAGE_ID.'&ID='. $arRes['CONTRACTOR_ID'];
			$contractorEditUrl = $adminSidePanelHelper->editUrlToPublicPage($contractorEditUrl);
			$contractorTitle = '<a href="'.$contractorEditUrl.'">'.htmlspecialcharsbx(getContractorTitle($arRes['CONTRACTOR_ID'])).'</a>';
		}
		$row->AddViewField("CONTRACTOR_ID", $contractorTitle);
	}
	if($arSelectFieldsMap['SITE_ID'])
	{
		$row->AddViewField("SITE_ID", getSiteTitle($arRes['SITE_ID']));
	}

	if($arSelectFieldsMap['TOTAL'])
	{
		$f_TOTAL = ($arRes['CURRENCY']) ? CCurrencyLang::CurrencyFormat(
			doubleval($arRes['TOTAL']), $arRes['CURRENCY'], false) : '';

		$row->AddViewField("TOTAL", $f_TOTAL);
	}

	if($arSelectFieldsMap['COMMENTARY'])
	{
		$row->AddViewField("COMMENTARY", htmlspecialcharsbx($arRes["COMMENTARY"]));
	}


	$arActions = array();
	$editUrl = $selfFolderUrl."cat_store_document_edit.php?lang=".LANGUAGE_ID."&ID=".$arRes['ID'];
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => Loc::getMessage("CAT_DOC_".$strForAction),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if ($bAllowForEdit)
	{
		$arActions[] = array(
			"ICON" => "pack",
			"TEXT" => Loc::getMessage("CAT_DOC_CONDUCT"),
			"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "conduct")
		);
		$arActions[] = array(
			"ICON" => "copy",
			"TEXT" => Loc::getMessage("CAT_DOC_COPY"),
			"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "copy")
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("CAT_DOC_DELETE"),
			"ACTION" => "if(confirm('".CUtil::JSEscape(Loc::getMessage('CAT_DOC_DELETE_CONFIRM'))."')) ".
				$lAdmin->ActionDoGroup($arRes['ID'], "delete")
		);
	}
	else
	{
		$arActions[] = array(
			"ICON" => "unpack",
			"TEXT" => Loc::getMessage("CAT_DOC_CANCELLATION"),
			"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "cancellation")
		);
		$arActions[] = array(
			"ICON" => "copy",
			"TEXT" => Loc::getMessage("CAT_DOC_COPY"),
			"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "copy")
		);
	}

	$row->AddActions($arActions);
}
if(isset($row))
	unset($row);

if($arSelectFieldsMap['CREATED_BY'] || $arSelectFieldsMap['MODIFIED_BY'])
{
	if(!empty($arUserID))
	{
		$rsUsers = CUser::GetList(
			'ID',
			'ASC',
			array('ID' => implode(' | ', array_keys($arUserID))),
			array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
		);
		while($arOneUser = $rsUsers->Fetch())
		{
			$arOneUser['ID'] = (int)$arOneUser['ID'];
			$urlToUser = $selfFolderUrl."user_edit.php?lang=".LANGUAGE_ID."&ID=".$arOneUser["ID"];
			if ($publicMode)
			{
				$urlToUser = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$arOneUser["ID"]."&lang=".LANGUAGE_ID;
				$urlToUser = $adminSidePanelHelper->editUrlToPublicPage($urlToUser);
			}
			$arUserList[$arOneUser['ID']] = '<a href="'.$urlToUser.'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
		}
	}
	foreach($arRows as &$row)
	{
		if($arSelectFieldsMap['CREATED_BY'])
		{
			$strCreatedBy = '';
			if($row->arRes['CREATED_BY'] > 0 && isset($arUserList[$row->arRes['CREATED_BY']]))
			{
				$strCreatedBy = $arUserList[$row->arRes['CREATED_BY']];
			}
			$row->AddViewField("CREATED_BY", $strCreatedBy);
		}
		if($arSelectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if($row->arRes['MODIFIED_BY'] > 0 && isset($arUserList[$row->arRes['MODIFIED_BY']]))
			{
				$strModifiedBy = $arUserList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if(isset($row))
		unset($row);
}

$actionList = array();
if ($showConduct)
	$actionList['conduct'] = Loc::getMessage('CAT_DOC_CONDUCT');
if ($showCancel)
	$actionList['cancellation'] = Loc::getMessage('CAT_DOC_CANCELLATION');
$actionList['copy'] = Loc::getMessage('CAT_DOC_COPY');
if ($showDelete)
	$actionList['delete'] = Loc::getMessage('MAIN_ADMIN_LIST_DELETE');
$lAdmin->AddGroupActionTable($actionList);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("CAT_DOCS_MSGVER_1"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();
if($errorMessage <> '')
	CAdminMessage::ShowMessage($errorMessage);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
