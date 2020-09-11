<?
/** @global CMain $APPLICATION */
/** @global $DB CDatabase */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Iblock\Grid\ActionType,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
if($arIBTYPE===false)
	$APPLICATION->AuthForm(GetMessage("IBSEC_A_BAD_BLOCK_TYPE_ID"));

$IBLOCK_ID = (isset($_REQUEST['IBLOCK_ID']) ? (int)$_REQUEST['IBLOCK_ID'] : 0);
$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);

if($arIBlock)
	$bBadBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
else
	$bBadBlock = true;

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBSEC_A_BAD_IBLOCK"));?>
	<a href="iblock_admin.php?lang=<?echo LANGUAGE_ID?>&amp;type=<?echo htmlspecialcharsbx($type)?>"><?echo GetMessage("IBSEC_A_BACK_TO_ADMIN")?></a>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$useTree = (isset($_GET['tree']) && $_GET['tree'] === 'Y');

$urlBuilder = Iblock\Url\AdminPage\BuilderManager::getInstance()->getBuilder();
if ($urlBuilder === null)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBSEC_A_ERR_BUILDER_ADSENT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$urlBuilder->setIblockId($IBLOCK_ID);
$urlBuilder->setUrlParams(array());

$pageConfig = array(
	'IBLOCK_EDIT' => false,
	'CHECK_NEW_CARD' => false,
	'USE_NEW_CARD' => false,

	'LIST_ID_PREFIX' => '',
	'LIST_ID' => $type.'.'.$IBLOCK_ID,
	'SHOW_NAVCHAIN' => true,
	'NAVCHAIN_ROOT' => false,
);
switch ($urlBuilder->getId())
{
	case 'CRM':
	case 'SHOP':
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_catalog_section_';
		$pageConfig['CHECK_NEW_CARD'] = true;
		$pageConfig['SHOW_NAVCHAIN'] = false;
		$pageConfig['CONTEXT_PATH'] = '/shop/settings/cat_section_admin.php'; // TODO: temporary hack
		break;
	case 'CATALOG':
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_catalog_section_';
		$pageConfig['CONTEXT_PATH'] = '/bitrix/admin/cat_section_admin.php'; // TODO: temporary hack
		break;
	case 'IBLOCK':
		$pageConfig['IBLOCK_EDIT'] = true;
		$pageConfig['LIST_ID_PREFIX'] = 'tbl_iblock_section_';
		$pageConfig['NAVCHAIN_ROOT'] = true;
		$pageConfig['CONTEXT_PATH'] = '/bitrix/admin/iblock_section_admin.php'; // TODO: temporary hack
		break;
}

$sectionTranslit = $arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"];
$useSectionTranslit = $sectionTranslit["TRANSLITERATION"] == "Y" && $sectionTranslit["USE_GOOGLE"] != "Y";
$sectionTranslitSettings = array();
if ($useSectionTranslit)
{
	$sectionTranslitSettings = array(
		"max_len" => $sectionTranslit['TRANS_LEN'],
		"change_case" => $sectionTranslit['TRANS_CASE'],
		"replace_space" => $sectionTranslit['TRANS_SPACE'],
		"replace_other" => $sectionTranslit['TRANS_OTHER'],
		"delete_repeat_replace" => ($sectionTranslit['TRANS_EAT'] == 'Y')
	);
}

$entity_id = "IBLOCK_".$IBLOCK_ID."_SECTION";
$sTableID = $pageConfig['LIST_ID_PREFIX'].md5($pageConfig['LIST_ID']);

$catalogIncluded = Loader::includeModule('catalog');
$useCatalog = $catalogIncluded;
$catalog = false;
if ($catalogIncluded)
{
	$catalog = CCatalogSKU::GetInfoByIBlock($arIBlock["ID"]);
	if (empty($catalog))
	{
		$useCatalog = false;
	}
	else
	{
		if (!$USER->CanDoOperation('catalog_price'))
			$useCatalog = false;
	}
}

if ($useTree)
{
	$by = "left_margin";
	$order = "asc";
}

