<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/prolog.php");
$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight("workflow");
if($WORKFLOW_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/workflow/include.php");
IncludeModuleLangFile(__FILE__);
define("HELP_FILE","workflow_history_list.php");

/**************************************************************************
				GET | POST
***************************************************************************/

$ID = intval($ID);

if ($ID>0)
{
	// look up in database
	$z = $DB->Query("SELECT ID FROM b_workflow_log WHERE ID='$ID'");
	if (!($zr=$z->Fetch()))
	{
		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$aMenu = array(
			array(
				"ICON" => "btn_list",
				"TEXT"	=> GetMessage("FLOW_DOCUMENT_LIST"),
				"LINK"	=> "workflow_list.php?lang=".LANG,
				"TITLE"	=> GetMessage("FLOW_DOCUMENT_LIST"),
		));

		$context = new CAdminContextMenu($aMenu);
		$context->Show();

		CAdminMessage::ShowMessage(GetMessage("FLOW_RECORD_NOT_FOUND"));

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

$PREV_ID = intval($PREV_ID);

if ($PREV_ID>0)
{
	// lookup in database
	$z = $DB->Query("SELECT ID FROM b_workflow_log WHERE ID='$PREV_ID'");
	if (!($zr=$z->Fetch()))
	{
		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$aMenu = array(
			array(
				"ICON" => "btn_list",
				"TEXT"	=> GetMessage("FLOW_DOCUMENT_LIST"),
				"LINK"	=> "workflow_list.php?lang=".LANG,
				"TITLE"	=> GetMessage("FLOW_DOCUMENT_LIST"),
		));

		$context = new CAdminContextMenu($aMenu);
		$context->Show();

		CAdminMessage::ShowMessage(GetMessage("FLOW_RECORD_NOT_FOUND"));

		require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

$history = CWorkflow::GetHistoryByID($ID);
ClearVars();
$ar = $history->ExtractFields();
$z = CWorkflow::GetByID($str_DOCUMENT_ID);
if ($zr = $z->Fetch())
	$document_exist = "Y";


$prev_history = CWorkflow::GetHistoryByID($PREV_ID);
$prev_ar = $prev_history->Fetch();
$prev_z = CWorkflow::GetByID($prev_ar["DOCUMENT_ID"]);
if ($prev_zr = $prev_z->Fetch())
	$prev_document_exist = "Y";

$sDocTitle = str_replace("#ID#", "$ID", GetMessage("FLOW_PAGE_TITLE"));
$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/***************************************************************************
				HTML form
****************************************************************************/
$aTabs = array();

if($ID && $PREV_ID)
{
	$aTabs[] = array(
		"DIV" => "edit1",
		"TAB" => GetMessage("FLOW_RECORD_SETTINGS"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("FLOW_PAGE_DIFF_TITLE", array("#ID#"=>$ID, "#PREV_ID#"=>$PREV_ID)),
	);
}
else
{
	$aTabs[] = array(
		"DIV" => "edit1",
		"TAB" => GetMessage("FLOW_RECORD_SETTINGS"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("FLOW_PAGE_TITLE", array("#ID#" => $ID)),
	);
}

if($document_exist == "Y")
{
	$aTabs[] =  array(
		"DIV" => "edit2",
		"TAB" => GetMessage("FLOW_CURRENT_SETTINGS"),
		"ICON"=>"main_user_edit",
		"TITLE"=>str_replace("#ID#", "$ID", GetMessage("FLOW_PAGE_PARAM_TITLE"))
	);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);


$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT"	=> GetMessage("FLOW_HISTORY"),
		"LINK"	=> "workflow_history_list.php?lang=".LANG."&set_filter=Y&find_document_id=".$str_DOCUMENT_ID,
		"TITLE"	=> GetMessage("FLOW_HISTORY"),
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?=GetMessage("FLOW_DOCUMENT_ID")?></td>
		<td width="60%"><?
			if (CWorkflow::IsHaveEditRights($str_DOCUMENT_ID)) :
			?><a href="workflow_edit.php?lang=<?=LANG?>&ID=<?=$str_DOCUMENT_ID?>" title="<?=GetMessage('FLOW_VIEW_DOC_ALT')?>"><?=$str_DOCUMENT_ID?></a><?
			else :
			?><?=$str_DOCUMENT_ID?><?
			endif;
		?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FLOW_HIST_VIEW_SITE")?></td>
		<td><a href="/bitrix/admin/site_edit.php?lang=<?=LANG?>&LID=<?=$str_SITE_ID?>" title="<?=GetMessage('FLOW_SITE_ALT')?>"><?=$str_SITE_ID?></a></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_FILENAME")?></td>
		<td><a href="<?=$str_FILENAME?>" title="<?=GetMessage('FLOW_VIEW_FILE_ALT')?>"><?=$str_FILENAME?></a></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_TITLE")?></td>
		<td><?=$str_TITLE?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_TIMESTAMP")?></td>
		<td><?=$str_TIMESTAMP_X?>&nbsp;&nbsp;[<a href="user_edit.php?ID=<?=$str_MODIFIED_BY?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$str_MODIFIED_BY?></a>]&nbsp;<?echo $str_USER_NAME?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_STATUS")?></td>
		<td>[<a href="workflow_status_edit.php?ID=<?=$str_STATUS_ID?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_STATUS_ALT')?>"><?echo $str_STATUS_ID?></a>]&nbsp;<?=$str_STATUS_TITLE?></td>
	</tr>
	<?
	if($ID > 0 && $PREV_ID > 0):?>
	<tr>
		<td colspan=2>
		<?echo getDiff($prev_ar["BODY"], $ar["BODY"])?>
		</td>
	</tr>
	<?
	elseif(COption::GetOptionString("workflow", "USE_HTML_EDIT", "Y")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td align="center" colspan="2"><?
		$bWithoutPHP = !$USER->IsAdmin();
		CFileMan::AddHTMLEditorFrame("BODY", $str_BODY, "BODY_TYPE", $str_BODY_TYPE, 300, "Y", $str_DOCUMENT_ID, GetDirPath($str_FILENAME),"",false,$bWithoutPHP);
		?></td>
	</tr>
	<?else:?>
	<tr>
		<td align="center" colspan="2"><?echo GetMessage("FLOW_TEXT")?>&nbsp;<? echo InputType("radio","BODY_TYPE","text",$str_BODY_TYPE,false)?>&nbsp;HTML&nbsp;<? echo InputType("radio","BODY_TYPE","html",$str_BODY_TYPE,false)?></td>
	</tr>
	<?endif;?>
	<?if ($str_DOCUMENT_ID>0):?>
	<tr>
		<td><?=GetMessage("FLOW_COMMENTS");?></td>
		<td colspan="2"><?=$str_COMMENTS?></td>
	</tr>
	<?endif;?>

<?
$tabControl->EndTab();
if ($document_exist=="Y"):
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?=GetMessage("FLOW_DOCUMENT_ID");?></td>
		<td width="60%"><?
			if (CWorkflow::IsHaveEditRights($zr["ID"])) :
			?><a href="workflow_edit.php?lang=<?=LANG?>&ID=<?=$zr["ID"]?>" title="<?=GetMessage('FLOW_VIEW_DOC_ALT')?>"><?=$zr["ID"]?></a><?
			else :
			?><?=$zr["ID"]?><?
			endif;
			?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENT_FILENAME")?></td>
		<td><a href="<?=htmlspecialcharsbx($zr["FILENAME"])?>" title="<?=GetMessage('FLOW_VIEW_FILE_ALT')?>"><?=htmlspecialcharsbx($zr["FILENAME"])?></a></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENT_TITLE")?></td>
		<td><?=htmlspecialcharsbx($zr["TITLE"])?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENT_DATE_ENTER")?></td>
		<td><?=$zr["DATE_ENTER"]?>&nbsp;&nbsp;[<a href="user_edit.php?ID=<?=$zr["ENTERED_BY"]?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$zr["ENTERED_BY"]?></a>]&nbsp;<?echo htmlspecialcharsbx($zr["EUSER_NAME"])?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENT_DATE_MODIFY")?></td>
		<td><?=$zr["DATE_MODIFY"]?>&nbsp;&nbsp;[<a href="user_edit.php?ID=<?=$zr["MODIFIED_BY"]?>&lang=<?=LANG?>"  title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$zr["MODIFIED_BY"]?></a>]&nbsp;<? echo htmlspecialcharsbx($zr["MUSER_NAME"])?></td>
	</tr>
	<? if ($zr["DATE_LOCK"] <> '') : ?>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENT_DATE_LOCK")?></td>
		<td><?=$zr["DATE_LOCK"]?>&nbsp;&nbsp;[<a href="user_edit.php?ID=<?=$zr["LOCKED_BY"]?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_USER_ALT')?>"><?=$zr["LOCKED_BY"]?></a>]&nbsp;<? echo htmlspecialcharsbx($zr["LUSER_NAME"])?>&nbsp;<?if ($zr["LOCKED_BY"]==$USER->GetID()):?><span class="required">(!)</span><?endif;?></td>
	</tr>
	<? endif; ?>
	<tr>
		<td><?=GetMessage("FLOW_DOCUMENT_STATUS")?></td>
		<td>[<a href="workflow_status_edit.php?ID=<?=$zr["STATUS_ID"]?>&lang=<?=LANG?>" title="<?=GetMessage('FLOW_STATUS_ALT')?>"><?=$zr["STATUS_ID"]?></a>]&nbsp;<? echo htmlspecialcharsbx($zr["STATUS_TITLE"])?></td>
	</tr>
<?
$tabControl->EndTab();
endif;
$tabControl->Buttons();

$tabControl->End();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
