<?
/** @global CMain $APPLICATION */
/** @global $DB CDatabase */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

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
$arOrder = (strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminList($sTableID, $oSort);

if($_GET["tree"]=="Y")
{
	$lAdmin->AddVisibleHeaderColumn("DEPTH_LEVEL");
}


$arFilterFields = Array(
	"find_section_id",
	"find_section_timestamp_1",
	"find_section_timestamp_2",
	"find_section_modified_by",
	"find_section_date_create_1",
	"find_section_date_create_2",
	"find_section_created_by",
	"find_section_name",
	"find_section_active",
	"find_section_section",
	"find_section_code",
	"find_section_external_id"
);
$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);

//We have to handle current section in a special way
$section_id = strlen($find_section_section) > 0? intval($find_section_section): "";
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;

//This is all parameters needed for proper navigation
$sThisSectionUrl = '&type='.urlencode($type).'&lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.$find_section_section;

$arFilter = array(
	"IBLOCK_ID"	=> $IBLOCK_ID,
	"?NAME"		=> $find_section_name,
	"SECTION_ID"	=> $find_section_section,
	"ID"		=> $find_section_id,
	">=TIMESTAMP_X"	=> $find_section_timestamp_1,
	"MODIFIED_BY"	=> $find_section_modified_user_id? $find_section_modified_user_id: $find_section_modified_by,
	">=DATE_CREATE"	=> $find_section_date_create_1,
	"CREATED_BY"	=> $find_section_created_user_id? $find_section_created_user_id: $find_section_created_by,
	"ACTIVE"	=> $find_section_active,
	"CODE"		=> $find_section_code,
	"EXTERNAL_ID"	=> $find_section_external_id,
);
if(!empty($find_section_timestamp_2))
	$arFilter["<=TIMESTAMP_X"] = CIBlock::isShortDate($find_section_timestamp_2)? ConvertTimeStamp(AddTime(MakeTimeStamp($find_section_timestamp_2), 1, "D"), "FULL"): $find_section_timestamp_2;
if(!empty($find_section_date_create_2))
	$arFilter["<=DATE_CREATE"] = CIBlock::isShortDate($find_section_date_create_2)? ConvertTimeStamp(AddTime(MakeTimeStamp($find_section_date_create_2), 1, "D"), "FULL"): $find_section_date_create_2;

$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);

if($find_section_section === "")
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
	foreach($FIELDS as $ID=>$arFields)
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
	if ($_REQUEST['action_target'] == 'selected')
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
	$arNavParams = array("nPageSize"=>CAdminResult::GetNavSize($sTableID));

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

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(htmlspecialcharsbx($arIBlock["SECTIONS_NAME"])));
$arRows = array();

