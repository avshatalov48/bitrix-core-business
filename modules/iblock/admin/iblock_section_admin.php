<?
/** @global CMain $APPLICATION */
/** @global $DB CDatabase */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

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

$entity_id = "IBLOCK_".$IBLOCK_ID."_SECTION";
$sTableID = "tbl_".(defined("CATALOG_PRODUCT")? "catalog": "iblock")."_section_".md5($type.".".$IBLOCK_ID);

if($_GET["tree"]=="Y")
{
	$by = "left_margin";
	$order = "asc";
}

$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");
global $by, $order;
$arOrder = (strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

if($_GET["tree"]=="Y")
{
	$lAdmin->AddVisibleHeaderColumn("DEPTH_LEVEL");
}

$sectionItems = array(
	"" => GetMessage("IBLOCK_ALL"),
	"0" => GetMessage("IBSEC_A_ROOT_SECTION"),
);
$sectionQueryObject = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
while($arSection = $sectionQueryObject->GetNext())
	$sectionItems[$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["~NAME"];

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
$section_id = strlen($find_section_section) > 0? intval($find_section_section): "";
$find_section_section = $section_id;

//This is all parameters needed for proper navigation
$sThisSectionUrl = '&type='.urlencode($type).'&lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$find_section_section;

$arFilter = $baseFilter = array("IBLOCK_ID" => $IBLOCK_ID);

$lAdmin->AddFilter($filterFields, $arFilter);

$USER_FIELD_MANAGER->AdminListAddFilterV2($entity_id, $arFilter, $sTableID, $filterFields);

if (!is_null($arFilter["SECTION_ID"]))
{
	$find_section_section = intval($arFilter["SECTION_ID"]);
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

if (intval($find_section_section) < 0 || strlen($find_section_section) <= 0)
{
	unset($arFilter["SECTION_ID"]);
}
elseif($_GET["tree"]=="Y")
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
	if (!empty($_REQUEST["action_all_rows_".$sTableID]) && $_REQUEST["action_all_rows_".$sTableID] === "Y")
	{
		$rsData = CIBlockSection::GetList($arOrder, $arFilter);
		while ($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		$ID = intval($ID);
		switch ($_REQUEST['action'])
		{
		case "delete":
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

		case "activate":
		case "deactivate":
			if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit"))
			{
				$ob = new CIBlockSection();
				$arFields = array(
					"ACTIVE" => ($_REQUEST['action'] == "activate" ? "Y" : "N"),
				);
				if (!$ob->Update($ID, $arFields))
					$lAdmin->AddGroupError(GetMessage("IBSEC_A_UPDERR").$ob->LAST_ERROR, $ID);
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
);
$USER_FIELD_MANAGER->AdminListAddHeaders($entity_id, $arHeaders);

if ($_GET["tree"] == "Y")
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

$arVisibleColumnsMap = array();
foreach($arVisibleColumns as $value)
	$arVisibleColumnsMap[$value] = true;

if($_REQUEST["mode"] == "excel")
	$arNavParams = false;
else
	$arNavParams = array("nPageSize"=>CAdminUiResult::GetNavSize($sTableID));

if (array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
{
	$arFilter["CNT_ALL"] = "Y";
	$arFilter["ELEMENT_SUBSECTIONS"] = "N";
	$rsData = CIBlockSection::GetList($arOrder, $arFilter, true, $arVisibleColumns, $arNavParams);
}
else
{
	$rsData = CIBlockSection::GetList($arOrder, $arFilter, false, $arVisibleColumns, $arNavParams);
}

$listElementScriptName = CIBlock::GetAdminElementListScriptName($IBLOCK_ID);
$listSectionScriptName = CIBlock::GetAdminSectionListScriptName($IBLOCK_ID);
$baseLink = ($publicMode ? $selfFolderUrl.$listSectionScriptName : $APPLICATION->GetCurPage());

$rsData = new CAdminUiResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->SetNavigationParams($rsData, array("BASE_LINK" => $baseLink));
$arRows = array();

while ($arRes = $rsData->NavNext(false))
{
	$el_list_url = $selfFolderUrl.CIBlock::GetAdminElementListLink($IBLOCK_ID, array(
		'find_section_section' => $arRes["ID"]
	));
	$el_add_url = htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminElementEditLink($IBLOCK_ID, 0, array(
		'find_section_section' => $arRes["ID"],
		'IBLOCK_SECTION_ID' => $arRes["ID"],
		'from' => 'iblock_section_admin_inc',
		"replace_script_name" => true
	)));
	$sec_list_url = htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminSectionListLink($IBLOCK_ID, array(
		'find_section_section' => $arRes["ID"],
		'tree' => $_GET["tree"] == "Y"? 'Y': null,
	)));
	$sec_add_url = htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminSectionEditLink($IBLOCK_ID, 0, array(
		'find_section_section' => $find_section_section,
		'IBLOCK_SECTION_ID' => $arRes["ID"],
		'from' => 'iblock_section_admin',
		"replace_script_name" => true
	)));
	$edit_url = htmlspecialcharsbx($selfFolderUrl.CIBlock::GetAdminSectionEditLink($IBLOCK_ID, $arRes["ID"], array(
		'find_section_section' => $find_section_section,
		'from' => 'iblock_section_admin',
		"replace_script_name" => true
	)));

	$el_list_url = \CHTTP::urlAddParams($el_list_url, array("SECTION_ID" => $arRes["ID"], "apply_filter" => "Y"));
	$sec_list_url = \CHTTP::urlAddParams($sec_list_url, array("SECTION_ID" => $arRes["ID"], "apply_filter" => "Y"));

	$arRows[$arRes["ID"]] = $row = $lAdmin->AddRow($arRes["ID"], $arRes, $sec_list_url, GetMessage("IBSEC_A_LIST"));
	$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);

	$row->AddViewField("ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBSEC_A_EDIT").'">'.$arRes["ID"].'</a>');
	$row->AddViewField("NAME", '<a href="'.CHTTP::URN2URI($sec_list_url).'" '.($_GET["tree"] == "Y" ? 'style="padding-left:'.(($arRes["DEPTH_LEVEL"] - 1) * 22).'px"' : '').' class="adm-list-table-icon-link" title="'.GetMessage("IBSEC_A_LIST").'"><span class="adm-submenu-item-link-icon adm-list-table-icon iblock-section-icon"></span><span class="adm-list-table-link">'.htmlspecialcharsbx($arRes["NAME"]).'</span></a>');
	if (array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
		$row->AddViewField("ELEMENT_CNT", '<a href="'.CHTTP::URN2URI($el_list_url).'&find_el_subsections=N" title="'.GetMessage("IBSEC_A_ELLIST").'">'.$arRes["ELEMENT_CNT"].'</a>('.'<a href="'.CHTTP::URN2URI($el_list_url).'&find_el_subsections=Y" title="'.GetMessage("IBSEC_A_ELLIST_TITLE").'">'.IntVal(CIBlockSection::GetSectionElementsCount($arRes["ID"], array(
			"CNT_ALL" => "Y",
		))).'</a>) [<a href="'.$el_add_url.'" title="'.GetMessage("IBSEC_A_ELADD_TITLE").'">+</a>]');

	if (array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
	{
		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
			"SECTION_ID" => $arRes["ID"],
		);
		$row->AddViewField("SECTION_CNT", '<a href="'.CHTTP::URN2URI($sec_list_url).'" title="'.GetMessage("IBSEC_A_LIST").'">'.IntVal(CIBlockSection::GetCount($arFilter)).'</a> [<a href="'.$sec_add_url.'" title="'.GetMessage("IBSEC_A_SECTADD_TITLE").'">+</a>]');
	}
	if (array_key_exists("MODIFIED_BY", $arVisibleColumnsMap))
	{
		if ($html = GetUserProfileLink($arRes["MODIFIED_BY"], GetMessage("IBSEC_A_USERINFO")))
			$row->AddViewField("MODIFIED_BY", $html);
	}
	if (array_key_exists("CREATED_BY", $arVisibleColumnsMap))
	{
		if ($html = GetUserProfileLink($arRes["CREATED_BY"], GetMessage("IBSEC_A_USERINFO")))
			$row->AddViewField("CREATED_BY", $html);
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
		"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array(
			"find_section_section" => 0,
			"tree" => $_GET["tree"] == "Y"? "Y" : null,
			"SECTION_ID" => $id,
			"apply_filter" => "Y"
		))),
		"DEFAULT" => "Y",
	);

	if(!defined("CATALOG_PRODUCT"))
	{
		$arActions[] = array(
			"ICON" => "list",
			"TEXT" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
			"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementListLink($IBLOCK_ID, array(
				'find_section_section' => $id,
				'SECTION_ID' => $id,
				'apply_filter' => 'y',
				'tree' => $_GET["tree"] == "Y"? 'Y' : null,
				'find_el_subsections' => 'N',
			))),
		);
	}

	if (isset($arSectionOps[$id]) && isset($arSectionOps[$id]["section_edit"]))
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => GetMessage("IBSEC_A_CHANGE"),
			"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminSectionEditLink($IBLOCK_ID, $id, array(
				'find_section_section' => $find_section_section,
				'from' => 'iblock_section_admin',
				"replace_script_name" => true
			))),
		);

	if (isset($arSectionOps[$id]) && isset($arSectionOps[$id]["section_delete"]))
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("IBSEC_A_DELETE"),
			"ACTION" => "if(confirm('".GetMessageJS("IBSEC_A_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($id, "delete", $sThisSectionUrl),
		);

	$row->AddActions($arActions);
}

