<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");

$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if($WORKFLOW_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");
IncludeModuleLangFile(__FILE__);

$sTableID = "t_wf_history_list";
$oSort = new CAdminSorting($sTableID, "s_date_modify", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"find",
	"find_type",
	"find_id",
	"find_id_exact_match",
	"find_document_id",
	"find_document_id_exact_match",
	"find_modify_1",
	"find_modify_2",
	"find_modified_user",
	"find_modified_user_exact_match",
	"find_site_id",
	"find_filename",
	"find_filename_exact_match",
	"find_title",
	"find_title_exact_match",
	"find_body",
	"find_body_exact_match",
	"find_status",
	"find_status_exact_match",
	"find_status_id",
	"FILTER_logic",
);

$lAdmin->InitFilter($arFilterFields);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		"ID",
		GetMessage('FLOW_F_DOCUMENT'),
		GetMessage("FLOW_F_DATE_MODIFY"),
		GetMessage('FLOW_F_MODIFIED_BY'),
		GetMessage('FLOW_SITE_ID'),
		GetMessage('FLOW_F_FILENAME'),
		GetMessage('FLOW_F_TITLE'),
		GetMessage('FLOW_F_BODY'),
		GetMessage('FLOW_F_STATUS'),
		GetMessage('FLOW_F_LOGIC'),
	)
);

InitBVar($find_id_exact_match);
InitBVar($find_document_id_exact_match);
InitBVar($find_modified_user_exact_match);
InitBVar($find_filename_exact_match);
InitBVar($find_title_exact_matc);
InitBVar($find_body_exact_match);
InitBVar($find_status_exact_match);

$arFilter = array(
	"ID" => $find_id,
	"DOCUMENT_ID" => $find_document_id,
	"DATE_MODIFY_1" => $find_modify_1,
	"DATE_MODIFY_2" => $find_modify_2,
	"MODIFIED_USER" => ($find_type == "modified_by" && $find <> '' ? $find : $find_modified_user),
	"SITE_ID" => $find_site_id,
	"FILENAME" => $find_filename,
	"TITLE" => ($find_type == "title" && $find <> '' ? $find : $find_title),
	"BODY" => ($find_type == "body" && $find <> '' ? $find : $find_body),
	"STATUS" => $find_status,
	"STATUS_ID" => $find_status_id,
	"ID_EXACT_MATCH" => $find_id_exact_match,
	"DOCUMENT_ID_EXACT_MATCH" => $find_document_id_exact_match,
	"MODIFIED_USER_EXACT_MATCH" => $find_modified_user_exact_match,
	"FILENAME_EXACT_MATCH" => $find_filename_exact_match,
	"TITLE_EXACT_MATCH" => $find_title_exact_match,
	"BODY_EXACT_MATCH" => $find_body_exact_match,
	"STATUS_EXACT_MATCH" => $find_status_exact_match,
);

if ($arID = $lAdmin->GroupAction())
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$rsData = CWorkflow::GetList('', '', $arFilter);
		while ($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	foreach ($arID as $ID)
	{
		$ID = intval($ID);
		if ($ID <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
		case "delete":
			if ($WORKFLOW_RIGHT > "R" && CWorkflow::IsAdmin())
			{
				CWorkflow::DeleteHistory($ID);
			}
			break;
		}
	}
}

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => "ID",
		"default" => false,
		"sort" => "s_id",
	),
	array(
		"id" => "DOCUMENT_ID",
		"content" => GetMessage("FLOW_DOCUMENT"),
		"default" => false,
		"sort" => "s_document_id",
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("FLOW_DATE_MODIFY"),
		"default" => true,
		"sort" => "s_date_modify",
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage("FLOW_MODIFIED_BY"),
		"default" => true,
		"sort" => "s_modified_by",
	),
	array(
		"id" => "SITE_ID",
		"content" => GetMessage("FLOW_SITE_ID"),
		"default" => true,
		"sort" => "s_site_id",
	),
	array(
		"id" => "FILENAME",
		"content" => GetMessage("FLOW_FILENAME"),
		"default" => true,
		"sort" => "s_filename",
	),
	array(
		"id" => "TITLE",
		"content" => GetMessage("FLOW_TITLE"),
		"default" => true,
		"sort" => "s_title",
	),
	array(
		"id" => "STATUS_ID",
		"content" => GetMessage("FLOW_STATUS"),
		"default" => true,
		"sort" => "s_status",
	),
);
$lAdmin->AddHeaders($arHeaders);

global $by, $order;

