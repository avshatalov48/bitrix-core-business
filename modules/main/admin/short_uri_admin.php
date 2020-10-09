<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation('manage_short_uri') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('manage_short_uri');

$sTableID = "tbl_short_uri";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	if (trim($find_modified_1) <> '' || trim($find_modified_2) <> '')
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_modified_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_modified_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if (!$date1_stm && trim($find_modified_1) <> '')
			$lAdmin->AddFilterError(GetMessage("SU_AF_WRONG_UPDATE_FROM"));
		else $date_1_ok = true;
		if (!$date2_stm && trim($find_modified_2) <> '')
			$lAdmin->AddFilterError(GetMessage("SU_AF_WRONG_UPDATE_TILL"));
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
			$lAdmin->AddFilterError(GetMessage("SU_AF_FROM_TILL_UPDATE"));
	}
	if (trim($find_last_used_1) <> '' || trim($find_last_used_2) <> '')
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(FmtDate($find_last_used_1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(FmtDate($find_last_used_2,"D.M.Y")." 23:59","d.m.Y H:i");
		if (!$date1_stm && trim($find_last_used_1) <> '')
			$lAdmin->AddFilterError(GetMessage("SU_AF_WRONG_INSERT_FROM"));
		else $date_1_ok = true;
		if (!$date2_stm && trim($find_last_used_2) <> '')
			$lAdmin->AddFilterError(GetMessage("SU_AF_WRONG_INSERT_TILL"));
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
			$lAdmin->AddFilterError(GetMessage("SU_AF_FROM_TILL_INSERT"));
	}
	return count($lAdmin->arFilterErrors)==0;
}

$FilterArr = Array(
	"find_uri",
	"find_short_uri",
	"find_modified_1",
	"find_modified_2",
	"find_last_used_1",
	"find_last_used_2",
	);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter())
{
	$arFilter = Array();
	if ($find_modified_1 <> '')
		$arFilter["MODIFIED_1"]	= $find_modified_1;
	if ($find_modified_2 <> '')
		$arFilter["MODIFIED_2"]	= $find_modified_2;
	if ($find_last_used_1 <> '')
		$arFilter["LAST_USED_1"] = $find_last_used_1;
	if ($find_last_used_2 <> '')
		$arFilter["LAST_USED_2"] = $find_last_used_2;
	if ($find_uri <> '')
		$arFilter["URI"] = $find_uri;
	if ($find_short_uri <> '')
		$arFilter["SHORT_URI"] = $find_short_uri;
}

if($lAdmin->EditAction() && $isAdmin)
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = intval($ID);

		if(!CBXShortUri::Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(GetMessage("SU_AF_SAVE_ERROR").$ID.": ".implode("\n ", CBXShortUri::GetErrors()), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

$strError = $strOk = "";

if(($arID = $lAdmin->GroupAction()) && $isAdmin)
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CBXShortUri::GetList(array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CBXShortUri::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("SU_AF_del_err"), $ID);
			}
			$DB->Commit();
			break;
		}

	}
}

$rsData = CBXShortUri::GetList(array($by=>$order), $arFilter, array("nPageSize"=>CAdminResult::GetNavSize($sTableID)));
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("SU_AF_nav")));

$lAdmin->AddHeaders(array(
	array(	"id"		=>"ID",
		"content"	=>"ID",
		"sort"		=>"ID",
		"align"		=>"right",
		"default"	=>true,
	),
	array(	"id"		=>"MODIFIED",
		"content"	=>GetMessage("SU_FLD_MODIFIED"),
		"sort"		=>"MODIFIED",
		"default"	=>true,
	),
	array(	"id"		=>"URI",
		"content"	=>GetMessage("SU_FLD_URI"),
		"sort"		=>"URI",
		"default"	=>true,
	),
	array(	"id"		=>"SHORT_URI",
		"content"	=>GetMessage("SU_FLD_SHORT_URI"),
		"sort"		=>"SHORT_URI",
		"default"	=>true,
	),
	array(	"id"		=>"STATUS",
		"content"	=>GetMessage("SU_FLD_STATUS"),
		"sort"		=>"STATUS",
		"default"	=>true,
	),
	array(	"id"		=>"LAST_USED",
		"content"	=>GetMessage("SU_FLD_LAST_USED"),
		"sort"		=>"LAST_USED",
		"default"	=>true,
	),
	array(	"id"		=>"NUMBER_USED",
		"content"	=>GetMessage("SU_FLD_NUMBER_USED"),
		"sort"		=>"NUMBER_USED",
		"default"	=>true,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("SU_AF_upd"),
		"ACTION"=>$lAdmin->ActionRedirect("short_uri_edit.php?ID=".$f_ID)
	);
	if ($isAdmin)
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("SU_AF_del"),
			"ACTION"=>"if(confirm('".GetMessage("SU_AF_del_conf")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	$row->AddActions($arActions);

endwhile;

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
	));

$aContext = array(
	array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"short_uri_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("SU_AF_add_title"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SU_AF_title"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
//		GetMessage("SU_AF_F_URI"),
		GetMessage("SU_AF_F_SHORT_URI"),
		GetMessage("SU_AF_F_MODIFIED"),
		GetMessage("SU_AF_F_LAST_USED"),
	)
);
?>

<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("SU_AF_F_URI")?>:</b></td>
	<td>
		<input type="text" size="47" name="find_uri" value="<?echo htmlspecialcharsbx($find_uri)?>">&nbsp;<?=ShowFilterLogicHelp()?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("SU_AF_F_SHORT_URI")?>:</td>
	<td><input type="text" name="find_short_uri" size="47" value="<?echo htmlspecialcharsbx($find_short_uri)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("SU_AF_F_MODIFIED")?>:</td>
	<td><?echo CalendarPeriod("find_modified_1", htmlspecialcharsbx($find_modified_1), "find_modified_2", htmlspecialcharsbx($find_modified_2), "find_form","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("SU_AF_F_LAST_USED")?>:</td>
	<td><?echo CalendarPeriod("find_last_used_1", htmlspecialcharsbx($find_last_used_1), "find_last_used_2", htmlspecialcharsbx($find_last_used_2), "find_form","Y")?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>