$arGroupActions = array(
	"edit" => GetMessage("MAIN_ADMIN_LIST_EDIT"),
	"for_all" => true
);
foreach ($arSectionOps as $id => $arOps)
{
	if (isset($arOps["section_delete"]))
	{
		$arGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");
		break;
	}
}
foreach ($arSectionOps as $id => $arOps)
{
	if (isset($arOps["section_edit"]))
	{
		$arGroupActions["activate"] = GetMessage("MAIN_ADMIN_LIST_ACTIVATE");
		$arGroupActions["deactivate"] = GetMessage("MAIN_ADMIN_LIST_DEACTIVATE");
		break;
	}
}
$lAdmin->AddGroupActionTable($arGroupActions);

$aContext = array();
$boolBtnNew = false;

if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_section_bind"))
{
	$aContext[] = array(
		"TEXT" => htmlspecialcharsbx($arIBlock["SECTION_ADD"]),
		"ICON" => ($boolBtnNew ? "" : "btn_new"),
		"LINK" => CIBlock::GetAdminSectionEditLink($IBLOCK_ID, 0, array(
			'find_section_section' => $find_section_section,
			'IBLOCK_SECTION_ID' => $find_section_section,
			'from' => 'iblock_section_admin',
			"replace_script_name" => true
		)),
		"TITLE" => GetMessage("IBSEC_A_SECTADD_PRESS")
	);
}