while ($arRes = $rsData->NavNext(true, "f_"))
{
	$el_list_url = htmlspecialcharsbx(CIBlock::GetAdminElementListLink($IBLOCK_ID, array(
		'find_section_section' => $f_ID,
	)));
	$el_add_url = htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($IBLOCK_ID, 0, array(
		'IBLOCK_SECTION_ID' => $arRes["ID"],
		'from' => 'iblock_section_admin_inc',
		'find_section_section' => $find_section_section,
	)));
	$sec_list_url = htmlspecialcharsbx(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array(
		'find_section_section' => $f_ID,
		'tree' => $_GET["tree"] == "Y"? 'Y': null,
	)));
	$sec_add_url = htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($IBLOCK_ID, 0, array(
		'IBLOCK_SECTION_ID' => $arRes["ID"],
		'from' => 'iblock_section_admin',
		'find_section_section' => $find_section_section,
	)));
	$edit_url = htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($IBLOCK_ID, $arRes["ID"], array(
		'from' => 'iblock_section_admin',
		'find_section_section' => $find_section_section,
	)));

	$arRows[$f_ID] = $row = $lAdmin->AddRow($f_ID, $arRes, $sec_list_url, GetMessage("IBSEC_A_LIST"));
	$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);

	$row->AddViewField("ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBSEC_A_EDIT").'">'.$f_ID.'</a>');
	$row->AddViewField("NAME", '<a href="'.$sec_list_url.'" '.($_GET["tree"] == "Y" ? 'style="padding-left:'.(($f_DEPTH_LEVEL - 1) * 22).'px"' : '').' class="adm-list-table-icon-link" title="'.GetMessage("IBSEC_A_LIST").'"><span class="adm-submenu-item-link-icon adm-list-table-icon iblock-section-icon"></span><span class="adm-list-table-link">'.$f_NAME.'</span></a>');
	if (array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
		$row->AddViewField("ELEMENT_CNT", '<a href="'.$el_list_url.'&find_el_subsections=N" title="'.GetMessage("IBSEC_A_ELLIST").'">'.$f_ELEMENT_CNT.'</a>('.'<a href="'.$el_list_url.'&find_el_subsections=Y" title="'.GetMessage("IBSEC_A_ELLIST_TITLE").'">'.IntVal(CIBlockSection::GetSectionElementsCount($f_ID, array(
			"CNT_ALL" => "Y",
		))).'</a>) [<a href="'.$el_add_url.'" title="'.GetMessage("IBSEC_A_ELADD_TITLE").'">+</a>]');

	if (array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
	{
		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
			"SECTION_ID" => $f_ID,
		);
		$row->AddViewField("SECTION_CNT", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionAjaxReload($sec_list_url).'; return false;" title="'.GetMessage("IBSEC_A_LIST").'">'.IntVal(CIBlockSection::GetCount($arFilter)).'</a> [<a href="'.$sec_add_url.'" title="'.GetMessage("IBSEC_A_SECTADD_TITLE").'">+</a>]');
	}
	if (array_key_exists("MODIFIED_BY", $arVisibleColumnsMap))
	{
		if ($html = GetUserProfileLink($f_MODIFIED_BY, GetMessage("IBSEC_A_USERINFO")))
			$row->AddViewField("MODIFIED_BY", $html);
	}
	if (array_key_exists("CREATED_BY", $arVisibleColumnsMap))
	{
		if ($html = GetUserProfileLink($f_CREATED_BY, GetMessage("IBSEC_A_USERINFO")))
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
			'find_section_section' => $id,
			'tree' => $_GET["tree"] == "Y"? 'Y' : null,
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
				'from' => 'iblock_section_admin',
				'find_section_section' => $find_section_section,
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

$lAdmin->AddFooter(array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsData->SelectedRowsCount(),
	),
	array(
		"counter" => true,
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => "0",
	),
));
$arGroupActions = array();
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
				'IBLOCK_SECTION_ID' => $find_section_section,
				'find_section_section' => $find_section_section,
				'from' => 'iblock_section_admin'
			)
		);
		if (!empty($arCatalogBtns))
			$aContext = $arCatalogBtns;
	}
	if (empty($aContext))
	{
		$aContext[] = array(
			"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENT_ADD"]),
			"ICON" => "btn_new",
			"LINK" => CIBlock::GetAdminElementEditLink($IBLOCK_ID, 0, array(
				'IBLOCK_SECTION_ID' => $find_section_section,
				'find_section_section' => $find_section_section,
				'from' => 'iblock_section_admin'
			)),
			"TITLE" => GetMessage("IBSEC_A_ADDEL_TITLE")
		);
	}
}

if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_section_bind"))
{
	$aContext[] = array(
		"TEXT" => htmlspecialcharsbx($arIBlock["SECTION_ADD"]),
		"ICON" => ($boolBtnNew ? "" : "btn_new"),
		"LINK" => CIBlock::GetAdminSectionEditLink($IBLOCK_ID, 0, array(
			'IBLOCK_SECTION_ID' => $find_section_section,
			'find_section_section' => $find_section_section,
			'from' => 'iblock_section_admin'
		)),
		"TITLE" => GetMessage("IBSEC_A_SECTADD_PRESS")
	);
}

if (defined("CATALOG_PRODUCT"))
{
	if($find_section_section > 0)
	{
		$rsParent = CIBlockSection::GetList(array(), array("=ID" => $find_section_section), false, array("ID", "IBLOCK_SECTION_ID"));
		if($arParent = $rsParent->Fetch())
		{
			$aContext[] = Array(
				"TEXT" => GetMessage("IBSEC_A_UP"),
				"LINK" => CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($arParent["IBLOCK_SECTION_ID"]))),
				"TITLE" => GetMessage("IBSEC_A_UP_TITLE")
			);
		}
	}
}
else
{
	$aContext[] = array(
		"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENTS_NAME"]),
		"LINK" => htmlspecialcharsbx(CIBlock::GetAdminElementListLink($IBLOCK_ID, array(
			'find_section_section' => $find_section_section
		))),
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