$oSort = new CAdminUiSorting($sTableID, "timestamp_x", "desc");
global $by, $order;
$arOrder = (mb_strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$groupParams = array(
	'ENTITY_ID' => $sTableID,
	'IBLOCK_ID' => $IBLOCK_ID
);
if ($useCatalog)
{
	$panelAction = new Catalog\Grid\Panel\ProductGroupAction($groupParams);
}
else
{
	$panelAction = new Iblock\Grid\Panel\GroupAction($groupParams);
}
unset($groupParams);

if ($useTree)
{
	$lAdmin->AddVisibleHeaderColumn("DEPTH_LEVEL");
}

$sectionItems = array(
	"" => GetMessage("IBLOCK_ALL"),
	"0" => GetMessage("IBSEC_A_ROOT_SECTION"),
);
$sectionQueryObject = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
while($arSection = $sectionQueryObject->Fetch())
	$sectionItems[$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("IBSEC_A_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true
	),
	array(
		"id" => "SECTION_ID",
		"name" => rtrim(GetMessage("IBSEC_A_SECTION"), ":"),
		"type" => "list",
		"items" => $sectionItems,
		"filterable" => "",
		"default" => true
	),
	array(
		"id" => "ID",
		"name" => GetMessage("IBSEC_A_ID"),
		"type" => "number",
		"filterable" => ""
	),
	array(
		"id" => "TIMESTAMP_X",
		"name" => GetMessage("IBSEC_A_TIMESTAMP"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "MODIFIED_BY",
		"name" => GetMessage("IBSEC_A_MODIFIED_BY"),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "DATE_CREATE",
		"name" => GetMessage("IBSEC_A_DATE_CREATE"),
		"type" => "date",
		"filterable" => ""
	),
	array(
		"id" => "CREATED_BY",
		"name" => GetMessage("IBSEC_A_CREATED_BY"),
		"type" => "custom_entity",
		"selector" => array("type" => "user"),
		"filterable" => ""
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("IBSEC_A_ACTIVE"),
		"type" => "list",
		"items" => array(
			"" => GetMessage("IBLOCK_ALL"),
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => ""
	),
	array(
		"id" => "CODE",
		"name" => GetMessage("IBSEC_A_CODE"),
		"filterable" => ""
	),
	array(
		"id" => "EXTERNAL_ID",
		"name" => GetMessage("IBSEC_A_XML_ID"),
		"filterable" => ""
	),
);

global $USER_FIELD_MANAGER;
$USER_FIELD_MANAGER->AdminListAddFilterFieldsV2($entity_id, $filterFields);

//We have to handle current section in a special way
$parent_section_id = $find_section_section === '' || $find_section_section === null ? '' : (int)$find_section_section;
$find_section_section = $parent_section_id;
$parent_section_id = (int)$parent_section_id;
if ($parent_section_id < 0)
{
	$parent_section_id = 0;
}

//This is all parameters needed for proper navigation
$sThisSectionUrl = '&type='.urlencode($type).'&lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$find_section_section;

$arFilter = $baseFilter = array("IBLOCK_ID" => $IBLOCK_ID);

$lAdmin->AddFilter($filterFields, $arFilter);

$USER_FIELD_MANAGER->AdminListAddFilterV2($entity_id, $arFilter, $sTableID, $filterFields);

if (isset($arFilter["SECTION_ID"]))
{
	$find_section_section = (int)$arFilter["SECTION_ID"];
}
else
{
	$isDifferences = array_diff($baseFilter, array_diff($arFilter, array_map(function ($field) {
		return $field["id"];
	}, $filterFields)));
	if ($isDifferences)
	{
		$arFilter["SECTION_ID"] = $find_section_section;
	}
}

if ($find_section_section === '' || $find_section_section === null || (int)$find_section_section < 0)
{
	unset($arFilter["SECTION_ID"]);
}
elseif ($useTree)
{
	unset($arFilter["SECTION_ID"]);
	$parentDepth = 0;
	$rsParent = CIBlockSection::GetByID($find_section_section);
	if($arParent = $rsParent->Fetch())
	{
		$arFilter["LEFT_MARGIN"] = $arParent["LEFT_MARGIN"]+1;
		$arFilter["RIGHT_MARGIN"] = $arParent["RIGHT_MARGIN"]-1;
		$parentDepth = $arParent["DEPTH_LEVEL"];
	}
}

// Edititng handling (do not forget rights check!)
if($lAdmin->EditAction()) //save button pressed
{
	if (!empty($_FILES['FIELDS']) && is_array($_FILES['FIELDS']))
		CFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);

	foreach($_POST['FIELDS'] as $ID=>$arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if(!CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit"))
			continue;

		$USER_FIELD_MANAGER->AdminListPrepareFields($entity_id, $arFields);
		$arFields["IBLOCK_ID"] = $IBLOCK_ID;

		$ib = new CIBlockSection;
		$DB->StartTransaction();
		if(!$ib->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
				$message = $e->GetString();
			else
				$message = $ib->LAST_ERROR;
			$lAdmin->AddUpdateError(GetMessage("IBSEC_A_SAVE_ERROR", array("#ID#"=>$ID)).": ".$message, $ID);
			$DB->Rollback();
		}
		else
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($IBLOCK_ID, $ID);
			$ipropValues->clearValues();
			$DB->Commit();
		}
	}
}

// action handler
if ($arID = $lAdmin->GroupAction())
{
	$actionId = $lAdmin->GetAction();
	$actionParams = null;
	if (is_string($actionId))
	{
		$actionParams = $panelAction->getRequest($actionId);
	}

	if ($actionId !== null && $actionParams !== null)
	{
		$productSections = array();

		if ($lAdmin->IsGroupActionToAll())
		{
			$arID = array();
			$rsData = CIBlockSection::GetList($arOrder, $arFilter, false, array('ID'));
			while ($arRes = $rsData->Fetch())
			{
				$arID[] = $arRes['ID'];
			}
			unset($arRes, $rsData);
		}
		foreach ($arID as $ID)
		{
			$ID = (int)$ID;
			if ($ID <= 0)
				continue;

			switch ($actionId)
			{
				case ActionType::DELETE:
					if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_delete"))
					{
						@set_time_limit(0);
						$DB->StartTransaction();
						if (!CIBlockSection::Delete($ID))
						{
							if ($e = $APPLICATION->GetException())
								$message = $e->GetString();
							else
								$message = GetMessage("IBSEC_A_DELERR_REFERERS");

							$lAdmin->AddGroupError(GetMessage("IBSEC_A_DELERR", array(
									"#ID#" => $ID,
								))." [".$message."]", $ID);
							$DB->Rollback();
						}
						else
						{
							$DB->Commit();
						}
					}
					break;

				case ActionType::ACTIVATE:
				case ActionType::DEACTIVATE:
					if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit"))
					{
						$ob = new CIBlockSection();
						$arFields = array(
							"ACTIVE" => ($actionId == ActionType::ACTIVATE ? "Y" : "N"),
						);
						if (!$ob->Update($ID, $arFields))
							$lAdmin->AddGroupError(GetMessage("IBSEC_A_UPDERR").$ob->LAST_ERROR, $ID);
					}
					break;
				case ActionType::CODE_TRANSLIT:
					if ($useSectionTranslit && CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit"))
					{
						$iterator = Iblock\SectionTable::getList(array(
							'select' => array('ID', 'NAME'),
							'filter' => array('=ID' => $ID)
						));
						$current = $iterator->fetch();
						$arFields = array(
							'CODE' => CUtil::translit(
								$current['NAME'],
								LANGUAGE_ID,
								$sectionTranslitSettings
							)
						);
						$ob = new CIBlockSection();
						if (!$ob->Update($ID, $arFields))
						{
							$lAdmin->AddGroupError(GetMessage("IBSEC_A_UPDERR").$ob->LAST_ERROR, $ID);
						}
					}
					break;
			}

			if ($useCatalog)
			{
				switch ($actionId)
				{
					case Catalog\Grid\ProductAction::SET_FIELD:
						if (
							CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit")
							&& CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_price")
						)
						{
							$productSections[] = $ID;
						}
						break;
				}
			}
		}

		if (
			$useCatalog
			&& !empty($productSections)
		)
		{
			switch ($actionId)
			{
				case Catalog\Grid\ProductAction::SET_FIELD:
					$result = Catalog\Grid\ProductAction::updateSectionList(
						$IBLOCK_ID,
						$productSections,
						$actionParams
					);
					if (!$result->isSuccess())
					{
						foreach ($result->getErrors() as $error)
						{
							$lAdmin->AddGroupError($error->getMessage(), $error->getCode());
						}
						unset($error);
					}
					unset($result);
					break;
			}
		}
		unset($productSections);
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

// list header
$arHeaders = array(
	array(
		"id" => "NAME",
		"content" => GetMessage("IBSEC_A_NAME"),
		"sort" => "name",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("IBSEC_A_ACTIVE"),
		"sort" => "active",
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("IBSEC_A_SORT"),
		"sort" => "sort",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "CODE",
		"content" => GetMessage("IBSEC_A_CODE"),
		"sort" => "code",
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("IBSEC_A_XML_ID"),
		"sort" => "xml_id",
	),
	array(
		"id" => "PICTURE",
		"content" => GetMessage("IBSEC_A_PICTURE"),
		"align" => "right",
		"default" => false,
		"editable" => false,
		"prevent_default" => false
	),
	array(
		"id" => "DETAIL_PICTURE",
		"content" => GetMessage("IBSEC_A_DETAIL_PICTURE"),
		"align" => "right",
		"default" => false,
		"editable" => false,
		"prevent_default" => false
	),
	array(
		"id" => "ELEMENT_CNT",
		"content" => GetMessage("IBSEC_A_ELEMENT_CNT"),
		"sort" => "element_cnt",
		"align" => "right",
	),
	array(
		"id" => "SECTION_CNT",
		"content" => GetMessage("IBSEC_A_SECTION_CNT"),
		"align" => "right",
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("IBSEC_A_TIMESTAMP"),
		"sort" => "timestamp_x",
		"default" => true,
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage("IBSEC_A_MODIFIED_BY"),
		"sort" => "modified_by",
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage("IBSEC_A_DATE_CREATE"),
		"sort" => "date_create",
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage("IBSEC_A_CREATED_BY"),
		"sort" => "created_by",
	),
	array(
		"id" => "ID",
		"content" => GetMessage("IBSEC_A_ID"),
		"sort" => "id",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "DEPTH_LEVEL",
		"content" => GetMessage("IBSEC_A_DEPTH_LEVEL"),
		"align" => "right",
	),
	array(
		"id" => "DESCRIPTION",
		"content" => GetMessage("IBSEC_A_DESCRIPTION"),
		"title" => "",
		"default" => false
	)
);
$USER_FIELD_MANAGER->AdminListAddHeaders($entity_id, $arHeaders);

if ($useTree)
{
	foreach ($arHeaders as $i => $arHeader)
		if (isset($arHeader["sort"]))
			unset($arHeaders[$i]["sort"]);
}

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$arVisibleColumns[] = "IBLOCK_ID";
$arVisibleColumns[] = "ID";
$arVisibleColumns[] = "SECTION_PAGE_URL";
$arVisibleColumns[] = "DEPTH_LEVEL";
if (in_array("DESCRIPTION", $arVisibleColumns))
{
	$arVisibleColumns[] = "DESCRIPTION_TYPE";
}

$arVisibleColumnsMap = array();
foreach($arVisibleColumns as $value)
	$arVisibleColumnsMap[$value] = true;

if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "excel")
{
	$arNavParams = false;
}
else
{
	//TODO:: remove this hack after refactoring CAdminResult::GetNavSize
	$navResult = new CAdminUiResult(null, '');
	$arNavParams = array("nPageSize"=>$navResult->GetNavSize($sTableID));
	unset($navResult);
}

$rsData = CIBlockSection::GetList($arOrder, $arFilter, false, $arVisibleColumns, $arNavParams);

//$baseLink = ($publicMode ? $selfFolderUrl.CIBlock::GetAdminSectionListScriptName($IBLOCK_ID) : $APPLICATION->GetCurPage());

$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
$minImageSize = array('W' => 1, 'H' => 1);
$maxImageSize = array(
	'W' => $listImageSize,
	'H' => $listImageSize
);
unset($listImageSize);

$rsData = new CAdminUiResult($rsData, $sTableID);
$rsData->NavStart();
//$lAdmin->SetNavigationParams($rsData, array("BASE_LINK" => $baseLink));
$lAdmin->SetNavigationParams($rsData, array());
$arRows = array();

$elementSectionFilter = array(
	'IBLOCK_ID' => $IBLOCK_ID,
	'SHOW_NEW' => 'Y',
	'CHECK_PERMISSIONS' => 'Y',
	'MIN_PERMISSION' => 'R',
	'INCLUDE_SUBSECTIONS' => 'N'
);
$fullElementSectionFilter = $elementSectionFilter;
$fullElementSectionFilter['INCLUDE_SUBSECTIONS'] = 'Y';

while ($arRes = $rsData->Fetch())
{
	$el_add_url = htmlspecialcharsbx($urlBuilder->getElementDetailUrl(
		0,
		array(
			'find_section_section' => $arRes['ID'],
			'IBLOCK_SECTION_ID' => $arRes['ID'],
			'from' => 'iblock_section_admin_inc'
		)
	));
	$sec_add_url = htmlspecialcharsbx($urlBuilder->getSectionDetailUrl(
		null,
		array(
			'find_section_section' => $find_section_section,
			'IBLOCK_SECTION_ID' => $arRes["ID"],
			'from' => 'iblock_section_admin',
		)
	));
	$edit_url = htmlspecialcharsbx($urlBuilder->getSectionDetailUrl(
		(int)$arRes["ID"],
		array(
			'find_section_section' => $find_section_section,
			'from' => 'iblock_section_admin',
		)
	));

	$elementListUrl = $urlBuilder->getElementListUrl($arRes['ID'], array('INCLUDE_SUBSECTIONS' => 'N'));
	$nestedElementListUrl = $urlBuilder->getElementListUrl($arRes['ID'], array('INCLUDE_SUBSECTIONS' => 'Y'));
	$sec_list_url = htmlspecialcharsbx($urlBuilder->getSectionListUrl(
		$arRes["ID"],
		array('tree' => $useTree? 'Y': 'N')
	));

	$arRows[$arRes["ID"]] = $row = $lAdmin->AddRow($arRes["ID"], $arRes, $sec_list_url, GetMessage("IBSEC_A_LIST"));
	$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);

	$row->AddViewField("ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBSEC_A_EDIT").'">'.$arRes["ID"].'</a>');
	$row->AddViewField("NAME", '<a href="'.CHTTP::URN2URI($sec_list_url).'" '.($useTree ? 'style="padding-left:'.(($arRes["DEPTH_LEVEL"] - 1) * 22).'px"' : '').' class="adm-list-table-icon-link" title="'.GetMessage("IBSEC_A_LIST").'"><span class="adm-submenu-item-link-icon adm-list-table-icon iblock-section-icon"></span><span class="adm-list-table-link">'.htmlspecialcharsbx($arRes["NAME"]).'</span></a>');
	if (isset($arVisibleColumnsMap["ELEMENT_CNT"]))
	{
		$elementSectionFilter['SECTION_ID'] = $arRes['ID'];
		$fullElementSectionFilter['SECTION_ID'] = $arRes['ID'];

		$elementCount = (int)CIBlockElement::GetList(
			array(),
			$elementSectionFilter,
			array()
		);
		$fullElementCount = (int)CIBlockElement::GetList(
			array(),
			$fullElementSectionFilter,
			array()
		);

		$row->AddViewField("ELEMENT_CNT", '<a href="'.CHTTP::URN2URI($elementListUrl).'" title="'.GetMessage("IBSEC_A_ELLIST").'">'.$elementCount.'</a>'.
			' (<a href="'.CHTTP::URN2URI($nestedElementListUrl).'" title="'.GetMessage("IBSEC_A_ELLIST_TITLE").'">'.$fullElementCount.'</a>)'.
			' [<a href="'.$el_add_url.'" title="'.GetMessage("IBSEC_A_ELADD_TITLE").'">+</a>]'
		);
	}
	if (isset($arVisibleColumnsMap["SECTION_CNT"]))
	{
		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
			"SECTION_ID" => $arRes["ID"],
		);
		$row->AddViewField("SECTION_CNT", '<a href="'.CHTTP::URN2URI($sec_list_url).'" title="'.GetMessage("IBSEC_A_LIST").'">'.intval(CIBlockSection::GetCount($arFilter)).'</a> [<a href="'.$sec_add_url.'" title="'.GetMessage("IBSEC_A_SECTADD_TITLE").'">+</a>]');
	}
	if (isset($arVisibleColumnsMap["MODIFIED_BY"]))
	{
		if ($html = GetUserProfileLink($arRes["MODIFIED_BY"], GetMessage("IBSEC_A_USERINFO")))
			$row->AddViewField("MODIFIED_BY", $html);
	}
	if (isset($arVisibleColumnsMap["CREATED_BY"]))
	{
		if ($html = GetUserProfileLink($arRes["CREATED_BY"], GetMessage("IBSEC_A_USERINFO")))
			$row->AddViewField("CREATED_BY", $html);
	}
	if (isset($arVisibleColumnsMap["PICTURE"]))
	{
		$row->AddFileField("PICTURE", array(
			"IMAGE" => "Y",
			"PATH" => "Y",
			"FILE_SIZE" => "Y",
			"DIMENSIONS" => "Y",
			"IMAGE_POPUP" => "Y",
			"MAX_SIZE" => $maxImageSize,
			"MIN_SIZE" => $minImageSize,
		), array(
				'upload' => false,
				'medialib' => false,
				'file_dialog' => false,
				'cloud' => false,
				'del' => false,
				'description' => false,
			)
		);
	}
	if (isset($arVisibleColumnsMap["DETAIL_PICTURE"]))
	{
		$row->AddFileField("DETAIL_PICTURE", array(
			"IMAGE" => "Y",
			"PATH" => "Y",
			"FILE_SIZE" => "Y",
			"DIMENSIONS" => "Y",
			"IMAGE_POPUP" => "Y",
			"MAX_SIZE" => $maxImageSize,
			"MIN_SIZE" => $minImageSize,
		), array(
				'upload' => false,
				'medialib' => false,
				'file_dialog' => false,
				'cloud' => false,
				'del' => false,
				'description' => false,
			)
		);
	}
	if (isset($arVisibleColumnsMap["DESCRIPTION"]))
	{
		$row->AddViewField("DESCRIPTION", ($row->arRes["DESCRIPTION_TYPE"] == "text" ? htmlspecialcharsEx($row->arRes["DESCRIPTION"]) : HTMLToTxt($row->arRes["DESCRIPTION"])));
	}
}

$arSectionOps = CIBlockSectionRights::UserHasRightTo(
	$IBLOCK_ID,
	array_keys($arRows),
	"",
	CIBlockRights::RETURN_OPERATIONS
);
foreach ($arRows as $id => $row)
{
	if (isset($arSectionOps[$id]) && isset($arSectionOps[$id]["section_edit"]))
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", array(
			'size' => '35',
		));
		$row->AddInputField("SORT", array(
			'size' => '3',
		));
		$row->AddInputField("CODE");
		$row->AddInputField("XML_ID");

		if (isset($arVisibleColumnsMap["DESCRIPTION"]))
		{
			$sHTML = '<input type="radio" name="FIELDS['.$id.'][DESCRIPTION_TYPE]" value="text" id="'.$id.'DESCRIPTIONtext"';
			if($row->arRes["DESCRIPTION_TYPE"]!="html")
				$sHTML .= ' checked';
			$sHTML .= '><label for="'.$id.'PREVIEWtext">text</label> /';
			$sHTML .= '<input type="radio" name="FIELDS['.$id.'][DESCRIPTION_TYPE]" value="html" id="'.$id.'DESCRIPTIONhtml"';
			if($row->arRes["DESCRIPTION_TYPE"]=="html")
				$sHTML .= ' checked';
			$sHTML .= '><label for="'.$id.'DESCRIPTIONhtml">html</label><br>';
			$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$id.'][DESCRIPTION]">'.htmlspecialcharsbx($row->arRes["DESCRIPTION"]).'</textarea>';
			$row->AddEditField("DESCRIPTION", $sHTML);
		}
	}
	else
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddInputField("NAME", false);
		$row->AddInputField("SORT", false);
		$row->AddInputField("CODE", false);
		$row->AddInputField("XML_ID", false);
	}

	$arActions = array();

	$arActions[] = array(
		"ICON" => "list",
		"TEXT" => htmlspecialcharsex($arIBlock["SECTIONS_NAME"]),
		"LINK" => $urlBuilder->getSectionListUrl(
			$id,
			array(
				"tree" => $useTree ? "Y" : "N",
			)
		),
		"DEFAULT" => "Y",
	);

	$arActions[] = array(
		"ICON" => "list",
		"TEXT" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
		"LINK" => $urlBuilder->getElementListUrl(
			$id,
			array(
				'INCLUDE_SUBSECTIONS' => 'Y',
			)
		)
	);

	if (isset($arSectionOps[$id]) && isset($arSectionOps[$id]["section_edit"]))
	{
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => GetMessage("IBSEC_A_CHANGE"),
			"LINK" => $urlBuilder->getSectionDetailUrl(
				$id,
				array(
					'find_section_section' => $find_section_section,
					'from' => 'iblock_section_admin',
				)
			)
		);
		if ($useSectionTranslit)
		{
			$arActions[] = array(
				"TEXT" => GetMessage('IBSEC_A_CODE_TRANSLIT'),
				"TITLE" => GetMessage('IBSEC_A_CODE_TRANSLIT_SECTION_TITLE'),
				"ACTION" => "if(confirm('".GetMessageJS("IBSEC_A_CODE_TRANSLIT_SECTION_CONFIRM")."')) ".$lAdmin->ActionDoGroup($id, ActionType::CODE_TRANSLIT, $sThisSectionUrl),
				"ONCLICK" => ""
			);
		}
	}

	if (isset($arSectionOps[$id]) && isset($arSectionOps[$id]["section_delete"]))
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("IBSEC_A_DELETE"),
			"ACTION" => "if(confirm('".GetMessageJS("IBSEC_A_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($id, ActionType::DELETE, $sThisSectionUrl),
		);

	$row->AddActions($arActions);
}