if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_element_bind"))
{
	$boolBtnNew = true;
	if (CModule::IncludeModule('catalog'))
	{
		CCatalogAdminTools::setProductFormParams();
		$arCatalogBtns = CCatalogAdminTools::getIBlockElementMenu(
			$IBLOCK_ID,
			$arCatalog,
			array(
				'find_section_section' => $find_section_section,
				'IBLOCK_SECTION_ID' => $find_section_section,
				'from' => 'iblock_section_admin',
				"replace_script_name" => true
			)
		);
		if (!empty($arCatalogBtns))
			$aContext = array_merge($aContext, $arCatalogBtns);
	}
	if (empty($aContext))
	{
		$aContext[] = array(
			"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENT_ADD"]),
			"ICON" => "btn_new",
			"LINK" => CIBlock::GetAdminElementEditLink($IBLOCK_ID, 0, array(
				'find_section_section' => $find_section_section,
				'IBLOCK_SECTION_ID' => $find_section_section,
				'from' => 'iblock_section_admin',
				"replace_script_name" => true
			)),
			"TITLE" => GetMessage("IBSEC_A_ADDEL_TITLE")
		);
	}
}

if (!defined("CATALOG_PRODUCT"))
{
	$aContext[] = array(
		"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENTS_NAME"]),
		"LINK" => CIBlock::GetAdminElementListLink($IBLOCK_ID, array(
			'find_section_section' => $find_section_section,
			'SECTION_ID' => $find_section_section,
			'apply_filter' => 'y',
		)),
		"TITLE" => GetMessage("IBSEC_A_LISTEL_TITLE")
	);
	if ($_GET["tree"] == "Y")
		$aContext[] = array(
			"TEXT" => GetMessage("IBSEC_A_NOT_TREE"),
			"LINK" => CIBlock::GetAdminSectionListLink($IBLOCK_ID, array(
				'find_section_section' => $find_section_section,
				'tree' => 'N'
			)),
			"TITLE" => GetMessage("IBSEC_A_NOT_TREE_TITLE")
		);
	else
		$aContext[] = array(
			"TEXT" => GetMessage("IBSEC_A_TREE"),
			"LINK" => CIBlock::GetAdminSectionListLink($IBLOCK_ID, array(
				'find_section_section' => $find_section_section,
				'tree' => 'Y'
			)),
			"TITLE" => GetMessage("IBSEC_A_TREE_TITLE")
		);
}