$lAdmin->AddAdminContextMenu($aContext);

if(!defined("CATALOG_PRODUCT"))
{
	$chain = $lAdmin->CreateChain();

	$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0));
	if($_GET["tree"]=="Y")
		$sSectionUrl .= '&tree=Y';
	$chain->AddItem(array(
		"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
		"LINK" => htmlspecialcharsbx($sSectionUrl),
		"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
	));
	if($find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'));
		while($ar_nav = $nav->GetNext())
		{
			$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"]));
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
else
{
	$chain = $lAdmin->CreateChain();
	if($find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'));
		while($ar_nav = $nav->GetNext())
		{
			$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"], 'catalog' => null));
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
?>
<form method="GET" name="find_section_form" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array(
	"parent" => GetMessage("IBSEC_A_PARENT"),
	"id" => GetMessage("IBSEC_A_ID"),
	"timestamp_x" => GetMessage("IBSEC_A_TIMESTAMP"),
	"modified_by" => GetMessage("IBSEC_A_MODIFIED_BY"),
	"date_create" => GetMessage("IBSEC_A_DATE_CREATE"),
	"created_by" => GetMessage("IBSEC_A_CREATED_BY"),
	"code" => GetMessage("IBSEC_A_CODE"),
	"xml_id" => GetMessage("IBSEC_A_XML_ID"),
	"active" => GetMessage("IBSEC_A_ACTIVE")
);
$USER_FIELD_MANAGER->AddFindFields($entity_id, $arFindFields);

$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);

$oFilter->Begin();
?>
	<tr>
		<td><b><?echo GetMessage("IBSEC_A_NAME")?>:</b></td>
		<td><input type="text" name="find_section_name" value="<?echo htmlspecialcharsex($find_section_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_SECTION")?>:</td>
		<td>
			<select name="find_section_section" >
				<option value=""><?echo GetMessage("IBLOCK_ALL")?></option>
				<option value="0"<?if($find_section_section=="0")echo" selected"?>><?echo GetMessage("IBSEC_A_ROOT_SECTION")?></option>
				<?
				$bsections = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
				while($arSection = $bsections->GetNext()):
					?><option value="<?echo $arSection["ID"]?>"<?if($arSection["ID"]==$find_section_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $arSection["DEPTH_LEVEL"])?><?echo $arSection["NAME"]?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_ID")?>:</td>
		<td><input type="text" name="find_section_id" size="47" value="<?echo htmlspecialcharsbx($find_section_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_TIMESTAMP").":"?></td>
		<td><?echo CalendarPeriod("find_section_timestamp_1", htmlspecialcharsbx($find_section_timestamp_1), "find_section_timestamp_2", htmlspecialcharsbx($find_section_timestamp_2), "find_section_form", "Y")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_MODIFIED_BY")?>:</td>
		<td>
			<?echo FindUserID(
				"find_section_modified_by",
				($find_section_modified_user_id? $find_section_modified_user_id: $find_section_modified_by),
				"",
				"find_section_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_DATE_CREATE").":"?></td>
		<td><?echo CalendarPeriod("find_section_date_create_1", htmlspecialcharsex($find_section_date_create_1), "find_section_date_create_2", htmlspecialcharsex($find_section_date_create_2), "find_section_form", "Y")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_CREATED_BY")?>:</td>
		<td>
			<?echo FindUserID(
				"find_section_created_by",
				($find_section_created_user_id? $find_section_created_user_id: $find_section_created_by),
				"",
				"find_section_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_CODE")?>:</td>
		<td><input type="text" name="find_section_code" size="47" value="<?echo htmlspecialcharsbx($find_section_code)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_XML_ID")?>:</td>
		<td><input type="text" name="find_section_external_id" size="47" value="<?echo htmlspecialcharsbx($find_section_external_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBSEC_A_ACTIVE")?>:</td>
		<td>
			<select name="find_section_active" >
				<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_ALL'))?></option>
				<option value="Y"<?if($find_section_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?if($find_section_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>
<?
$USER_FIELD_MANAGER->AdminListShowFilter($entity_id);
$oFilter->Buttons(array(
	"table_id"=>$sTableID,
	"url"=>$APPLICATION->GetCurPage().'?type='.$type.'&IBLOCK_ID='.$IBLOCK_ID,
	"form"=>"find_section_form",
));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'iblock_edit') && !defined("CATALOG_PRODUCT"))
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