$actionList = [];
foreach ($arSectionOps as $arOps)
{
	if (isset($arOps["section_delete"]))
	{
		$actionList[] = ActionType::DELETE;
		break;
	}
}
$productEdit = false;
if ($useCatalog)
{
	foreach ($arSectionOps as $arOps)
	{
		if (
			isset($arOps["element_edit"])
			&& isset($arOps["element_edit_price"])
		)
		{
			$productEdit = true;
			break;
		}
	}
}

foreach ($arSectionOps as $arOps)
{
	if (isset($arOps["section_edit"]))
	{
		$actionList[] = ActionType::EDIT;
		$actionList[] = ActionType::SELECT_ALL;
		$actionList[] = ActionType::ACTIVATE;
		$actionList[] = ActionType::DEACTIVATE;
		if ($useSectionTranslit)
		{
			$actionList[ActionType::CODE_TRANSLIT] = [
				'CONFIRM_MESSAGE' => GetMessage('IBSEC_A_CODE_TRANSLIT_SECTION_CONFIRM_MULTI')
			];
		}
		if ($useCatalog && $productEdit)
		{
			$actionList[] = Catalog\Grid\ProductAction::SET_FIELD;
		}
		break;
	}
}
$lAdmin->AddGroupActionTable($panelAction->getList($actionList));
unset($actionList);

