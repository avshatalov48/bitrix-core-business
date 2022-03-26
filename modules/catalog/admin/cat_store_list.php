<?php
/** @global CUserTypeManager $USER_FIELD_MANAGER */
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;
global $USER_FIELD_MANAGER;

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

if(!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm('');
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_store');

Loc::loadMessages(__FILE__);

$bExport = false;
if ($_REQUEST["mode"] == "excel")
	$bExport = true;

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
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

$sTableID = "b_catalog_store";
$entityId = Catalog\StoreTable::getUfId();

$oSort = new CAdminUiSorting($sTableID, "SORT", "ASC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$listSite = array();
$sitesQueryObject = CSite::getList("sort", "asc", array("ACTIVE" => "Y"));
while ($site = $sitesQueryObject->fetch())
{
	$listSite[$site["LID"]] = $site["NAME"]." [".$site["LID"]."]";
}

$filterFields = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"type" => "number",
		"filterable" => "=",
		"default" => true
	),
	array(
		"id" => "SITE_ID",
		"name" => Loc::getMessage("STORE_SITE_ID"),
		"type" => "list",
		"items" => $listSite,
		"filterable" => ""
	),
	array(
		"id" => "ACTIVE",
		"name" => Loc::getMessage("STORE_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => Loc::getMessage("BX_CATALOG_STORE_LIST_FILTER_YES_VALUE"),
			"N" => Loc::getMessage("BX_CATALOG_STORE_LIST_FILTER_NO_VALUE")
		),
		"filterable" => "="
	),
	[
		'id' => 'IS_DEFAULT',
		'name' => Loc::getMessage('BX_CATALOG_STORE_LIST_FIELD_IS_DEFAULT'),
		'type' => 'list',
		'items' => [
			'Y' => Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_YES_VALUE'),
			'N' => Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_NO_VALUE'),
		],
		'filterable' => '=',
	],
	array(
		"id" => "TITLE",
		"name" => Loc::getMessage("TITLE"),
		"filterable" => "%",
		"quickSearch" => "%"
	),
	array(
		"id" => "CODE",
		"name" => Loc::getMessage("STORE_CODE"),
		"filterable" => "="
	),
	array(
		"id" => "XML_ID",
		"name" => Loc::getMessage("STORE_XML_ID"),
		"filterable" => "="
	),
	array(
		"id" => "ISSUING_CENTER",
		"name" => Loc::getMessage("ISSUING_CENTER"),
		"type" => "list",
		"items" => array(
			"Y" => Loc::getMessage("BX_CATALOG_STORE_LIST_FILTER_YES_VALUE"),
			"N" => Loc::getMessage("BX_CATALOG_STORE_LIST_FILTER_NO_VALUE")
		),
		"filterable" => "="
	),
	array(
		"id" => "SHIPPING_CENTER",
		"name" => Loc::getMessage("SHIPPING_CENTER"),
		"type" => "list",
		"items" => array(
			"Y" => Loc::getMessage("BX_CATALOG_STORE_LIST_FILTER_YES_VALUE"),
			"N" => Loc::getMessage("BX_CATALOG_STORE_LIST_FILTER_NO_VALUE")
		),
		"filterable" => "="
	),
	array(
		"id" => "ADDRESS",
		"name" => Loc::getMessage("ADDRESS"),
		"filterable" => "%"
	),
	array(
		"id" => "PHONE",
		"name" => Loc::getMessage("PHONE"),
		"filterable" => "%"
	),
	array(
		"id" => "EMAIL",
		"name" => "E-mail",
		"filterable" => "%"
	),
);
$USER_FIELD_MANAGER->AdminListAddFilterFieldsV2($entityId, $filterFields);

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);
$USER_FIELD_MANAGER->AdminListAddFilterV2($entityId, $filter, $sTableID, $filterFields);

