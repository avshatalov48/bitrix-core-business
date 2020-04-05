<?php
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "utilities/agent_edit.php");

ClearVars("a_");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$ID = intval($ID);
if($ID > 0)
{
	$res = CAgent::GetById($ID);
	$arr = $res->ExtractFields("a_");
}

$APPLICATION->SetTitle( ($ID <=0) ? GetMessage("MAIN_AGENT_NEW_PAGE_TITLE") : str_replace("#ID#", " $ID", GetMessage("MAIN_AGENT_EDIT_PAGE_TITLE")));
$sTableID = "tbl_agent_edit";

$aTabs = array(array("DIV"=>"tab1", "TAB"=>GetMessage("MAIN_AGENT_TAB"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MAIN_AGENT_TAB_TITLE")));
$editTab = new CAdminTabControl("editTab", $aTabs);

$APPLICATION->ResetException();
if($REQUEST_METHOD=="POST" && (strlen($save)>0 || strlen($apply)>0) && $isAdmin && check_bitrix_sessid())
{
	$arFields = Array(
		"NAME" => $NAME,
		"MODULE_ID" => $MODULE_ID,
		"ACTIVE" => $ACTIVE,
		"SORT" => $SORT,
		"IS_PERIOD" => $IS_PERIOD,
		"AGENT_INTERVAL" => $AGENT_INTERVAL,
		"NEXT_EXEC" => $NEXT_EXEC,
		"USER_ID"	=>	false
	);

	if(intval($USER_ID) > 0)
		$arFields["USER_ID"] = $USER_ID;

	if($ID>0)
		$res = CAgent::Update($ID, $arFields);
	else
	{
		$ID = CAgent::Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if(strlen($save) > 0)
			LocalRedirect("/bitrix/admin/agent_list.php");
		elseif(strlen($apply) > 0)
			LocalRedirect("/bitrix/admin/agent_edit.php?&ID=".$ID."&".$editTab->ActiveTabParam());
	}
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("MAIN_AGENT_RECORD_LIST"),
		"LINK"	=> "/bitrix/admin/agent_list.php?lang=".LANG,
		"ICON"	=> "btn_list",
		"TITLE"	=> GetMessage("MAIN_AGENT_RECORD_LIST_TITLE"),
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_AGENT_NEW_RECORD"),
		"LINK"	=> "/bitrix/admin/agent_edit.php?lang=".LANGUAGE_ID,
		"ICON"	=> "btn_new",
		"TITLE"	=> GetMessage("MAIN_AGENT_NEW_RECORD_TITLE"),
	);
	$aMenu[] = array(
		"TEXT"	=> GetMessage("MAIN_AGENT_DEL_RECORD_TITLE"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("MAIN_AGENT_DELETE_RECORD_CONF")."')) window.location='/bitrix/admin/agent_list.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"	=> "btn_delete",
		"TITLE"	=> GetMessage("MAIN_AGENT_DEL_RECORD_TITLE"),
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

$message = null;
if($e = $APPLICATION->GetException())
{
	$message = new CAdminMessage(GetMessage("MAIN_AGENT_ERROR_SAVING"), $e);
	$DB->InitTableVarsForEdit("b_agent", "", "a_");
}

if($message)
	echo $message->Show();
?>
<form name="f_agent" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="POST">
<?=bitrix_sessid_post()?>
<?
$editTab->Begin();
$editTab->BeginNextTab();
?>
	<input type="hidden" name="ID" value=<?echo $ID?>>
	<?if($ID > 0):?>
	<tr>
		<td><?echo GetMessage("MAIN_AGENT_LAST_EXEC")?></td>
		<td><?echo $a_LAST_EXEC?></td>
	</tr>
	<?endif;?>

	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("MAIN_AGENT_START_EXEC")?>:</td>
		<td width="60%"><?echo CalendarDate("NEXT_EXEC", htmlspecialcharsbx($a_NEXT_EXEC), "f_agent", 20)?></td>
	</tr>
	<tr>
		<td><?echo GetMessage('MAIN_AGENT_ACTIVE')?></td>
		<td>
			<input type="hidden" name="ACTIVE" value="N">
			<input type="checkbox" name="ACTIVE" value="Y"<?if($a_ACTIVE=="Y") echo " checked"?>>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_AGENT_MODULE_ID")?></td>
		<td><input type="text" name="MODULE_ID" size="40" value="<? echo $a_MODULE_ID?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("MAIN_AGENT_NAME")?></td>
		<td><input type="text" name="NAME" size="40" value="<? echo $a_NAME?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_AGENT_USER_ID")?></td>
		<td><?echo FindUserID("USER_ID", $a_USER_ID, "", "f_agent", 4)?>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_AGENT_SORT")?></td>
		<td><input type="text" name="SORT" size="40" value="<? echo $a_SORT?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("MAIN_AGENT_PERIODICAL1")?></td>
		<td>
			<label><input type="radio" name="IS_PERIOD" value="N"<?if($a_IS_PERIOD<>"Y") echo " checked"?>><?echo GetMessage("MAIN_AGENT_PERIODICAL_INTERVAL")?></label><br>
			<label><input type="radio" name="IS_PERIOD" value="Y"<?if($a_IS_PERIOD=="Y") echo " checked"?>><?echo GetMessage("MAIN_AGENT_PERIODICAL_TIME")?></label>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_AGENT_INTERVAL")?></td>
		<td><input type="text" name="AGENT_INTERVAL" size="40" value="<? echo $a_AGENT_INTERVAL?>"></td>
	</tr>

<?
$editTab->Buttons(array("disabled"=>!$isAdmin, "back_url"=>"agent_list.php?lang=".LANGUAGE_ID));
$editTab->End();
?>
</form>
<?
$editTab->ShowWarnings("f_agent", $message);
?>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