$pagePath = (defined("CATALOG_PRODUCT") ? "cat_section_admin.php" : "iblock_section_admin.php");
$pagePath = ($publicMode ? $selfFolderUrl.$pagePath : $APPLICATION->GetCurPage());
$lAdmin->setContextSettings(array("pagePath" => $pagePath));
$lAdmin->AddAdminContextMenu($aContext);

if(!defined("CATALOG_PRODUCT"))
{
	$chain = $lAdmin->CreateChain();

	$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array("find_section_section" => 0, "SECTION_ID" => 0, "apply_filter" => "y"));
	if($_GET["tree"]=="Y")
		$sSectionUrl .= '&tree=Y';
	$chain->AddItem(array(
		"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
		"LINK" => $sSectionUrl,
		"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
	));
	if($find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'));
		while($ar_nav = $nav->GetNext())
		{
			$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array("find_section_section"=> $ar_nav["ID"], "SECTION_ID" => $ar_nav["ID"], "apply_filter" => "y"));
			if($_GET["tree"]=="Y")
				$sSectionUrl .= '&tree=Y';
			$chain->AddItem(array(
				"TEXT" => $ar_nav["NAME"],
				"LINK" => $sSectionUrl,
				"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
			));
		}
	}
	$lAdmin->ShowChain($chain);
}
else
{
	$chain = $lAdmin->CreateChain();
	if($find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'));
		while($ar_nav = $nav->GetNext())
		{
			$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array("find_section_section"=> $ar_nav["ID"], "SECTION_ID" => $ar_nav["ID"], "apply_filter" => "y", 'catalog' => null));
			if($_GET["tree"]=="Y")
				$sSectionUrl .= '&tree=Y';
			$chain->AddItem(array(
				"TEXT" => $ar_nav["NAME"],
				"LINK" => htmlspecialcharsbx($sSectionUrl),
				"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
			));
		}
	}
	$lAdmin->ShowChain($chain);
}

$lAdmin->CheckListMode();

if(defined("CATALOG_PRODUCT"))
{
	$sSectionName = $arIBlock["SECTIONS_NAME"];
	if($find_section_section > 0)
	{
		$rsSection = CIBlockSection::GetList(array(), array("=ID" => $find_section_section), false, array("NAME"));
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
$lAdmin->DisplayList(array("default_action" => $sec_list_url));
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