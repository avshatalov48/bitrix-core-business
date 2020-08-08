<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");

$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if($WORKFLOW_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");
IncludeModuleLangFile(__FILE__);
define("HELP_FILE","workflow_status_list.php");

$ID = intval($ID);
$message = false;

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("FLOW_EDIT_RECORD"),
		"ICON"=>"workflow_edit",
		"TITLE" => GetMessage("FLOW_EDIT_RECORD"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $WORKFLOW_RIGHT=="W" && check_bitrix_sessid())
{
	$obWorkflowStatus = new CWorkflowStatus;

	$arFields = array(
		"~TIMESTAMP_X" => $DB->GetNowFunction(),
		"C_SORT" => $C_SORT,
		"ACTIVE" => ($ACTIVE <> "Y"? "N":"Y"),
		"TITLE" => $TITLE,
		"DESCRIPTION" => $DESCRIPTION,
		"NOTIFY" => ($NOTIFY <> "Y"? "N":"Y"),
	);
	if($ID > 0)
	{
		$res = $obWorkflowStatus->Update($ID, $arFields);
	}
	else
	{
		$ID = $obWorkflowStatus->Add($arFields);
		$res = ($ID > 0);
	}

	if($res)
	{

		$obWorkflowStatus->SetPermissions($ID, $arPERMISSION_M, 1);
		$obWorkflowStatus->SetPermissions($ID, $arPERMISSION_E, 2);

		if($apply != "")
			LocalRedirect("/bitrix/admin/workflow_status_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/workflow_status_list.php?lang=".LANG);
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("FLOW_ERROR"), $e);
	}
}

ClearVars();
$status = CWorkflowStatus::GetByID($ID);
if (!($status->ExtractFields()))
{
	$ID = 0;
	$str_ACTIVE = "Y";
	$str_C_SORT = CWorkflowStatus::GetNextSort();
}
else
{
	$strSql = "
		SELECT
			GROUP_ID,
			PERMISSION_TYPE
		FROM
			b_workflow_status2group
		WHERE
			STATUS_ID='$ID'
		";
	$z = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	while ($zr=$z->Fetch())
	{
		if ($zr["PERMISSION_TYPE"]=="1") $arPERMISSION_M[] = $zr["GROUP_ID"];
		elseif ($zr["PERMISSION_TYPE"]=="2") $arPERMISSION_E[] = $zr["GROUP_ID"];
	}
}

if($message !== false)
	$DB->InitTableVarsForEdit("b_workflow_status", "", "str_");

$sDocTitle = ($ID > 0)? GetMessage("FLOW_EDIT_RECORD" ,array("#ID#" => $ID)): GetMessage("FLOW_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => GetMessage("FLOW_RECORDS_LIST"),
		"LINK" => "workflow_status_list.php?lang=".LANGUAGE_ID,
	),
);
if (intval($ID) > 0)
{
	$aMenu[] = array(
		"SEPARATOR" => "Y",
	);
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("FLOW_NEW_STATUS"),
		"LINK" => "workflow_status_edit.php?lang=".LANGUAGE_ID,
	);
	if ($WORKFLOW_RIGHT == "W" && intval($ID) > 1)
	{
		$aMenu[] = array(
			"ICON" => "btn_delete",
			"TEXT" => GetMessage("FLOW_DELETE_STATUS"),
			"LINK" => "javascript:if(confirm('".GetMessage("FLOW_DELETE_STATUS_CONFIRM")."')) window.location='workflow_status_list.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();
?>
<form method="POST" name="form1" action="<?echo $APPLICATION->GetCurPage()?>?" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?>>
<input type="hidden" name="lang" value="<?=LANG?>">
<?

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<? if ($str_TIMESTAMP_X <> '' && $str_TIMESTAMP_X!="00.00.0000 00:00:00") : ?>
	<tr>
		<td><?=GetMessage("FLOW_TIMESTAMP")?></td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
<? endif; ?>
<? if ($ID>0) : ?>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENTS")?></td>
		<td><a href="workflow_list.php?lang=<?=LANG?>&find_status=<?=$ID?>&set_filter=Y" title="<?=GetMessage('FLOW_DOCUMENTS_ALT')?>"><?echo intval($str_DOCUMENTS)?></a></td>
	</tr>
<?endif;?>
<? if ($ID!=1):?>
	<tr>
		<td><label for="active"><?=GetMessage("FLOW_ACTIVE")?></label></td>
		<td><?=InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false, "", 'id="active"')?></td>
	</tr>
<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("FLOW_SORTING")?></td>
		<td width="60%"><input type="text" name="C_SORT" size="5" value="<?=$str_C_SORT?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("FLOW_TITLE")?></td>
		<td><input type="text" name="TITLE" maxlength="255" value="<?=$str_TITLE?>" style="width:60%"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("FLOW_DESCRIPTION")?></td>
		<td><textarea name="DESCRIPTION" rows="5" style="width:60%"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage('FLOW_MOVE_RIGHTS');?><br><img src="/bitrix/images/workflow/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?echo SelectBoxM("arPERMISSION_M[]", CGroup::GetDropDownList(""), $arPERMISSION_M,"",true,8);?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage('FLOW_EDIT_RIGHTS');?><br><img src="/bitrix/images/workflow/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?echo SelectBoxM("arPERMISSION_E[]", CGroup::GetDropDownList(""), $arPERMISSION_E,"",true,8);?></td>
	</tr>
	<tr>
		<td><label for="notify"><?=GetMessage("FLOW_NOTIFY")?></label></td>
		<td><?=InputType("checkbox","NOTIFY","Y",$str_NOTIFY,false, "", 'id="notify"')?></td>
	</tr>
<?
$tabControl->Buttons(array(
	"disabled" => $WORKFLOW_RIGHT < "W",
	"back_url" => "workflow_status_list.php?lang=".LANGUAGE_ID,
));
$tabControl->End();
?>
</form>
<?$tabControl->ShowWarnings("form1", $message);?>
<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
