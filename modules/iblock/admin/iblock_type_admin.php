<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);


// Data table ID
$sTableID = "tbl_iblock_type";

// Sorting init
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$arOrder = (mb_strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
// List init
$lAdmin = new CAdminList($sTableID, $oSort);

// Filters for list
$arFilterFields = array(
	"find_id",
	"find_name"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"ID" => $find_id,
	"NAME" => $find_name,
);

if($USER->IsAdmin())
{
	$rsData = CIBlockType::GetList($arOrder, $arFilter);
}
else
{
	$arTypesToShow = array();
	$rsIBlocks = CIBlock::GetList(array(), array("MIN_PERMISSION" => "X"));
	while($arIBlock = $rsIBlocks->Fetch())
		$arTypesToShow[$arIBlock["IBLOCK_TYPE_ID"]] = $arIBlock["IBLOCK_TYPE_ID"];

	if(empty($arTypesToShow))
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	else
		$arFilter["=ID"] = $arTypesToShow;
}

// Editing handling (rights check should be done!)
if($USER->IsAdmin() && $lAdmin->EditAction()) // Save button was pressed
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$obBlocktype = new CIBlockType;
		$res = $obBlocktype->Update($ID, $arFields);
		if(!$res)
		{
			$lAdmin->AddUpdateError(GetMessage("IBLOCK_TYPE_ADMIN_ERR_SAVE")." (&quot;".htmlspecialcharsbx($ID)."&quot;): ".$obBlocktype->LAST_ERROR, $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}
if($USER->IsAdmin() && ($arID = $lAdmin->GroupAction()))
{
	if ($lAdmin->IsGroupActionToAll())
	{
		$rsData = CIBlockType::GetList($arOrder, $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			$DB->StartTransaction();
			if(!CIBlockType::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("IBLOCK_TYPE_ADMIN_ERR_DEL")." (&quot;".htmlspecialcharsbx($ID)."&quot;)", $ID);
			}
			$DB->Commit();
			break;
		}
	}
}

// Fill list with data
$rsData = CIBlockType::GetList($arOrder, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// Set page navigation
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("IBLOCK_TYPE_ADMIN_NAV")));

// List headers/columns

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "id",
		"default" => true,
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("IBLOCK_TYPE_ADMIN_COL_NAME"),
		"default" => true,
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("IBLOCK_TYPE_ADMIN_COL_SORT"),
		"sort" => "sort",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "SECTIONS",
		"content" => GetMessage("IBLOCK_TYPE_ADMIN_COL_SECT"),
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "IN_RSS",
		"content" => GetMessage("IBLOCK_TYPE_ADMIN_COL_RSS"),
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "EDIT_FILE_BEFORE",
		"content" => GetMessage("IBLOCK_TYPE_ADMIN_COL_EDIT_BEF"),
	),
	array(
		"id" => "EDIT_FILE_AFTER",
		"content" => GetMessage("IBLOCK_TYPE_ADMIN_COL_EDIT_AFT"),
	),
));

// Build elements list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$ibtypelang = CIBlockType::GetByIDLang($f_ID, LANGUAGE_ID);
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("NAME", $ibtypelang["NAME"]);
	if($USER->IsAdmin())
	{
		$row->AddInputField("SORT");
		$row->AddCheckField("SECTIONS");
		$row->AddCheckField("IN_RSS");
		$row->AddInputField("EDIT_FILE_BEFORE");
		$row->AddInputField("EDIT_FILE_AFTER");
	}
	else
	{
		$row->AddViewField("SORT", $f_SORT);
		$row->AddCheckField("SECTIONS", false);
		$row->AddCheckField("IN_RSS", false);
		$row->AddViewField("EDIT_FILE_BEFORE", $f_EDIT_FILE_BEFORE);
		$row->AddViewField("EDIT_FILE_AFTER", $f_EDIT_FILE_AFTER);
	}

	$arActions = array();
	$arActions[] = array(
		"ICON"=>"list",
		"TEXT"=>GetMessage("IBLOCK_TYPE_ADMIN_IB"),
		"ACTION"=>$lAdmin->ActionRedirect('iblock_admin.php?lang='.LANG.'&type='.$f_ID.'&admin=Y'),
	);
	if($USER->IsAdmin())
	{
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array(
			"ICON"=>"edit",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
			"ACTION"=>$lAdmin->ActionRedirect('iblock_type_edit.php?lang='.LANG.'&ID='.$f_ID),
			"DEFAULT"=>true,
		);
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION"=>"if(confirm('".GetMessageJS("IBLOCK_TYPE_ADMIN_DEL_CONF")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}
	$row->AddActions($arActions);
}

// "footer" of the list
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

// Add form with actions
$arGroupActions = array();
if($USER->IsAdmin())
	$arGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");
$lAdmin->AddGroupActionTable($arGroupActions);

// Add context menu
$aContext = array();
if ($USER->IsAdmin())
{
	$aContext[] = array(
		"TEXT" => GetMessage("IBLOCK_TYPE_ADMIN_ADD"),
		"LINK" => "iblock_type_edit.php?lang=".LANG,
		"TITLE" => GetMessage("IBLOCK_TYPE_ADMIN_ADD_HINT"),
		"ICON" => "btn_new",
	);
}

$lAdmin->AddAdminContextMenu($aContext);

// Check if list will be output (in this case, rest of the script will be skipped)
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("IBLOCK_TYPE_ADMIN_TITLE"));

// Start of visual output
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Filter output
?>
<form name="filter_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("IBLOCK_TYPE_ADMIN_FILTER_ID")
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?echo GetMessage("IBLOCK_TYPE_ADMIN_COL_NAME")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" title="<?=GetMessage("MAIN_ADMIN_LIST_FILTER_1ST")?>">
	</td>
</tr>
<tr>
	<td><?echo GetMessage("IBLOCK_TYPE_ADMIN_FILTER_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"filter_form"));
$oFilter->End();
?>
</form>
<?

// Here is List will be displayed
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