$rsData = CWorkflow::GetHistoryList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart(50);
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FLOW_PAGES")));
while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row = & $lAdmin->AddRow($f_ID, $arRes);
	if (CWorkflow::IsHaveEditRights($f_DOCUMENT_ID) && $f_DOCUMENT_ID > 0)
		$row->AddViewField("DOCUMENT_ID", '<a href="workflow_edit.php?lang='.LANG.'&ID='.$f_DOCUMENT_ID.'">'.$f_DOCUMENT_ID.'</a>');

	$row->AddViewField("MODIFIED_BY", '[<a title="'.GetMessage("FLOW_USER_ALT").'" href="user_edit.php?ID='.$f_MODIFIED_BY.'&lang='.LANG.'">'.$f_MODIFIED_BY.'</a>]&nbsp;'.$f_USER_NAME);
	$row->AddViewField("FILENAME", '<a href="'.$f_FILENAME.'">'.TruncateText($f_FILENAME, 45).'</a>');
	$row->AddViewField("STATUS_ID", '[<a title="'.GetMessage("FLOW_STATUS_ALT").'" href="workflow_status_edit.php?ID='.$f_STATUS_ID.'&lang='.LANG.'">'.$f_STATUS_ID.'</a>]&nbsp;'.$f_STATUS_TITLE);
	$arActions = array(
		array(
			"ICON" => "view",
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("FLOW_VIEW"),
			"ACTION" => $lAdmin->ActionRedirect("workflow_history_view.php?lang=".LANG."&ID=".$f_ID),
		),
	);
	if ($WORKFLOW_RIGHT > "R" && CWorkflow::IsAdmin())
	{
		$arActions[] = array(
			"SEPARATOR" => true,
		);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("FLOW_DELETE"),
			"ACTION" => "if(confirm('".GetMessage('FLOW_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}
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

if ($WORKFLOW_RIGHT > "R" && CWorkflow::IsAdmin())
{
	$lAdmin->AddGroupActionTable(array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		array(
			"action" => "Diff()",
			"value" => "compare",
			"type" => "button",
			"name" => GetMessage("FLOW_COMPARE"),
		),
	));
}

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => GetMessage("FLOW_RECORDS_LIST"),
		"LINK" => "workflow_list.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("FLOW_RECORDS_LIST"),
	),
);

$lAdmin->AddAdminContextMenu($aMenu);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("FLOW_PAGE_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<script>
function Diff()
{
	var selection = new Array();
	var j = 0;

	var inputs = document.getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].getAttribute("name") == 'ID[]')
		{
			var a = inputs[i].checked;
			if (a == true)
			{
				selection[j] = inputs[i].value;
				j++;
			}
		}
	}
	if(j < 2 || j > 2)
	{
		alert('<?echo GetMessageJS("FLOW_COMPARE_ALERT")?>');
	}
	else
	{
		window.location='workflow_history_view.php?lang=<?echo urlencode(LANG)?>&ID='+selection[0]+'&PREV_ID='+selection[1];
	}
}
</script>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr>
	<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("MAIN_FIND_TITLE")?>">
		<select name="find_type">
			<option value="title"<?if($find_type=="title") echo " selected"?>><?=GetMessage('FLOW_F_TITLE')?></option>
			<option value="body"<?if($find_type=="body") echo " selected"?>><?=GetMessage('FLOW_F_BODY')?></option>
			<option value="modified_by"<?if($find_type=="modified_by") echo " selected"?>><?=GetMessage('FLOW_F_MODIFIED_BY')?></option>
		</select>
	</td>
</tr>

<tr>
	<td>ID:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("FLOW_F_DOCUMENT")?>:</td>
	<td nowrap><input type="text" name="find_document_id" size="47" value="<?echo htmlspecialcharsbx($find_document_id)?>"><?=ShowExactMatchCheckbox("find_document_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?echo GetMessage("FLOW_F_DATE_MODIFY").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_modify_1", $find_modify_1, "find_modify_2", $find_modify_2, "form1", "Y")?></td>
</tr>
<tr valign="center">
	<td nowrap valign="top"><?=GetMessage("FLOW_F_MODIFIED_BY")?>:</td>
	<td nowrap><input type="text" name="find_modified_user" value="<?echo htmlspecialcharsbx($find_modified_user)?>" size="47"><?=ShowExactMatchCheckbox("find_modified_user")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("FLOW_SITE_ID")?>:</td>
	<td><?=CSite::SelectBox("find_site_id", $find_site_id, GetMessage("MAIN_ALL"));?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("FLOW_F_FILENAME")?>:</td>
	<td nowrap><input type="text" name="find_filename" value="<?echo htmlspecialcharsbx($find_filename)?>" size="47"><?=ShowExactMatchCheckbox("find_filename")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("FLOW_F_TITLE")?>:</td>
	<td nowrap><input type="text" name="find_title" value="<?echo htmlspecialcharsbx($find_title)?>" size="47"><?=ShowExactMatchCheckbox("find_title")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap><?=GetMessage("FLOW_F_BODY")?>:</td>
	<td nowrap><input type="text" name="find_body" value="<?echo htmlspecialcharsbx($find_body)?>" size="47"><?=ShowExactMatchCheckbox("find_body")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td nowrap valign="top"><?=GetMessage("FLOW_F_STATUS")?>:</td>
	<td nowrap><input type="text" name="find_status" value="<?echo htmlspecialcharsbx($find_status)?>" size="47"><?=ShowExactMatchCheckbox("find_status")?>&nbsp;<?=ShowFilterLogicHelp()?><br><?
	echo SelectBox("find_status_id", CWorkflowStatus::GetDropDownList("Y"), GetMessage("MAIN_ALL"), htmlspecialcharsbx($find_status_id));
	?></td>
</tr>
<?
echo ShowLogicRadioBtn();
$filter->Buttons(array(
	"table_id" => $sTableID,
	"url" => $APPLICATION->GetCurPage(),
	"form" => "form1",
));
$filter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>