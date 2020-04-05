<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
$statDB = CDatabase::GetModuleConnection('statistic');
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("STAT_EVENT_TYPE"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("STAT_EVENT_TYPE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","event_type_list.php");

/***************************************************************************
		GET | POST handlers
***************************************************************************/
$ID = intval($ID);
InitBVar($ADV_VISIBLE);
InitBVar($DIAGRAM_DEFAULT);
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $STAT_RIGHT=="W" && check_bitrix_sessid())
{
	$cEventType = new CStatEventType();

	$strSql = "SELECT KEEP_DAYS FROM b_stat_event WHERE ID = $ID";
	$rsEvent = $statDB->Query($strSql, false, $err_mess.__LINE__);
	$arEvent = $rsEvent->Fetch();

	$statDB->PrepareFields("b_stat_event");
	$sql_KEEP_DAYS = (strlen(trim($KEEP_DAYS))<=0) ? "null" : intval($KEEP_DAYS);
	$arFields = array(
		"EVENT1"		=> (strlen(trim($EVENT1))>0) ? $str_EVENT1 : "",
		"EVENT2"		=> (strlen(trim($EVENT2))>0) ? $str_EVENT2 : "",
		"ADV_VISIBLE"		=> "'".$str_ADV_VISIBLE."'",
		"NAME"			=> "'".$str_NAME."'",
		"DESCRIPTION"		=> "'".$str_DESCRIPTION."'",
		"KEEP_DAYS"		=> $sql_KEEP_DAYS,
		"C_SORT"		=> "'".$str_C_SORT."'",
		"DIAGRAM_DEFAULT"	=> "'".$str_DIAGRAM_DEFAULT."'",
		"DYNAMIC_KEEP_DAYS"	=> (strlen(trim($DYNAMIC_KEEP_DAYS))<=0) ? "null" : intval($str_DYNAMIC_KEEP_DAYS)
		);
	if($cEventType->CheckFields($arFields, $ID))
	{
		$arFields["EVENT1"]=$arFields["EVENT1"]==""?'null':"'".$arFields["EVENT1"]."'";
		$arFields["EVENT2"]=$arFields["EVENT2"]==""?'null':"'".$arFields["EVENT2"]."'";

		$statDB->StartTransaction();
		if ($ID>0)
		{
			$statDB->Update("b_stat_event",$arFields,"WHERE ID='".$ID."'",$err_mess.__LINE__);
			if (intval($KEEP_DAYS)!=$arEvent["KEEP_DAYS"])
			{
				$arFields = array("KEEP_DAYS" => $sql_KEEP_DAYS);
				$statDB->Update("b_stat_event_list",$arFields,"WHERE EVENT_ID=$ID",$err_mess.__LINE__);
			}
		}
		else
		{
			$arFields["DATE_ENTER"] = "null";
			$arFields["DATE_CLEANUP"] = "null";
			$ID = $statDB->Insert("b_stat_event",$arFields, $err_mess.__LINE__);
			$new = "Y";
		}
		$statDB->Commit();
		if($apply!="")
			LocalRedirect("event_type_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("event_type_list.php?lang=".LANG);
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("STAT_SAVE_ERROR"), $e);
		$bVarsFromForm = true;
	}
}

ClearVars();
$str_ADV_VISIBLE="Y";
$str_C_SORT = "100";
if($ID>0)
{
	$event = CStatEventType::GetByID($ID);
	if(!($event_arr = $event->ExtractFields("str_")))
		$ID=0;
}
if($bVarsFromForm)
	$statDB->InitTableVarsForEdit("b_stat_event", "", "str_");

if ($ID>0)
	$APPLICATION->SetTitle(GetMessage("STAT_EDIT_RECORD", array("#ID#" => $ID)));
else
	$APPLICATION->SetTitle(GetMessage("STAT_NEW_RECORD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/***************************************************************************
				HTML form
****************************************************************************/

$aMenu = array(
	array(
		"TEXT"=>GetMessage("STAT_LIST"),
		"TITLE"=>GetMessage("STAT_RECORDS_LIST"),
		"LINK"=> "event_type_list.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);

if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=> GetMessage("STAT_ADD"),
		"TITLE"=>GetMessage("STAT_NEW_EVENT_TYPE"),
		"LINK"=>"event_type_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=> GetMessage("STAT_CLEAR"),
		"TITLE"=>GetMessage("STAT_RESET_EVENT_TYPE"),
		"LINK"	=> "javascript:if(confirm('".GetMessageJS("STAT_RESET_EVENT_TYPE_CONFIRM")."'))window.location='event_type_list.php?ID=".$ID."&action=clear&lang=".LANG."&".bitrix_sessid_get()."';",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("STAT_DELETE"),
		"TITLE"=>GetMessage("STAT_DELETE_EVENT_TYPE"),
		"LINK"	=> "javascript:if(confirm('".GetMessageJS("STAT_DELETE_EVENT_TYPE_CONFIRM")."'))window.location='event_type_list.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message)
	echo $message->Show();
?>

<form method="POST" action="<?=$APPLICATION->GetCurPage()?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%">event1:</td>
		<td width="60%"><input type="text" name="EVENT1" size="20" maxlength="200" value="<?echo $str_EVENT1?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>event2:</td>
		<td><input type="text" name="EVENT2" size="20" maxlength="200" value="<?echo $str_EVENT2?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_ADV_VISIBLE", array("#LANG#" => LANG));?></td>
		<td><?echo InputType("checkbox","ADV_VISIBLE","Y",$str_ADV_VISIBLE,false);?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_PIE_CHART")?></td>
		<td><?echo InputType("checkbox","DIAGRAM_DEFAULT","Y",$str_DIAGRAM_DEFAULT,false) ?></td>
	</tr>
	<tr>
		<td><? echo GetMessage("STAT_KEEP_DAYS")?></td>
		<td><input type="text" name="KEEP_DAYS" size="5" value="<?echo $str_KEEP_DAYS?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_DYNAMIC_KEEP_DAYS")?></td>
		<td><input type="text" name="DYNAMIC_KEEP_DAYS" size="5" value="<?echo $str_DYNAMIC_KEEP_DAYS?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_SORT")?></td>
		<td><input type="text" name="C_SORT" size="5" value="<?echo $str_C_SORT?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_NAME")?></td>
		<td><input type="text" name="NAME" size="50" maxlength="50" value="<?echo $str_NAME?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_DESCRIPTION")?></td>
		<td><textarea class="typearea" name="DESCRIPTION" cols="50" rows="6"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($STAT_RIGHT<"W"),
		"back_url"=>"event_type_list.php?lang=".LANG,

	)
);
?>
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?echo $ID?>>
<input type="hidden" name="lang" value="<?=LANG?>">
<?
$tabControl->End();
?>
</form>
<?
$tabControl->ShowWarnings("post_form", $message);
?>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
