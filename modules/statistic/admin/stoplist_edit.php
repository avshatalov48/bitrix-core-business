<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$statDB = CDatabase::GetModuleConnection('statistic');
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","stoplist_list.php");

/***************************************************************************
			GET | POST handling
***************************************************************************/
$message = null;
$ID = intval($ID);
InitBVar($ACTIVE);
InitBVar($SAVE_STATISTIC);
InitBVar($USER_AGENT_IS_NULL);
// "save" on the current page was pressed
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $STAT_RIGHT>="W" && check_bitrix_sessid())
{
	$arFields = array(
		"DATE_START" => $_POST["DATE_START"],
		"DATE_END" => $_POST["DATE_END"],
		"SITE_ID" => $_POST["SITE_ID"],
		"ACTIVE" => $ACTIVE,
		"SAVE_STATISTIC" => $SAVE_STATISTIC,
		"IP_1" => $_POST["IP_1"],
		"IP_2" => $_POST["IP_2"],
		"IP_3" => $_POST["IP_3"],
		"IP_4" => $_POST["IP_4"],
		"MASK_1" => $_POST["MASK_1"],
		"MASK_2" => $_POST["MASK_2"],
		"MASK_3" => $_POST["MASK_3"],
		"MASK_4" => $_POST["MASK_4"],
		"USER_AGENT" => $_POST["USER_AGENT"],
		"USER_AGENT_IS_NULL" => $USER_AGENT_IS_NULL,
		"URL_TO" => $_POST["URL_TO"],
		"URL_FROM" => $_POST["URL_FROM"],
		"MESSAGE" => $_POST["MESSAGE"],
		"URL_REDIRECT" => $_POST["URL_REDIRECT"],
		"COMMENTS" => $_POST["COMMENTS"],
		"MESSAGE_LID" => $_POST["MESSAGE_LID"],
	);
	$obStopList = new CStoplist;

	if($ID > 0)
	{
		$res = $obStopList->Update($ID, $arFields);
		$new = "N";
	}
	else
	{
		$res = $ID = $obStopList->Add($arFields);
		$new = "Y";
	}

	if($res)
	{
		if(strlen($_POST["save"]) > 0)
			LocalRedirect("stoplist_list.php?lang=".LANG);
		else
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID."&tabControl_active_tab=".urlencode($tabControl_active_tab));
	}
	else
	{
		if ($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("STAT_ERROR"), $e);
	}
}

ClearVars();
$stoplist = CStoplist::GetByID($ID);
if (!($stoplist && $stoplist->ExtractFields()))
{
	$ID=0;
	$str_ACTIVE="Y";
	$str_MASK_1="255";
	$str_MASK_2="255";
	$str_MASK_3="255";
	$str_MASK_4="255";
	$str_IP_1 = $net1;
	$str_IP_2 = $net2;
	$str_IP_3 = $net3;
	$str_IP_4 = $net4;
	$str_USER_AGENT = $user_agent;
	$str_DATE_START=GetTime(time()+CTimeZone::GetOffset(),"FULL");
	$str_MESSAGE = GetMessage("STAT_DEFAULT_MESSAGE");
	$str_MESSAGE_LID = LANG;
	$str_SAVE_STATISTIC = "Y";
}
if ($message)
	$statDB->InitTableVarsForEdit("b_stop_list", "", "str_");

if ($ID>0) $sDocTitle = GetMessage("STAT_EDIT_RECORD", array("#ID#" => $ID));
else $sDocTitle = GetMessage("STAT_NEW_RECORD");

$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/***************************************************************************
			HTML form
****************************************************************************/

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT"	=> GetMessage("STAT_RECORDS_LIST"),
		"LINK"	=> "stoplist_list.php?lang=".LANGUAGE_ID
	)
);

if(intval($ID)>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TITLE"	=> GetMessage("STAT_NEW_STOPLIST"),
		"TEXT"	=> GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"LINK"	=> "stoplist_edit.php?lang=".LANGUAGE_ID
	);

	if ($STAT_RIGHT>="W")
	{
		$aMenu[] = array(
			"ICON"	=> "btn_delete",
			"TITLE"	=> GetMessage("STAT_DELETE_STOPLIST"),
			"TEXT"	=> GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"LINK"	=> "javascript:if(confirm('".GetMessageJS("STAT_DELETE_STOPLIST_CONFIRM")."'))window.location='stoplist_list.php?action=delete&ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		);
	}
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();


$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("STAT_PARAMS"), "ICON"=>"stat_stoplist", "TITLE"=>GetMessage("STAT_PARAMS_S"));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("STAT_ACTIONS"), "ICON"=>"stat_stoplist", "TITLE"=>GetMessage("STAT_WHAT_TO_DO"));
$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("STAT_COMMENT_S"), "ICON"=>"stat_stoplist", "TITLE"=>GetMessage("STAT_COMMENT"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);?>



