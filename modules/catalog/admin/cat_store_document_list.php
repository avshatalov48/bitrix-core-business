<?
use Bitrix\Catalog;

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

if(!$USER->CanDoOperation('catalog_store'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_store');

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

ClearVars();

$sTableID = "b_catalog_store_docs";

$oSort = new CAdminUiSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$errorMessage = "";
$bVarsFromForm = false;

$ID = (isset($_REQUEST["ID"]) ? (int)$_REQUEST["ID"] : 0);

$str_ACTIVE = "Y";
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

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_store_docs", "", "str_");

//$documentTypes = CCatalogDocs::$types;
$arSiteMenu = array();

$listDocType = Catalog\StoreDocumentTable::getTypeList(true);
foreach ($listDocType as $type => $title)
{
	$addUrl = $selfFolderUrl."cat_store_document_edit.php?lang=".LANGUAGE_ID."&DOCUMENT_TYPE=".$type."";
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
		"TEXT" => GetMessage("CAT_DOC_ADD"),
		"ICON" => "btn_new",
		"TITLE" =>  GetMessage("CAT_DOC_ADD_TITLE"),
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

$filterFields = array(
	array(
		"id" => "ID",
		"name" => "ID",
		"filterable" => "",
		"quickSearch" => ""
	),
	array(
		"id" => "SITE_ID",
		"name" => GetMessage("CAT_DOC_SITE_ID"),
		"type" => "list",
		"items" => $listSite,
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "DOC_TYPE",
		"name" => GetMessage("CAT_DOC_TYPE"),
		"type" => "list",
		"items" => $listDocType,
		"filterable" => ""
	),
	array(
		"id" => "DATE_DOCUMENT",
		"name" => GetMessage("CAT_DOC_DATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "CONTRACTOR_ID",
		"name" => GetMessage("CAT_DOC_CONTRACTOR"),
		"type" => "list",
		"items" => $listContractors,
		"filterable" => ""
	),
	array(
		"id" => "STATUS",
		"name" => GetMessage("CAT_DOC_STATUS"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("CAT_DOC_EXECUTION_Y"),
			"N" => GetMessage("CAT_DOC_EXECUTION_N")
		),
		"filterable" => "",
	),
);

$arFilter = array();

$lAdmin->AddFilter($filterFields, $arFilter);

global $by, $order;
if (!isset($by))
	$by = 'ID';
$by = mb_strtoupper($by);

if (!isset($order))
	$order = 'DESC';
$order = mb_strtoupper($order);
$docsOrder = array($by => $order);

if (!$bReadOnly && ($arID = $lAdmin->GroupAction()))
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arID = array();
		$docsIterator = CCatalogDocs::getList($docsOrder, $arFilter, false, false, array('ID'));
		while($arResult = $docsIterator->Fetch())
		{
			$arID[] = $arResult['ID'];
		}
	}

	$blockedList = array();
	$arID = array_filter($arID);
	if (!empty($arID))
	{
		if (
			$_REQUEST['action'] == 'delete'
			|| $_REQUEST['action'] == 'conduct'
			|| $_REQUEST['action'] == 'cancellation'
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
			switch($_REQUEST['action'])
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
							$lAdmin->AddGroupError(GetMessage("ERROR_DELETING_TYPE"), $ID);
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
		switch($_REQUEST['action'])
		{
			case 'delete':
				$strError = GetMessage('CAT_DOC_GROUP_ERR_DELETE');
				break;
			case 'conduct':
				$strError = GetMessage('CAT_DOC_GROUP_ERR_CONDUCT');
				break;
			case 'cancellation':
				$strError = GetMessage('CAT_DOC_GROUP_ERR_CANCEL');
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

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"DOC_TYPE", "content"=>GetMessage("CAT_DOC_TYPE"), "sort"=>"DOC_TYPE", "default"=>true),
	array("id"=>"STATUS", "content"=>GetMessage("CAT_DOC_STATUS"), "sort"=>"STATUS", "default"=>true),
	array("id"=>"DATE_DOCUMENT","content"=>GetMessage("CAT_DOC_DATE_DOCUMENT"), "sort"=>"DATE_DOCUMENT", "default"=>true),
	array("id"=>"CREATED_BY", "content"=>GetMessage("CAT_DOC_CREATOR"),  "sort"=>"CREATED_BY", "default"=>true),
	array("id"=>"DATE_CREATE","content"=>GetMessage("CAT_DOC_DATE_CREATE"), "sort"=>"DATE_CREATE", "default"=>false),
	array("id"=>"MODIFIED_BY", "content"=>GetMessage("CAT_DOC_MODIFIER"),  "sort"=>"MODIFIED_BY", "default"=>true),
	array("id"=>"DATE_MODIFY","content"=>GetMessage("CAT_DOC_DATE_MODIFY"), "sort"=>"DATE_MODIFY", "default"=>true),
	array("id"=>"CONTRACTOR_ID", "content"=>GetMessage("CAT_DOC_CONTRACTOR"),  "sort"=>"CONTRACTOR_ID", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage("CAT_DOC_SITE_ID"),  "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"CURRENCY", "content"=>GetMessage("CAT_DOC_CURRENCY"),  "sort"=>"CURRENCY", "default"=>true),
	array("id"=>"TOTAL", "content"=>GetMessage("CAT_DOC_TOTAL"),  "sort"=>"TOTAL", "default"=>true),
	array("id"=>"COMMENTARY", "content"=>GetMessage("CAT_DOC_COMMENT"),  "sort"=>"COMMENTARY", "default"=>false),
));
$arSelectFieldsMap = array(
	"ID" => false,
	"DOC_TYPE" => false,
	"STATUS" => false,
	"DATE_DOCUMENT" => false,
	"CREATED_BY" => false,
	"DATE_CREATE" => false,
	"MODIFIED_BY" => false,
	"DATE_MODIFY" => false,
	"CONTRACTOR_ID" => false,
	"SITE_ID" => false,
	"CURRENCY" => false,
	"TOTAL" => false,
	"COMMENTARY" => false,
);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();

$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

if(in_array('TOTAL', $arSelectFields))
	$arSelectFields[] = 'CURRENCY';
$arReqFileds = array(
	'ID',
	'STATUS'
);
$arSelectFields = array_unique(array_merge($arSelectFields, $arReqFileds));

$arUserList = array();
$strNameFormat = CSite::GetNameFormat(true);
$arRows = array();

$showCancel = false;
$showConduct = false;
$showDelete = false;

$dbResultList = CCatalogDocs::getList($docsOrder, $arFilter, false, false, $arSelectFields);
$dbResultList = new CAdminUiResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."cat_store_document_list.php"));

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

	if ($arRes['STATUS'] == 'Y')
	{
		$strForAction = "VIEW";
		$bAllowForEdit = false;
		$showCancel = true;
	}
	if ($arRes['STATUS'] == 'N')
	{
		$showConduct = true;
		$showDelete = true;
	}

	$arRows[$arRes['ID']] = $row = &$lAdmin->AddRow($arRes['ID'], $arRes, "cat_store_document_edit.php?ID=".$arRes['ID']."&lang=".LANGUAGE_ID);
	$row->AddField("ID", $arRes['ID']);
	if($arSelectFieldsMap['DOC_TYPE'])
		$row->AddViewField('DOC_TYPE', $listDocType[$arRes['DOC_TYPE']]);
	if($arSelectFieldsMap['STATUS'])
		$row->AddViewField("STATUS", GetMessage("CAT_DOC_EXECUTION_".$arRes['STATUS']));
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
		"TEXT" => GetMessage("CAT_DOC_".$strForAction),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		if ($bAllowForEdit)
		{
			$arActions[] = array(
				"ICON" => "pack",
				"TEXT" => GetMessage("CAT_DOC_CONDUCT"),
				"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "conduct")
			);
			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("CAT_DOC_COPY"),
				"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "copy")
			);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("CAT_DOC_DELETE"),
				"ACTION" => "if(confirm('".GetMessageJS('CAT_DOC_DELETE_CONFIRM')."')) ".
					$lAdmin->ActionDoGroup($arRes['ID'], "delete")
			);
		}
		else
		{
			$arActions[] = array(
				"ICON" => "unpack",
				"TEXT" => GetMessage("CAT_DOC_CANCELLATION"),
				"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "cancellation")
			);
			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("CAT_DOC_COPY"),
				"ACTION" => $lAdmin->ActionDoGroup($arRes['ID'], "copy")
			);
		}
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
			$urlToUser = $selfFolderUrl."user_edit.php?lang=".LANGUAGE_ID."&ID=".$arOneUser["ID"]."";
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

if (!$bReadOnly)
{
	$actionList = array();
	if ($showConduct)
		$actionList['conduct'] = GetMessage('CAT_DOC_CONDUCT');
	if ($showCancel)
		$actionList['cancellation'] = GetMessage('CAT_DOC_CANCELLATION');
	$actionList['copy'] = GetMessage('CAT_DOC_COPY');
	if ($showDelete)
		$actionList['delete'] = GetMessage('MAIN_ADMIN_LIST_DELETE');
	$lAdmin->AddGroupActionTable($actionList);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CAT_DOCS"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();
if($errorMessage <> '')
	CAdminMessage::ShowMessage($errorMessage);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");