if (!$bReadOnly)
{
	if ($lAdmin->EditAction())
	{
		foreach ($lAdmin->GetEditFields() as $ID => $arFields)
		{
			$ID = (int)$ID;

			if ($ID <= 0)
			{
				continue;
			}
			if (array_key_exists('IMAGE_ID', $arFields))
			{
				unset($arFields['IMAGE_ID']);
			}
			if (isset($arFields['GPS_N']))
			{
				$arFields['GPS_N'] = str_replace(',', '.', $arFields['GPS_N']);
			}
			if (isset($arFields['GPS_S']))
			{
				$arFields['GPS_S'] = str_replace(',', '.', $arFields['GPS_S']);
			}
			if (array_key_exists('IS_DEFAULT', $arFields))
			{
				unset($arFields['IS_DEFAULT']);
			}
			$USER_FIELD_MANAGER->AdminListPrepareFields($entityId, $arFields);

			$DB->StartTransaction();
			if (!CCatalogStore::Update($ID, $arFields))
			{
				if ($ex = $APPLICATION->GetException())
				{
					$lAdmin->AddUpdateError($ex->GetString(), $ID);
				}
				else
				{
					$lAdmin->AddUpdateError(Loc::getMessage("ERROR_UPDATING_REC")
						. " ("
						. $arFields["ID"]
						. ", "
						. $arFields["TITLE"]
						. ", "
						. $arFields["SORT"]
						. ")", $ID);
				}

				$DB->Rollback();
			}
			else
			{
				$ufUpdated = $USER_FIELD_MANAGER->Update($entityId, $ID, $arFields);
				$DB->Commit();
			}
		}
	}

	$arID = $lAdmin->GroupAction();
	if (!empty($arID) && is_array($arID))
	{
		$actionId = $lAdmin->GetAction();
		if ($actionId !== null)
		{
			if ($lAdmin->IsGroupActionToAll())
			{
				$arID = [];
				$dbResultList = CCatalogStore::GetList([], $filter, false, false, ['ID']);
				while ($arResult = $dbResultList->Fetch())
				{
					$arID[] = $arResult['ID'];
				}
			}

			Main\Type\Collection::normalizeArrayValuesByInt($arID, false);

			$defaultStoreId = (int)Catalog\StoreTable::getDefaultStoreId();
			$allowedStoreSite = '';
			$siteCount = Main\SiteTable::getCount([
				'=ACTIVE' => 'Y',
			]);
			if ($siteCount === 1)
			{
				$iterator = Main\SiteTable::getList([
					'select' => ['LID'],
					'filter' => ['=ACTIVE' => 'Y'],
				]);
				$row = $iterator->fetch();
				$allowedStoreSite = $row['LID'];
				unset($row, $iterator);
			}
			unset($siteCount);

			foreach ($arID as $ID)
			{
				switch ($actionId)
				{
					case 'delete':
						if ($ID === $defaultStoreId)
						{
							$lAdmin->AddGroupError(
								Loc::getMessage('BX_CATALOG_STORE_LIST_ERR_CANNOT_DELETE_DEFAULT_STORE'),
								$ID
							);
							break;
						}

						@set_time_limit(0);
						$DB->StartTransaction();

						if (!CCatalogStore::Delete($ID))
						{
							$DB->Rollback();

							if ($ex = $APPLICATION->GetException())
							{
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							}
							else
							{
								$lAdmin->AddGroupError(
									Loc::getMessage(
										'BX_CATALOG_STORE_LIST_ERR_CANNOT_DELETE_STORE',
										['#ID#' => $ID]
									),
									$ID
								);
							}
						}
						else
						{
							$DB->Commit();
						}
						break;
					case 'activate':
						@set_time_limit(0);
						$DB->StartTransaction();
						if (CCatalogStore::Update($ID, ['ACTIVE' => 'Y']))
						{
							$DB->Commit();
						}
						else
						{
							$DB->Rollback();
							if ($ex = $APPLICATION->GetException())
							{
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							}
							else
							{
								$lAdmin->AddGroupError(
									Loc::getMessage(
										'BX_CATALOG_STORE_LIST_ERR_CANNOT_ACTIVATE_STORE',
										['#ID#' => $ID]
									),
									$ID
								);
							}
						}
						break;
					case 'deactivate':
						if ($ID === $defaultStoreId)
						{
							$lAdmin->AddGroupError(
								Loc::getMessage('BX_CATALOG_STORE_LIST_ERR_CANNOT_DEACTIVATE_DEFAULT_STORE'),
								$ID
							);
							break;
						}

						@set_time_limit(0);
						$DB->StartTransaction();
						if (CCatalogStore::Update($ID, ['ACTIVE' => 'N']))
						{
							$DB->Commit();
						}
						else
						{
							$DB->Rollback();
							if ($ex = $APPLICATION->GetException())
							{
								$lAdmin->AddGroupError($ex->GetString(), $ID);
							}
							else
							{
								$lAdmin->AddGroupError(
									Loc::getMessage(
										'BX_CATALOG_STORE_LIST_ERR_CANNOT_DEACTIVATE_STORE',
										['#ID#' => $ID]
									),
									$ID
								);
							}
						}
						break;
					case 'setdefault':
						if ($ID !== $defaultStoreId)
						{
							$iterator = Catalog\StoreTable::getList([
								'select' => ['ID', 'ACTIVE', 'SITE_ID'],
								'filter' => ['=ID' => $ID],
							]);
							$row = $iterator->fetch();
							unset($iterator);
							if (!empty($row))
							{
								if ($row['ACTIVE'] !== 'Y')
								{
									$lAdmin->AddGroupError(
										Loc::getMessage(
											'BX_CATALOG_STORE_LIST_ERR_CANNOT_SET_DEFAULT_NON_ACTIVE_STORE'
										),
										$ID
									);
								}
								$row['SITE_ID'] = (string)$row['SITE_ID'];
								if ($row['SITE_ID'] !== '' && $row['SITE_ID'] !== $allowedStoreSite)
								{
									$lAdmin->AddGroupError(
										Loc::getMessage(
											'BX_CATALOG_STORE_LIST_ERR_CANNOT_SET_DEFAULT_SITE_STORE'
										),
										$ID
									);
								}
								$DB->StartTransaction();
								$successChange = true;
								$internalResult = Catalog\StoreTable::update($defaultStoreId, ['IS_DEFAULT' => 'N']);
								if ($internalResult->isSuccess())
								{
									$internalResult = Catalog\StoreTable::update($ID, ['IS_DEFAULT' => 'Y']);
									if ($internalResult->isSuccess())
									{
										$defaultStoreId = $ID;
									}
									else
									{
										$successChange = false;
										$lAdmin->AddGroupError(
											Loc::getMessage(
												'BX_CATALOG_STORE_LIST_ERR_CANNOT_SET_DEFAULT_STORE_INTERNAL',
												['#ERROR#' => implode('; ', $internalResult->getErrorMessages())]
											),
											$ID
										);
									}
								}
								else
								{
									$successChange = false;
									$lAdmin->AddGroupError(
										Loc::getMessage(
											'BX_CATALOG_STORE_LIST_ERR_CANNOT_SET_DEFAULT_STORE_INTERNAL',
											['#ERROR#' => implode('; ', $internalResult->getErrorMessages())]
										),
										$ID
									);
								}
								if ($successChange)
								{
									$DB->Commit();
								}
								else
								{
									$DB->Rollback();
								}
							}
						}
						break;
				}
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
}

$filterSiteList = array();
$siteList = array();
$siteIterator = Main\SiteTable::getList(array(
	'select' => array('LID', 'NAME', 'ACTIVE', 'SORT'),
	'order' => array('SORT' => 'ASC')
));
while ($site = $siteIterator->fetch())
{
	$filterSiteList[] = $site;
	$siteList[$site['LID']] = $site['LID'];
}
unset($site, $siteIterator);

$arSelect = array(
	"ID",
	"ACTIVE",
	"TITLE",
	"ADDRESS",
	"DESCRIPTION",
	"GPS_N",
	"GPS_S",
	"IMAGE_ID",
	"PHONE",
	"SCHEDULE",
	"XML_ID",
	"DATE_MODIFY",
	"DATE_CREATE",
	"USER_ID",
	"MODIFIED_BY",
	"SORT",
	"EMAIL",
	"ISSUING_CENTER",
	"SHIPPING_CENTER",
	"SITE_ID",
	"CODE",
	"IS_DEFAULT",
	"UF_*"
);

global $by, $order;
if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$dbResultList = CCatalogStore::GetList(array($by => $order), $filter, false, false, $arSelect);

$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_store_list.php"));

$headers = array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "SORT",
		"content" => Loc::getMessage("CSTORE_SORT"),
		"sort" => "SORT",
		"default" => true
	),
	array(
		"id" => "TITLE",
		"content" => Loc::getMessage("TITLE"),
		"sort" => "TITLE",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"content" => Loc::getMessage("STORE_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true
	),
	[
		'id' => 'IS_DEFAULT',
		'content' => Loc::getMessage('BX_CATALOG_STORE_LIST_FIELD_IS_DEFAULT'),
		'sort' => 'IS_DEFAULT',
		'default' => true,
	],
	array(
		"id" => "ADDRESS",
		"content" => Loc::getMessage("ADDRESS"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "IMAGE_ID",
		"content" => Loc::getMessage("STORE_IMAGE"),
		"sort" => "",
		"default" => false
	),
	array(
		"id" => "DESCRIPTION",
		"content" => Loc::getMessage("DESCRIPTION"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "GPS_N",
		"content" => Loc::getMessage("GPS_N"),
		"sort" => "GPS_N",
		"default" => false
	),
	array(
		"id" => "GPS_S",
		"content" => Loc::getMessage("GPS_S"),
		"sort" => "GPS_S",
		"default" => false
	),
	array(
		"id" => "PHONE",
		"content" => Loc::getMessage("PHONE"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "SCHEDULE",
		"content" => Loc::getMessage("SCHEDULE"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "DATE_MODIFY",
		"content" => Loc::getMessage("DATE_MODIFY"),
		"sort" => "DATE_MODIFY",
		"default" => true
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => Loc::getMessage("MODIFIED_BY"),
		"sort" => "MODIFIED_BY",
		"default" => true
	),
	array(
		"id" => "DATE_CREATE",
		"content" => Loc::getMessage("DATE_CREATE"),
		"sort" => "DATE_CREATE",
		"default" => false
	),
	array(
		"id" => "USER_ID",
		"content" => Loc::getMessage("USER_ID"),
		"sort" => "USER_ID",
		"default" => false
	),
	array(
		"id" => "EMAIL",
		"content" => "E-mail",
		"sort" => "EMAIL",
		"default" => false
	),
	array(
		"id" => "ISSUING_CENTER",
		"content" => Loc::getMessage("ISSUING_CENTER"),
		"sort" => "ISSUING_CENTER",
		"default" => false
	),
	array(
		"id" => "SHIPPING_CENTER",
		"content" => Loc::getMessage("SHIPPING_CENTER"),
		"sort" => "SHIPPING_CENTER",
		"default" => false
	),
	array(
		"id" => "SITE_ID",
		"content" => Loc::getMessage("STORE_SITE_ID"),
		"sort" => "SITE_ID",
		"default" => true
	),
	array(
		"id" => "CODE",
		"content" => Loc::getMessage("STORE_CODE"),
		"sort" => "CODE",
		"default" => false
	),
	array(
		"id" => "XML_ID",
		"content" => Loc::getMessage("STORE_XML_ID"),
		"sort" => "XML_ID",
		"default" => false
	)
);

$USER_FIELD_MANAGER->AdminListAddHeaders($entityId, $headers);

$arSelectFieldsMap = array(
	"ID" => false,
	"TITLE" => false,
	"ACTIVE" => false,
	"ADDRESS" => false,
	"IMAGE_ID" => false,
	"DESCRIPTION" => false,
	"GPS_N" => false,
	"GPS_S" => false,
	"PHONE" => false,
	"SCHEDULE" => false,
	"DATE_MODIFY" => false,
	"MODIFIED_BY" => false,
	"DATE_CREATE" => false,
	"USER_ID" => false,
	"EMAIL" => false,
	"ISSUING_CENTER" => false,
	"SHIPPING_CENTER" => false,
	"SITE_ID" => false,
	"CODE" => false,
	"XML_ID" => false,
	"IS_DEFAULT" => false,
);

$lAdmin->AddHeaders($headers);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if(!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

$arUserList = array();
$arUserID = array();
$strNameFormat = CSite::GetNameFormat(true);

$arRows = array();

while ($arRes = $dbResultList->Fetch())
{
	$arRes['ID'] = (int)$arRes['ID'];
	$arRes['SORT'] = (int)$arRes['SORT'];
	if($arSelectFieldsMap['USER_ID'])
	{
		$arRes['USER_ID'] = (int)$arRes['USER_ID'];
		if(0 < $arRes['USER_ID'])
			$arUserID[$arRes['USER_ID']] = true;
	}
	if($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arRes['MODIFIED_BY'] = (int)$arRes['MODIFIED_BY'];
		if(0 < $arRes['MODIFIED_BY'])
			$arUserID[$arRes['MODIFIED_BY']] = true;
	}

	$editUrl = $selfFolderUrl."cat_store_edit.php?ID=".$arRes['ID']."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arRows[$arRes['ID']] = $row =& $lAdmin->AddRow($arRes['ID'], $arRes, $editUrl);
	$USER_FIELD_MANAGER->AddUserFields($entityId, $arRes, $row);
	$row->AddField("ID", '<a href="'.$editUrl.'">'.$arRes['ID'].'</a>');
	if($bReadOnly)
	{
		$row->AddViewField("SORT", $arRes['SORT']);
		if($arSelectFieldsMap['CODE'])
			$row->AddInputField("CODE", false);
		if($arSelectFieldsMap['TITLE'])
			$row->AddInputField("TITLE", false);
		if($arSelectFieldsMap['ADDRESS'])
			$row->AddInputField("ADDRESS", false);
		if($arSelectFieldsMap['DESCRIPTION'])
			$row->AddInputField("DESCRIPTION", false);
		if($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE", false);
		if($arSelectFieldsMap['ISSUING_CENTER'])
			$row->AddCheckField("ISSUING_CENTER", false);
		if($arSelectFieldsMap['SHIPPING_CENTER'])
			$row->AddCheckField("SHIPPING_CENTER", false);
		if($arSelectFieldsMap['PHONE'])
			$row->AddInputField("PHONE", false);
		if($arSelectFieldsMap['SCHEDULE'])
			$row->AddInputField("SCHEDULE", false);
		if($arSelectFieldsMap['EMAIL'])
			$row->AddInputField("EMAIL", false);
		if($arSelectFieldsMap['IMAGE_ID'] && !$bExport)
			$row->AddField("IMAGE_ID", CFile::ShowImage($arRes['IMAGE_ID'], 100, 100, "border=0", "", true));
		if($arSelectFieldsMap['GPS_N'])
			$row->AddInputField('GPS_N', false);
		if($arSelectFieldsMap['GPS_S'])
			$row->AddInputField('GPS_S', false);
		if($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", false);
	}
	else
	{
		$row->AddInputField("SORT", array("size" => "3"));
		if($arSelectFieldsMap['CODE'])
			$row->AddInputField("CODE");
		if($arSelectFieldsMap['TITLE'])
			$row->AddInputField("TITLE");
		if($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE");
		if($arSelectFieldsMap['ISSUING_CENTER'])
			$row->AddCheckField("ISSUING_CENTER");
		if($arSelectFieldsMap['SHIPPING_CENTER'])
			$row->AddCheckField("SHIPPING_CENTER");
		if($arSelectFieldsMap['ADDRESS'])
			$row->AddInputField("ADDRESS", array("size" => 30));
		if($arSelectFieldsMap['DESCRIPTION'])
			$row->AddInputField("DESCRIPTION", array("size" => 50));
		if($arSelectFieldsMap['PHONE'])
			$row->AddInputField("PHONE", array("size" => 25));
		if($arSelectFieldsMap['SCHEDULE'])
			$row->AddInputField("SCHEDULE", array("size" => 35));
		if($arSelectFieldsMap['EMAIL'])
			$row->AddInputField("EMAIL", array("size" => 35));
		if($arSelectFieldsMap['IMAGE_ID'] && !$bExport)
			$row->AddField("IMAGE_ID", CFile::ShowImage($arRes['IMAGE_ID'], 100, 100, "border=0", "", true));
		if($arSelectFieldsMap['GPS_N'])
			$row->AddInputField('GPS_N', array('size' => 35));
		if($arSelectFieldsMap['GPS_S'])
			$row->AddInputField('GPS_S', array('size' => 35));
		if($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID");
	}

	if ($arSelectFieldsMap['IS_DEFAULT'])
	{
		$row->AddCheckField('IS_DEFAULT', false);
	}

	if($arSelectFieldsMap['SITE_ID'] && $arRes['SITE_ID'])
		$row->AddViewField("SITE_ID", htmlspecialcharsbx(getSiteTitle($arRes['SITE_ID'])));
	if($arSelectFieldsMap['DATE_CREATE'])
		$row->AddCalendarField("DATE_CREATE", false);
	if($arSelectFieldsMap['DATE_MODIFY'])
		$row->AddCalendarField("DATE_MODIFY", false);

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => Loc::getMessage("EDIT_STORE_ALT"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if(!$bReadOnly)
	{
		if ($arRes['IS_DEFAULT'] !== 'Y')
		{
			if ($arRes['ACTIVE'] !== 'Y')
			{
				$arActions[] = [
					'ICON' => 'activate',
					'TEXT' => Loc::getMessage('BX_CATALOG_STORE_LIST_ACTION_ACTIVATE'),
					'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'activate'),
				];
			}
			else
			{
				$arActions[] = [
					'ICON' => 'deactivate',
					'TEXT' => Loc::getMessage('BX_CATALOG_STORE_LIST_ACTION_DEACTIVATE'),
					'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'deactivate'),
				];
			}
			$arActions[] = [
				'ICON' => 'edit',
				'TEXT' => Loc::getMessage('BX_CATALOG_STORE_LIST_ACTION_SET_DEFAULT'),
				'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'setdefault'),
			];
			$arActions[] = [
				'ICON' => 'delete',
				'TEXT' => Loc::getMessage('DELETE_STORE_ALT'),
				'ACTION' => "if(confirm('" . CUtil::JSEscape(Loc::getMessage('DELETE_STORE_CONFIRM')) . "')) "
					. $lAdmin->ActionDoGroup($arRes['ID'], 'delete'),
			];
		}
		else
		{
			if ($arRes['ACTIVE'] !== 'Y')
			{
				$arActions[] = [
					'ICON' => 'activate',
					'TEXT' => Loc::getMessage('BX_CATALOG_STORE_LIST_ACTION_ACTIVATE'),
					'ACTION' => $lAdmin->ActionDoGroup($arRes['ID'], 'activate'),
				];
			}
		}
	}

	$row->AddActions($arActions);
}
if(isset($row))
	unset($row);

if($arSelectFieldsMap['USER_ID'] || $arSelectFieldsMap['MODIFIED_BY'])
{
	if(!empty($arUserID))
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
			$urlToUser = "/bitrix/admin/user_edit.php?lang=".LANGUAGE_ID."&ID=".$arOneUser["ID"]."";
			if ($publicMode)
			{
				$urlToUser = $selfFolderUrl."sale_buyers_profile.php?USER_ID=".$arOneUser["ID"]."&lang=".LANGUAGE_ID;
				$urlToUser = $adminSidePanelHelper->editUrlToPublicPage($urlToUser);
			}
			$arUserList[$arOneUser['ID']] = '<a href="'.$urlToUser.'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
		}
	}

	foreach ($arRows as &$row)
	{
		if($arSelectFieldsMap['USER_ID'])
		{
			$strCreatedBy = '';
			if (0 < $row->arRes['USER_ID'] && isset($arUserList[$row->arRes['USER_ID']]))
			{
				$strCreatedBy = $arUserList[$row->arRes['USER_ID']];
			}
			$row->AddViewField("USER_ID", $strCreatedBy);
		}
		if($arSelectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if (0 < $row->arRes['MODIFIED_BY'] && isset($arUserList[$row->arRes['USER_ID']]))
			{
				$strModifiedBy = $arUserList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if(isset($row))
		unset($row);
}

if (!$bReadOnly)
{
	$actions = [];
	if (!Catalog\Config\State::isExceededStoreLimit())
	{
		$actions['edit'] = true;
	}
	$actions['delete'] = true;
	$lAdmin->AddGroupActionTable($actions);
	unset($actions);
}

$aContext = [];
if(!$bReadOnly)
{
	if (Catalog\Config\State::isAllowedNewStore())
	{
		$addUrl = $selfFolderUrl."cat_store_edit.php?lang=".LANGUAGE_ID;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		$aContext[] = [
			"TEXT" => Loc::getMessage("STORE_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => $addUrl,
			"TITLE" => Loc::getMessage("STORE_ADD_NEW_ALT")
		];
	}
	else
	{
		$helpLink = Catalog\Config\Feature::getMultiStoresHelpLink();
		if (!empty($helpLink))
		{
			$aContext[] = [
				'TEXT' => Loc::getMessage('STORE_ADD_NEW'),
				'ICON' => 'btn_lock',
				$helpLink['TYPE'] => $helpLink['LINK'],
				'TITLE' => Loc::getMessage('STORE_ADD_NEW_ALT')
			];
		}
		unset($helpLink);
	}
}
$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."cat_store_list.php"));
$lAdmin->AddAdminContextMenu($aContext);
unset($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("STORE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
