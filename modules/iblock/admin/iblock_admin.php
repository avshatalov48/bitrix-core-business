<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
if($arIBTYPE===false)
	LocalRedirect("/bitrix/admin/iblock_type_admin.php?lang=".LANGUAGE_ID);

$bBizproc = CModule::IncludeModule("bizproc");
$bWorkflow = CModule::IncludeModule("workflow");

if($_REQUEST["admin"] == "Y")
	$sTableID = "tbl_iblock_admin_".md5($type);
else
	$sTableID = "tbl_iblock_".md5($type);

$oSort = new CAdminUiSorting($sTableID, "TIMESTAMP_X", "desc");
$arOrder = (mb_strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminUiList($sTableID, $oSort);

$arFilterFields = Array(
	"find_id",
	"find_name",
	"find_lang",
	"find_active",
	"find_code",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"ID" => $find_id,
	"ACTIVE" => $find_active,
	"LID" => $find_lang,
	"?CODE" => $find_code,
	"?NAME" => $find_name,
	"TYPE" => $type,
	"MIN_PERMISSION" => "U",
	"CNT_ALL" => "Y",
);

/* Prepare data for new filter */
$queryObject = CLang::getList($b = "sort", $o = "asc", array("VISIBLE" => "Y"));
$listLang = array();
while($lang = $queryObject->getNext())
	$listLang[$lang["LID"]] = $lang["NAME"];

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("IBLOCK_ADM_FILT_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true
	),
	array(
		"id" => "LID",
		"name" => GetMessage("IBLOCK_ADM_FILT_SITE"),
		"type" => "list",
		"items" => $listLang,
		"filterable" => ""
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("IBLOCK_ADM_FILT_ACT"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		),
		"filterable" => ""
	),
	array(
		"id" => "CODE",
		"name" => GetMessage("IBLOCK_FIELD_CODE"),
		"filterable" => "?"
	),
);