$aContext = array();
if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_section_bind"))
{
	$aContext[] = array(
		"TEXT" => htmlspecialcharsbx($arIBlock["SECTION_ADD"]),
		"LINK" => $urlBuilder->getSectionDetailUrl(
			null,
			array(
				'find_section_section' => $find_section_section,
				'IBLOCK_SECTION_ID' => $find_section_section,
				'from' => 'iblock_section_admin',
			)
		),
		"TITLE" => GetMessage("IBSEC_A_SECTADD_PRESS")
	);
}

if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_element_bind"))
{
	if ($catalogIncluded)
	{
		CCatalogAdminTools::setProductFormParams();
		$arCatalogBtns = CCatalogAdminTools::getIBlockElementMenu(
			$IBLOCK_ID,
			$arCatalog,
			array(
				'find_section_section' => $find_section_section,
				'IBLOCK_SECTION_ID' => $find_section_section,
				'from' => 'iblock_section_admin'
			),
			$urlBuilder
		);
		if (!empty($arCatalogBtns))
			$aContext = array_merge($aContext, $arCatalogBtns);
	}
	else
	{
		$aContext[] = array(
			"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENT_ADD"]),
			"LINK" => $urlBuilder->getElementDetailUrl(
				0,
				array(
					'find_section_section' => $find_section_section,
					'IBLOCK_SECTION_ID' => $find_section_section,
					'from' => 'iblock_section_admin',
				)
			),
			"TITLE" => GetMessage("IBSEC_A_ADDEL_TITLE")
		);
	}
}