<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?ID=<?=$ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?echo $ID?>>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<? if (strlen($str_TIMESTAMP_X)>0) : ?>
	<tr valign="center">
		<td width="40%" align="right"><?echo GetMessage("STAT_TIMESTAMP")?></td>
		<td width="60%"><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<? endif; ?>
	<tr valign="top" class="heading">
		<td colspan="2"><?=GetMessage("STAT_ACTIVITY")?><?
			if (strlen($str_LAMP)>0) :
				?>&nbsp;<?
				if ($str_LAMP=="green") echo "<font class=\"stat_pointed\">(".GetMessage("STAT_GREEN_LAMP").")</span>";
				else echo "<span class=\"stat_attention\">(".GetMessage("STAT_RED_LAMP").")</span>";
				?><?
			endif;
			?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_ACTIVE")?></td>
		<td><?echo InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false) ?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_START_DATE").":"?></td>
		<td><?echo CalendarDate("DATE_START", $str_DATE_START, "form1", "19")?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_END_DATE").":"?></td>
		<td><?echo CalendarDate("DATE_END", $str_DATE_END, "form1", "19")?></td>
	</tr>
	<tr valign="top" class="heading">
		<td colspan="2"><?=GetMessage("STAT_CONDITIONS")?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_SITE")?>:</td>
		<td><?echo CSite::SelectBox("SITE_ID", $str_SITE_ID, GetMessage("MAIN_ALL"))?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_MASK")?></td>
		<td><input  type="text" name="MASK_1" size="3" maxlength="3" value="<?echo $str_MASK_1?>">&nbsp;<input  type="text" name="MASK_2" size="3" maxlength="3" value="<?echo $str_MASK_2?>">&nbsp;<input  type="text" name="MASK_3" size="3" maxlength="3" value="<?echo $str_MASK_3?>">&nbsp;<input  type="text" name="MASK_4" size="3" maxlength="3" value="<?echo $str_MASK_4?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_IP")?></td>
		<td><input  type="text" name="IP_1" size="3" maxlength="3" value="<?echo $str_IP_1?>">&nbsp;<input  type="text" name="IP_2" size="3" maxlength="3" value="<?echo $str_IP_2?>">&nbsp;<input  type="text" name="IP_3" size="3" maxlength="3" value="<?echo $str_IP_3?>">&nbsp;<input  type="text" name="IP_4" size="3" maxlength="3" value="<?echo $str_IP_4?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_USER_AGENT")?></td>
		<td><input type="text" name="USER_AGENT" size="50" maxlength="255" value="<?echo $str_USER_AGENT?>">&nbsp;<?echo GetMessage("STAT_EMPTY")?>&nbsp;<?echo InputType("checkbox","USER_AGENT_IS_NULL","Y",$str_USER_AGENT_IS_NULL,false) ?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_URL_FROM")?></td>
		<td><input type="text" name="URL_FROM" size="60" maxlength="255" value="<?echo $str_URL_FROM?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_URL_TO")?></td>
		<td><input type="text" name="URL_TO" size="60" maxlength="255" value="<?echo $str_URL_TO?>"></td>
	</tr>
<?$tabControl->BeginNextTab();?>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_REDIRECT")?></td>
		<td><input type="text" name="URL_REDIRECT" size="60" maxlength="255" value="<?echo $str_URL_REDIRECT?>"></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_MESSAGE")?></td>
		<td><textarea name="MESSAGE" rows="5" cols="45"><?echo $str_MESSAGE?></textarea></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_MESSAGE_LID")?></td>
		<td><?echo CLanguage::SelectBox("MESSAGE_LID", $str_MESSAGE_LID);?></td>
	</tr>
	<tr valign="top">
		<td align="right"><?echo GetMessage("STAT_SAVE_STATISTIC")?></td>
		<td><?echo InputType("checkbox","SAVE_STATISTIC","Y",$str_SAVE_STATISTIC,false) ?></td>
	</tr>
<?$tabControl->BeginNextTab();?>
	<tr valign="top">
		<td colspan="2" align="center"><textarea style="width:100%" name="COMMENTS" rows="5" wrap="VIRTUAL"><?=$str_COMMENTS?></textarea></td>
	</tr>
<?
$tabControl->Buttons(Array("disabled" =>$STAT_RIGHT<"W" ,"back_url" =>"/bitrix/admin/stoplist_list.php?lang=".LANG."&set_filter=Y"));
$tabControl->End();
?>
</form>
<?$tabControl->ShowWarnings("form1", $message);?>
<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