$arFilter = array(
	"TYPE" => $type,
	"MIN_PERMISSION" => "U",
	"CNT_ALL" => "Y",
);
$lAdmin->AddFilter($filterFields, $arFilter);

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID => $postFields)
	{
		$DB->StartTransaction();
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		if(!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit"))
			continue;

		$allowedFields = array(
			"NAME",
			"SORT",
			"ACTIVE",
			"LIST_PAGE_URL",
			"DETAIL_PAGE_URL",
			"CANONICAL_PAGE_URL",
			"CODE",
			"INDEX_ELEMENT",
			"WORKFLOW",
		);
		$arFields = array();
		foreach ($allowedFields as $fieldId)
		{
			if (array_key_exists($fieldId, $postFields))
				$arFields[$fieldId] = $postFields[$fieldId];
		}

		$ib = new CIBlock;
		if(!$ib->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage("IBLOCK_ADM_SAVE_ERROR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$ib->LAST_ERROR)), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($lAdmin->IsGroupActionToAll())
	{
		$rsIBlocks = CIBlock::GetList($arOrder, $arFilter);
		while($arRes = $rsIBlocks->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			if(!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_delete"))
				break;
			@set_time_limit(0);
			$DB->StartTransaction();
			$rsIBlock = CIBlock::GetByID($ID);
			$arIBlock = $rsIBlock->GetNext();
			if(!CIBlock::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_DELETE_ERROR"), $ID);
			}
			else
			{
				if(COption::GetOptionString("iblock", "event_log_iblock", "N") === "Y")
				{
					$res_log["NAME"] = $arIBlock["NAME"];
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_DELETE",
						"iblock",
						$ID,
						serialize($res_log)
					);
				}
			}
			$DB->Commit();
			break;
		case "activate":
		case "deactivate":
			if(!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_delete"))
				break;
			$ob = new CIBlock();
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
				$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_UPD_ERROR").$ob->LAST_ERROR, $ID);

			break;
		}
	}
}

$arHeader = array(
	array(
		"id"=>"NAME",
		"content"=>GetMessage("IBLOCK_ADM_NAME"),
		"sort"=>"name",
		"default"=>true,
	),
	array(
		"id"=>"SORT",
		"content"=>GetMessage("IBLOCK_ADM_SORT"),
		"sort"=>"sort",
		"default"=>true,
		"align"=>"right",
	),
	array(
		"id"=>"ACTIVE",
		"content"=>GetMessage("IBLOCK_ADM_ACTIVE"),
		"sort"=>"active",
		"default"=>true,
		"align"=>"center",
	),
	array(
		"id"=>"CODE",
		"content"=>GetMessage("IBLOCK_FIELD_CODE"),
		"sort"=>"code",
	),
	array(
		"id"=>"LIST_PAGE_URL",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_LIST_URL"),
	),
	array(
		"id"=>"DETAIL_PAGE_URL",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_DETAIL_URL"),
	),
	array(
		"id"=>"CANONICAL_PAGE_URL",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_CANONICAL_PAGE_URL"),
	),
	array(
		"id"=>"ELEMENT_CNT",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_EL"),
		"default"=>true,
		"align"=>"right",
	),
);

if($arIBTYPE["SECTIONS"]=="Y")
	$arHeader[] = array(
		"id"=>"SECTION_CNT",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_SECT"),
		"default"=>true,
		"align"=>"right",
	);

$arHeader[] = array(
	"id"=>"LID",
	"content"=>GetMessage("IBLOCK_ADM_LANG"),
	"sort"=>"lid",
	"default"=>true,
	"align"=>"left",
);
$arHeader[] = array(
	"id"=>"INDEX_ELEMENT",
	"content"=>GetMessage("IBLOCK_ADM_HEADER_TOINDEX"),
);
if($bWorkflow)
	$arHeader[] = array(
		"id"=>"WORKFLOW",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_WORKFLOW"),
	);
$arHeader[] = array(
	"id"=>"TIMESTAMP_X",
	"content"=>GetMessage("IBLOCK_ADM_TIMESTAMP"),
	"sort"=>"timestamp_x",
	"default"=>true,
);
$arHeader[] = array(
	"id"=>"ID",
	"content"=>"ID",
	"sort"=>"id",
	"default"=>true,
	"align"=>"right",
);
if($bBizproc && IsModuleInstalled("bizprocdesigner"))
	$arHeader[] = array(
		"id"=>"WORKFLOW_TEMPLATES",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_BIZPROC"),
		"default"=>true,
		"align"=>"right",
	);

$lAdmin->AddHeaders($arHeader);

$rsIBlocks = CIBlock::GetList($arOrder, $arFilter, false);
$rsIBlocks = new CAdminUiResult($rsIBlocks, $sTableID);
$rsIBlocks->NavStart();

$lAdmin->SetNavigationParams($rsIBlocks);

while($dbrs = $rsIBlocks->NavNext(true, "f_"))
{
	if(
		$_REQUEST["admin"] == "Y"
		&& CIBlockRights::UserHasRightTo($f_ID, $f_ID, "iblock_edit")
	)
	{
		$row =& $lAdmin->AddRow($f_ID, $dbrs, 'iblock_edit.php?ID='.$f_ID.'&type='.htmlspecialcharsbx($type).'&lang='.LANGUAGE_ID.'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N"), GetMessage("IBLOCK_ADM_TO_EDIT"));
	}
	else
	{
		if($arIBTYPE["SECTIONS"]=="Y")
			$row =& $lAdmin->AddRow($f_ID, $dbrs, CIBlock::GetAdminSectionListLink($f_ID, array('find_section_section'=>0)), GetMessage("IBLOCK_ADM_TO_EL_LIST"));
		else
			$row =& $lAdmin->AddRow($f_ID, $dbrs, CIBlock::GetAdminElementListLink($f_ID, array('find_section_section'=>-1)), GetMessage("IBLOCK_ADM_TO_EL_LIST"));
	}

	if($f_SECTIONS_NAME == '')
		$f_SECTIONS_NAME = $arIBTYPE["SECTION_NAME"]? htmlspecialcharsbx($arIBTYPE["SECTION_NAME"]): GetMessage("IBLOCK_ADM_SECTIONS");
	if(!$f_ELEMENTS_NAME)
		$f_ELEMENTS_NAME = $arIBTYPE["ELEMENT_NAME"]? htmlspecialcharsbx($arIBTYPE["ELEMENT_NAME"]): GetMessage("IBLOCK_ADM_ELEMENTS");

	$f_LID = '';
	$db_LID = CIBlock::GetSite($f_ID);
	while($ar_LID = $db_LID->Fetch())
		$f_LID .= ($f_LID!=""?" / ":"").htmlspecialcharsbx($ar_LID["LID"]);

	$row->AddViewField("LID", $f_LID);
	if(
		$_REQUEST["admin"] == "Y"
		&& CIBlockRights::UserHasRightTo($f_ID, $f_ID, "iblock_edit")
	)
	{
		$row->AddViewField("ID", $f_ID);

		$row->AddInputField("NAME", array("size"=>"35"));
		$row->AddViewField("NAME", '<a href="iblock_edit.php?ID='.$f_ID.'&type='.htmlspecialcharsbx($type).'&lang='.LANGUAGE_ID.'&admin='.($_REQUEST["admin"]=="Y"? "Y": "N").'" title="'.GetMessage("IBLOCK_ADM_TO_EDIT").'">'.$f_NAME.'</a>');

		$row->AddInputField("SORT", array("size"=>"3"));
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("CODE");
		$row->AddInputField("LIST_PAGE_URL");
		$row->AddInputField("DETAIL_PAGE_URL");
		$row->AddInputField("CANONICAL_PAGE_URL");
		$row->AddCheckField("INDEX_ELEMENT");
		if($bWorkflow)
			$row->AddCheckField("WORKFLOW");
	}
	else
	{
		if($arIBTYPE["SECTIONS"]=="Y")
			$row->AddViewField("NAME", '<a href="'.htmlspecialcharsbx(CIBlock::GetAdminSectionListLink($f_ID , array('find_section_section'=>0))).'" title="'.GetMessage("IBLOCK_ADM_TO_SECTLIST").'">'.$f_NAME.'</a>');
		else
			$row->AddViewField("NAME", '<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementListLink($f_ID , array('find_section_section'=>-1))).'" title="'.GetMessage("IBLOCK_ADM_TO_EL_LIST").'">'.$f_NAME.'</a>');
		$row->AddCheckField("ACTIVE", false);
		$row->AddCheckField("INDEX_ELEMENT", false);
		if($bWorkflow)
			$row->AddCheckField("WORKFLOW", false);
	}

	if(in_array("ELEMENT_CNT", $lAdmin->GetVisibleHeaderColumns()))
	{
		$f_ELEMENT_CNT = CIBlock::GetElementCount($f_ID);
		$row->AddViewField("ELEMENT_CNT", '<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementListLink($f_ID, array('find_section_section'=>-1))).'" title="'.GetMessage("IBLOCK_ADM_TO_ELLIST").'">'.$f_ELEMENT_CNT.'</a>');
	}

	if($arIBTYPE["SECTIONS"]=="Y" && in_array("SECTION_CNT", $lAdmin->GetVisibleHeaderColumns()))
		$row->AddViewField("SECTION_CNT", '<a href="'.htmlspecialcharsbx(CIBlock::GetAdminSectionListLink($f_ID, array())).'" title="'.GetMessage("IBLOCK_ADM_TO_SECTLIST").'">'.intval(CIBlockSection::GetCount(array("IBLOCK_ID"=>$f_ID))).'</a>');

	if(
		$bBizproc
		&& $dbrs["BIZPROC"] == "Y"
		&& in_array("WORKFLOW_TEMPLATES", $lAdmin->GetVisibleHeaderColumns())
		&& IsModuleInstalled("bizprocdesigner")
	)
	{
		$cnt = CBPDocument::GetNumberOfWorkflowTemplatesForDocumentType(
			array("iblock", "CIBlockDocument", "iblock_".$f_ID)
		);
		$row->AddViewField("WORKFLOW_TEMPLATES", '<a href="/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_'.$f_ID.'&lang='.LANGUAGE_ID.'&back_url_list='.urlencode($APPLICATION->GetCurPageParam("", array())).'">'.$cnt.'</a>');
	}

	$arActions = array();

	if(
		$_REQUEST["admin"] == "Y"
		&& CIBlockRights::UserHasRightTo($f_ID, $f_ID, "iblock_edit")
	)
	{
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => $_REQUEST["admin"]=="Y",
			"ACTION" => $lAdmin->ActionRedirect("iblock_edit.php?ID=".$f_ID."&type=".urlencode($type)."&lang=".LANGUAGE_ID."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N")),
		);
		$arActions[] = array(
			"ICON" => "list",
			"TEXT" => GetMessage("IBLOCK_ADM_MENU_PROPERTIES"),
			"ACTION" => $lAdmin->ActionRedirect("iblock_property_admin.php?IBLOCK_ID=".$f_ID."&lang=".LANGUAGE_ID.($_REQUEST["admin"]=="Y"? "&admin=Y": "&admin=N")),
		);
	}

	if(
		$bBizproc
		&& $dbrs["BIZPROC"] == "Y"
		&& CIBlockRights::UserHasRightTo($f_ID, $f_ID, "iblock_edit")
		&& IsModuleInstalled("bizprocdesigner")
	)
	{
		$arActions[] = array(
			"ICON"=>"",
			"TEXT"=>GetMessage("IBLOCK_ADM_MENU_BIZPROC"),
			"ACTION"=>"window.location='/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_".$f_ID."&lang=".LANGUAGE_ID.'&back_url_list='.urlencode($APPLICATION->GetCurPageParam("", array()))."';"
		);
	}

	if(
		$_REQUEST["admin"] == "Y"
		&& CIBlockRights::UserHasRightTo($f_ID, $f_ID, "iblock_delete")
	)
	{
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION"=>"if(confirm('".GetMessageJS("IBLOCK_ADM_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "&type=".htmlspecialcharsbx($type)."&lang=".LANGUAGE_ID."&admin=".($_REQUEST["admin"]=="Y"? "Y": "N")),
		);
	}

	if(count($arActions))
		$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsIBlocks->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if($USER->IsAdmin() && ($_REQUEST["admin"] == "Y"))
{
	$aContext = array(
		array(
			"ICON"=>"btn_new",
			"TEXT"=>GetMessage("IBLOCK_ADM_TO_ADDIBLOCK"),
			"LINK"=>"iblock_edit.php?lang=".LANGUAGE_ID."&admin=Y&type=".urlencode($type),
			"TITLE"=>GetMessage("IBLOCK_ADM_TO_ADDIBLOCK_TITLE")
		),
	);

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable(Array(
		"edit"=>true,
		"delete"=>true,
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		));
}
else
{
	$lAdmin->AddAdminContextMenu(array());
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("IBLOCK_ADM_TITLE", array("#IBLOCK_TYPE#" => $arIBTYPE["~NAME"])));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

if ($_REQUEST["admin"]!="Y"):
	echo	BeginNote(),
	GetMessage("IBLOCK_ADM_MANAGE_HINT"),
		' <a href="iblock_admin.php?type='.htmlspecialcharsbx($type).'&amp;lang='.LANGUAGE_ID.'&amp;admin=Y">',
	GetMessage("IBLOCK_ADM_MANAGE_HINT_HREF"),
	'</a>.',
	EndNote();
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");