$aContext[] = array(
	"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENTS_NAME"]),
	"LINK" => $urlBuilder->getElementListUrl($parent_section_id),
	"TITLE" => GetMessage("IBSEC_A_LISTEL_TITLE")
);
if ($urlBuilder->getId() == 'IBLOCK')
{
	if ($useTree)
		$aContext[] = array(
			"TEXT" => GetMessage("IBSEC_A_NOT_TREE"),
			"LINK" => $urlBuilder->getSectionListUrl(
				$parent_section_id,
				array('tree' => 'N')
			),
			"TITLE" => GetMessage("IBSEC_A_NOT_TREE_TITLE")
		);
	else
		$aContext[] = array(
			"TEXT" => GetMessage("IBSEC_A_TREE"),
			"LINK" => $urlBuilder->getSectionListUrl(
				$parent_section_id,
				array('tree' => 'Y')
			),
			"TITLE" => GetMessage("IBSEC_A_TREE_TITLE")
		);
}

$lAdmin->setContextSettings(array("pagePath" => $pageConfig['CONTEXT_PATH']));
$excelExport = ((string)Main\Config\Option::get("iblock", "excel_export_rights") == "Y"
	? CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_export")
	: true
);
$lAdmin->AddAdminContextMenu(
	$aContext,
	$excelExport
);

