<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;

if(!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
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

$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$errorMessage = "";
$bVarsFromForm = false;

$ID = (isset($_REQUEST["ID"]) ? (int)$_REQUEST["ID"] : 0);
$TAB_TITLE = (isset($TAB_TITLE)) ? GetMessage("CAT_DOC_$TAB_TITLE") : GetMessage("CAT_DOC_NEW");

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
 * @param $siteId
 * @return string
 */
function getSiteTitle($siteId)
{
	static $rsSites = '';
	static $arSitesShop = array();
	$siteTitle = $siteId;

	if($rsSites === '')
	{
		$rsSites = CSite::GetList($b="id", $o="asc", Array("ACTIVE" => "Y"));
		while($arSite = $rsSites->GetNext())
			$arSitesShop[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
	}

	foreach($arSitesShop as $arSite)
	{
		if($arSite["ID"] == $siteId)
		{
			$siteTitle = $arSite["NAME"]." (".$arSite["ID"].")";
		}
	}
	return $siteTitle;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_store_docs", "", "str_");

$documentTypes = CCatalogDocs::$types;
$arSiteMenu = array();

foreach($documentTypes as $type => $class)
	$arSiteMenu[] = array(
		"TEXT" => GetMessage("CAT_DOC_".$type),
		"ACTION" => "window.location = 'cat_store_document_edit.php?lang=".LANGUAGE_ID."&DOCUMENT_TYPE=".$type."';"
	);

$aContext = array(
	array(
		"TEXT" => GetMessage("CAT_DOC_ADD"),
		"ICON" => "btn_new",
		"TITLE" =>  GetMessage("CAT_DOC_ADD_TITLE"),
		"MENU" => $arSiteMenu
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$arFilterFields = array(
	"filter_site_id",
	"filter_doc_type",
	"filter_contractor_id",
	"filter_status",
	"filter_date_document_from",
	"filter_date_document_to",
);
$filterValues = array_fill_keys($arFilterFields, null);
$lAdmin->InitFilter($arFilterFields);

if (isset($filter_site_id) && is_string($filter_site_id))
{
	$filter_site_id = trim($filter_site_id);
	if ($filter_site_id != '' && $filter_site_id != 'NOT_REF')
		$filterValues['filter_site_id'] = $filter_site_id;
}
if (isset($filter_doc_type) && is_string($filter_doc_type))
{
	$filter_doc_type = trim($filter_doc_type);
	if ($filter_doc_type != '' && isset($documentTypes[$filter_doc_type]))
		$filterValues['filter_doc_type'] = $filter_doc_type;
}
if (isset($filter_contractor_id) && is_string($filter_contractor_id))
{
	$filter_contractor_id = trim($filter_contractor_id);
	if ($filter_contractor_id != '')
		$filterValues['filter_contractor_id'] = (int)$filter_contractor_id;
}
if (isset($filter_status) && is_string($filter_status))
{
	if ($filter_status == 'Y' || $filter_status == 'N')
		$filterValues['filter_status'] = $filter_status;
}
if (isset($filter_date_document_from) && is_string($filter_date_document_from))
{
	$filter_date_document_from = trim($filter_date_document_from);
	if ($filter_date_document_from !== '')
		$filterValues['filter_date_document_from'] = $filter_date_document_from;
}
if (isset($filter_date_document_to) && is_string($filter_date_document_to))
{
	if ($filter_date_document_to !== '')
		$filterValues['filter_date_document_to'] = $filter_date_document_to;
}

$arFilter = array();
if ($filterValues['filter_site_id'] !== null)
	$arFilter['SITE_ID'] = $filterValues['filter_site_id'];
if ($filterValues['filter_doc_type'] !== null)
	$arFilter['DOC_TYPE'] = $filterValues['filter_doc_type'];
if ($filterValues['filter_contractor_id'] !== null)
	$arFilter['CONTRACTOR_ID'] = $filterValues['filter_contractor_id'];
if ($filterValues['filter_status'] !== null)
	$arFilter["STATUS"] = $filterValues['filter_status'];
if ($filterValues['filter_date_document_from'] !== null)
	$arFilter['!<DATE_DOCUMENT'] = $filterValues['filter_date_document_from'];
if ($filterValues['filter_date_document_to'] !==  null)
	$arFilter["!>DATE_DOCUMENT"] = (CIBlock::isShortDate($filterValues['filter_date_document_to'])
		? ConvertTimeStamp(AddTime(MakeTimeStamp($filterValues['filter_date_document_to']), 1, "D"), "FULL")
		: $filterValues['filter_date_document_to']
	);

if(strlen($_REQUEST["filter_date_document_to"])>0)
{
	if($arDate = ParseDateTime($_REQUEST["filter_date_document_to"], CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if(StrLen($_REQUEST["filter_date_document_to"]) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_document_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["!>DATE_DOCUMENT"] = $filter_date_document_to;
	}
	else
	{
		$filter_date_document_to = "";
	}
}

if (!isset($by))
	$by = 'ID';
$by = strtoupper($by);

if (!isset($order))
	$order = 'DESC';
$order = strtoupper($order);
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
					$dbDocument = CCatalogDocs::getList(array(), array("ID" => $ID), false, false, array("DOC_TYPE", "SITE_ID", "CONTRACTOR_ID", "CURRENCY", "TOTAL"));
					if($arDocument = $dbDocument->Fetch())
					{
						foreach($arDocument as $key => $value)
						{
							$arResult[$key] = $value;
						}
						$arResult["DATE_DOCUMENT"] = 'now';
						$arResult["CREATED_BY"] = $arResult["MODIFIED_BY"] = $USER->GetID();
						$dbDocumentElement = CCatalogStoreDocsElement::getList(array(), array("DOC_ID" => $ID), false, false, array("ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "IS_MULTIPLY_BARCODE"));
						while($arDocumentElement = $dbDocumentElement->Fetch())
						{
							$arElement = array();
							foreach($arDocumentElement as $key => $value)
							{
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

$arNavParams = (
	isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel'
	? false
	: array('nPageSize' => CAdminResult::GetNavSize($sTableID))
);

$dbResultList = CCatalogDocs::getList(
	$docsOrder,
	$arFilter,
	false,
	$arNavParams,
	$arSelectFields
);
$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("group_admin_nav")));

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

	$arRows[$arRes['ID']] = $row = &$lAdmin->AddRow($arRes['ID'], $arRes);
	$row->AddField("ID", $arRes['ID']);
	if($arSelectFieldsMap['DOC_TYPE'])
		$row->AddViewField('DOC_TYPE', GetMessage("CAT_DOC_".$arRes['DOC_TYPE']));
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
			$contractorTitle = '<a href="/bitrix/admin/cat_contractor_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes['CONTRACTOR_ID'].'">'.htmlspecialcharsbx(getContractorTitle($arRes['CONTRACTOR_ID'])).'</a>';
		}
		$row->AddViewField("CONTRACTOR_ID", $contractorTitle);
	}
	if($arSelectFieldsMap['SITE_ID'])
	{
		$row->AddViewField("SITE_ID", getSiteTitle($arRes['SITE_ID']));
	}

	if($arSelectFieldsMap['TOTAL'])
	{
			$f_TOTAL = ($arRes['CURRENCY']) ? CCurrencyLang::CurrencyFormat(doubleval($arRes['TOTAL']), $arRes['CURRENCY'], false) : '';

		$row->AddViewField("TOTAL", $f_TOTAL);
	}

	if($arSelectFieldsMap['COMMENTARY'])
	{
		$row->AddViewField("COMMENTARY", htmlspecialcharsbx($arRes["COMMENTARY"]));
	}


	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("CAT_DOC_".$strForAction), "ACTION"=>$lAdmin->ActionRedirect("cat_store_document_edit.php?ID=".$arRes['ID']."&lang=".LANGUAGE_ID/*."&".GetFilterParams("filter_").""*/), "DEFAULT"=>true);

	if (!$bReadOnly)
	{
		if ($bAllowForEdit)
		{
			$arActions[] = array("ICON"=>"pack", "TEXT"=>GetMessage("CAT_DOC_CONDUCT"), "ACTION"=>$lAdmin->ActionDoGroup($arRes['ID'], "conduct"));
			$arActions[] = array("ICON"=>"copy", "TEXT"=>GetMessage("CAT_DOC_COPY"), "ACTION"=>$lAdmin->ActionDoGroup($arRes['ID'], "copy"));
			$arActions[] = array("SEPARATOR" => true);
			$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("CAT_DOC_DELETE"), "ACTION"=>"if(confirm('".GetMessageJS('CAT_DOC_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arRes['ID'], "delete"));
		}
		else
		{
			$arActions[] = array("ICON"=>"unpack", "TEXT"=>GetMessage("CAT_DOC_CANCELLATION"), "ACTION"=>$lAdmin->ActionDoGroup($arRes['ID'], "cancellation"));
			$arActions[] = array("ICON"=>"copy", "TEXT"=>GetMessage("CAT_DOC_COPY"), "ACTION"=>$lAdmin->ActionDoGroup($arRes['ID'], "copy"));
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
		$byUser = 'ID';
		$byOrder = 'ASC';
		$rsUsers = CUser::GetList(
			$byUser,
			$byOrder,
			array('ID' => implode(' | ', array_keys($arUserID))),
			array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
		);
		while($arOneUser = $rsUsers->Fetch())
		{
			$arOneUser['ID'] = (int)$arOneUser['ID'];
			$arUserList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arOneUser['ID'].'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
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
			if($row->arRes['MODIFIED_BY'] > 0 && isset($arUserList[$row->arRes['CREATED_BY']]))
			{
				$strModifiedBy = $arUserList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if(isset($row))
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
?>
	<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
		<?
		$arContractors = array();
		$dbContractors = CCatalogContractor::getList(array());
		while($arContractorRes = $dbContractors->Fetch())
		{
			$arContractors[] = $arContractorRes;
		}

		$oFilter = new CAdminFilter(
			$sTableID."_filter",
			array(
				GetMessage("CAT_DOC_SITE_ID"),
				GetMessage("CAT_DOC_TYPE"),
				GetMessage("CAT_DOC_DATE"),
				GetMessage("CAT_DOC_CONTRACTOR"),
				GetMessage("CAT_DOC_STATUS"),
			)
		);

		$oFilter->Begin();
		?>
		<tr>
			<td><?= GetMessage("CAT_DOC_SITE_ID") ?>:</td>
			<td>
				<?echo CSite::SelectBox("filter_site_id", $filterValues['filter_site_id'], "(".GetMessage("CAT_DOC_SITE_ID").")"); ?>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CAT_DOC_TYPE") ?>:</td>
			<td>
				<select name="filter_doc_type">
					<option value=""><?=htmlspecialcharsbx("(".GetMessage("CAT_DOC_TYPE").")") ?></option>

					<?
					foreach($documentTypes as $type => $class)
					{
						?>
						<option value="<?=$type?>"<?if($filterValues['filter_doc_type'] == $type) echo " selected"?>><?=htmlspecialcharsbx(GetMessage("CAT_DOC_".$type)) ?></option>
					<?
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CAT_DOC_DATE") ?> (<?= CSite::GetDateFormat("SHORT") ?>):</td>
			<td>
				<?=CAdminCalendar::CalendarPeriod(
					'filter_date_document_from',
					'filter_date_document_to',
					$filterValues['filter_date_document_from'],
					$filterValues['filter_date_document_to'],
					true,
					10,
					false
				); ?>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CAT_DOC_CONTRACTOR") ?>:</td>
			<td>
				<select name="filter_contractor_id">
					<option value=""><?=htmlspecialcharsbx("(".GetMessage("CAT_DOC_CONTRACTOR").")") ?></option>

					<?
					foreach($arContractors as $arContractor)
					{
						?>
						<option value="<?=$arContractor["ID"]?>"<?if($filterValues['filter_contractor_id'] == $arContractor["ID"]) echo " selected"?>><?= htmlspecialcharsbx(getContractorTitle($arContractor["ID"])) ?></option>
					<?
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CAT_DOC_STATUS") ?>:</td>
			<td>
				<select name="filter_status">
					<option value=""><?=htmlspecialcharsbx("(".GetMessage("CAT_DOC_STATUS").")") ?></option>
					<option value="Y"<?if($filterValues['filter_status'] == "Y") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("CAT_DOC_EXECUTION_Y")) ?></option>
					<option value="N"<?if($filterValues['filter_status'] == "N") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("CAT_DOC_EXECUTION_N")) ?></option>
				</select>
			</td>
		</tr>

		<?
		$oFilter->Buttons(
			array(
				"table_id" => $sTableID,
				"url" => $APPLICATION->GetCurPage(),
				"form" => "find_form"
			)
		);
		$oFilter->End();
		?>
	</form>
<?
$lAdmin->DisplayList();
if(strlen($errorMessage) > 0)
	CAdminMessage::ShowMessage($errorMessage);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");