if ($pageConfig['SHOW_NAVCHAIN'])
{
	$chain = $lAdmin->CreateChain();
	if ($pageConfig['NAVCHAIN_ROOT'])
	{
		$sSectionUrl = $urlBuilder->getSectionListUrl(
			0,
			array(
				'tree' => ($useTree ? 'Y' : 'N')
			)
		);
		$chain->AddItem(array(
			"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
			"LINK" => $sSectionUrl,
			"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
		));
	}
	if ($parent_section_id > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $parent_section_id, array('ID', 'NAME'), true);
		foreach ($nav as $ar_nav)
		{
			$sSectionUrl = $urlBuilder->getSectionListUrl(
				(int)$ar_nav['ID'],
				array(
					'tree' => ($useTree ? 'Y' : 'N')
				)
			);
			$chain->AddItem(array(
				"TEXT" => htmlspecialcharsEx($ar_nav["NAME"]),
				"LINK" => htmlspecialcharsbx($sSectionUrl),
				"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
			));
		}
		unset($sSectionUrl, $ar_nav, $nav);
	}
	$lAdmin->ShowChain($chain);
	unset($chain);
}

$lAdmin->CheckListMode();

if(defined("CATALOG_PRODUCT"))
{
	$sSectionName = $arIBlock["SECTIONS_NAME"];
	if($parent_section_id > 0)
	{
		$rsSection = CIBlockSection::GetList(array(), array("=ID" => $parent_section_id), false, array("NAME"));
		$arSection = $rsSection->GetNext();
		if($arSection)
			$sSectionName = $arSection["NAME"];
	}

	$APPLICATION->SetTitle($arIBlock["NAME"].": ".$sSectionName);
}
else
{
	$APPLICATION->SetTitle($arIBlock["NAME"].": ".$arIBlock["SECTIONS_NAME"]);
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList(array("default_action" => true));
if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'iblock_edit') && !defined("CATALOG_PRODUCT") && !$publicMode)
{
	echo
		BeginNote(),
		GetMessage("IBSEC_A_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialcharsbx($type).'&amp;lang='.LANGUAGE_ID.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array(
			"find_section_section" => $find_section_section,
		))).'">',
		GetMessage("IBSEC_A_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");