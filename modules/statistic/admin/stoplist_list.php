<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
			Helper functions
***************************************************************************/

$sTableID = "t_stop_list";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_SITE"),
		GetMessage("STAT_F_DATE_START"),
		GetMessage("STAT_F_DATE_END"),
		GetMessage("STAT_F_ACTIVE"),
		GetMessage("STAT_F_STATISTIC"),
		GetMessage("STAT_F_IP"),
		GetMessage("STAT_F_USER_AGENT"),
		GetMessage("STAT_F_FROM"),
		GetMessage("STAT_F_TO"),
		GetMessage("STAT_F_REDIRECT"),
		GetMessage("STAT_F_MESSAGE"),
		GetMessage("STAT_F_COMMENTS"),
		GetMessage("STAT_F_LOGIC"),
	)
);

$arFilterFields = Array(
	"find_id",  "find_id_exact_match",
	"find_site_id",
	"find_date_start_1",
	"find_date_start_2",
	"find_date_end_1",
	"find_date_end_2",
	"find_active",
	"find_save_statistic",
	"find_ip_1",
	"find_ip_2",
	"find_ip_3",
	"find_ip_4","find_ip_exact_match",
	"find_user_agent", "find_user_agent_exact_match",
	"find_url_from","find_url_from_exact_match",
	"find_url_to","find_url_to_exact_match",
	"find_url_redirect","find_url_redirect_exact_match",
	"find_message","find_message_exact_match",
	"find_comments","find_comments_exact_match",
	"FILTER_logic",
);

$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_user_agent_exact_match);
InitBVar($find_url_from_exact_match);
InitBVar($find_url_to_exact_match);
InitBVar($find_url_redirect_exact_match);
InitBVar($find_comments_exact_match);
InitBVar($find_message_exact_match);

AdminListCheckDate($lAdmin, array("find_date_start_1"=>$find_date_start_1, "find_date_start_2"=>$find_date_start_2));
AdminListCheckDate($lAdmin, array("find_date_end_1"=>$find_date_end_1, "find_date_end_2"=>$find_date_end_2));

$arFilter = Array(
	"ID"			=> $find_id,
	"SITE_ID"		=> $find_site_id,
	"DATE_START_1"		=> $find_date_start_1,
	"DATE_START_2"		=> $find_date_start_2,
	"DATE_END_1"		=> $find_date_end_1,
	"DATE_END_2"		=> $find_date_end_2,
	"ACTIVE"		=> $find_active,
	"SAVE_STATISTIC"	=> $find_save_statistic,
	"IP_1"			=> $find_ip_1,
	"IP_2"			=> $find_ip_2,
	"IP_3"			=> $find_ip_3,
	"IP_4"			=> $find_ip_4,
	"USER_AGENT"		=> $find_user_agent,
	"URL_FROM"		=> $find_url_from,
	"URL_TO"		=> $find_url_to,
	"URL_REDIRECT"		=> $find_url_redirect,
	"COMMENTS"		=> $find_comments,
	"MESSAGE"		=> $find_message,

	"ID_EXACT_MATCH"		=> $find_id_exact_match,
	"IP_1_EXACT_MATCH"		=> $find_ip_exact_match,
	"IP_2_EXACT_MATCH"		=> $find_ip_exact_match,
	"IP_3_EXACT_MATCH"		=> $find_ip_exact_match,
	"IP_4_EXACT_MATCH"		=> $find_ip_exact_match,
	"USER_AGENT_EXACT_MATCH"	=> $find_user_agent_exact_match,
	"URL_FROM_EXACT_MATCH"		=> $find_url_from_exact_match,
	"URL_TO_EXACT_MATCH"		=> $find_url_to_exact_match,
	"URL_REDIRECT_EXACT_MATCH"	=> $find_url_redirect_exact_match,
	"COMMENTS_EXACT_MATCH"		=> $find_comments_exact_match,
	"MESSAGE_EXACT_MATCH"		=> $find_message_exact_match,
);

if ($lAdmin->EditAction())
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$obStopList = new CStoplist;
		if(!$obStopList->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
		}
	}
}

if(($arID = $lAdmin->GroupAction()) && $STAT_RIGHT>="W")
{
	if($_REQUEST['action_target'] == "selected")
	{
		$rsData = CStoplist::GetList('', '', $arFilter);
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
			$obStopList = new CStoplist;
			$obStopList->Delete($ID);
			break;
		case "activate":
		case "deactivate":
			$obStopList = new CStoplist;
			if(!$obStopList->SetActive($ID, $_REQUEST['action']=="activate"? "Y": "N"))
			{
				if($e = $APPLICATION->GetException())
				{
					$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
				}
			}
			break;
		}
	}
}

global $by, $order;

$rsData = CStoplist::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_STOP_PAGES")));

$arHeaders = Array();

$arHeaders[] = array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true,);
$arHeaders[] = array("id"=>"LAMP", "content"=>GetMessage("STAT_RECORD_STATUS"), "default"=>true, "align" => "center");

$arHeaders[] = array("id"=>"DATE_START", "content"=>GetMessage("STAT_DATE_START"), "sort"=>"s_date_start", "default"=>true,);
$arHeaders[] = array("id"=>"DATE_END", "content"=>GetMessage("STAT_DATE_END"), "sort"=>"s_date_end", "default"=>false,);
$arHeaders[] = array("id"=>"ACTIVE", "content"=>GetMessage("STAT_ACTIVE"), "sort"=>"s_active", "default"=>true,);
$arHeaders[] = array("id"=>"SITE_ID", "content"=>GetMessage("STAT_SITE"), "sort"=>"s_site_id", "default"=>true,);
$arHeaders[] = array("id"=>"IP", "content"=>GetMessage("STAT_IP"), "sort"=>"s_ip", "default"=>true,);
$arHeaders[] = array("id"=>"MASK", "content"=>GetMessage("STAT_MASK"), "sort"=>"s_mask", "default"=>true,);
$arHeaders[] = array("id"=>"URL_FROM", "content"=>GetMessage("STAT_REFERER"), "sort"=>"s_url_from", "default"=>false,);
$arHeaders[] = array("id"=>"URL_TO", "content"=>GetMessage("STAT_URL_TO"), "sort"=>"s_url_to", "default"=>false,);
$arHeaders[] = array("id"=>"SAVE_STATISTIC", "content"=>GetMessage("STAT_STAT"), "sort"=>"s_save_statistic", "default"=>true,);
$arHeaders[] = array("id"=>"USER_AGENT", "content"=>GetMessage("STAT_USER_AGENT"), "default"=>false,);

$lAdmin->AddHeaders($arHeaders);


while($arRes = $rsData->NavNext(true, "f_"))
{

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$alt = ($f_MESSAGE <> '') ? "\n".GetMessage("STAT_MESSAGE").":  ".$f_MESSAGE : "";
	$alt .= ($f_COMMENTS <> '') ? "\n".GetMessage("STAT_COMMENT").":  ".$f_COMMENTS : "";
	$alt .= ($f_URL_REDIRECT <> '') ? "\n".GetMessage("STAT_REDIRECT").":  ".$f_URL_REDIRECT : "";
	if ($f_LAMP=="green") :
		$str = '<div class="lamp-green" title="'.GetMessage("STAT_LAMP_ACTIVE").' '.$alt.'"></div>';

	elseif ($f_LAMP=="red") :
		$str = '<div class="lamp-red" title="'.GetMessage("STAT_LAMP_NOT_ACTIVE").' '.$alt.'"></div>';
	endif;

	$row->AddViewField("LAMP", $str);


	if ($f_SITE_ID <> '') :
		$row->AddViewField("SITE_ID", '<a href="/bitrix/admin/site_edit.php?LID='.$f_SITE_ID.'&lang='.LANGUAGE_ID.'">'.$f_SITE_ID.'</a>');

	else:
		$row->AddViewField("SITE_ID", GetMessage("MAIN_ALL"));
	endif;

	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("SAVE_STATISTIC");


	if ($row->bEditMode)
	{
		$str = $str2 = "";

		for ($i = 1; $i <=4; $i++)
		{
			if ($row->VarsFromForm() && $_REQUEST["FIELDS"])
			{
				$val = $_REQUEST["FIELDS"][$f_ID]["f_IP_".$i];
				$val2 = $_REQUEST["FIELDS"][$f_ID]["f_MASK_".$i];
			}
			else
			{
				$val = ${"f_IP_".$i};
				$val2 = ${"f_MASK_".$i};
			}

			$str .= '
			<input type="text" maxlength="3" size="3" name="FIELDS['.htmlspecialcharsbx($f_ID).'][IP_'.$i.']" value="'.htmlspecialcharsbx($val).'">
			<input type="hidden" name="FIELDS_OLD['.htmlspecialcharsbx($f_ID).'][IP_'.$i.']" value="'.htmlspecialcharsbx(${"f_IP_".$i}).'">';

			$str2 .= '
			<input type="text" maxlength="3" size="3" name="FIELDS['.htmlspecialcharsbx($f_ID).'][MASK_'.$i.']" value="'.htmlspecialcharsbx($val2).'">
			<input type="hidden" name="FIELDS_OLD['.htmlspecialcharsbx($f_ID).'][MASK_'.$i.']" value="'.htmlspecialcharsbx(${"f_MASK_".$i}).'">';
		}
		$row->AddEditField("IP", "<nobr>".$str."</nobr>");
		$row->AddEditField("MASK", "<nobr>".$str2."</nobr>");
	}
	else
	{

		$row->AddViewField("IP", intval($f_IP_1).".".intval($f_IP_2).".".intval($f_IP_3).".".intval($f_IP_4));

		$row->AddViewField("MASK", $f_MASK_1.".".$f_MASK_2.".".$f_MASK_3.".".$f_MASK_4);
	}

	if ($f_URL_FROM <> '')
	{
		$row->AddViewField("URL_FROM", StatAdminListFormatURL($arRes["URL_FROM"], array(
			"new_window" => false,
			"max_display_chars" => "default",
			"chars_per_line" => "default",
			"kill_sessid" => $STAT_RIGHT < "W",
		)));
	}

	if ($f_URL_TO <> '')
	{
		$row->AddViewField("URL_TO", StatAdminListFormatURL($arRes["URL_TO"], array(
			"new_window" => false,
			"max_display_chars" => "default",
			"chars_per_line" => "default",
			"kill_sessid" => $STAT_RIGHT < "W",
		)));
	}

	$arActions = Array();
		$arActions[] = array(
			"DEFAULT" => "Y",
			"ICON"=>"edit",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
			"ACTION"=>$lAdmin->ActionRedirect("stoplist_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"))
		);

	if ($STAT_RIGHT>="W")
	{
		$arActions[] = array("SEPARATOR"=>true);

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION"=>"if(confirm('".GetMessageJS('STAT_CONFIRM_DEL_STOP')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($STAT_RIGHT>="W")
{
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
	));
}

$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("STAT_ADD"),
		"LINK"=>"stoplist_edit.php?lang=".LANG.GetFilterParams("filter_"),
		"TITLE"=>GetMessage("STAT_ADD")
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr>
	<td><?echo GetMessage("STAT_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top">
	<td valign="top"><?=GetMessage("STAT_F_SITE")?>:<br><img src="/bitrix/images/statistic/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
	$ref = array();
	$ref_id = array();
	$rs = CSite::GetList();
	while ($ar = $rs->Fetch())
	{
		$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
		$ref_id[] = $ar["ID"];
	}
	echo SelectBoxMFromArray("find_site_id[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site_id, "",false,"3");
	?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap>
		<?echo GetMessage("STAT_F_DATE_START").":"?></td>
	<td width="0%" nowrap>
		<?echo CalendarPeriod("find_date_start_1", htmlspecialcharsbx($find_date_start_1), "find_date_start_2", htmlspecialcharsbx($find_date_start_2), "form1","Y")?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap>
		<?echo GetMessage("STAT_F_DATE_END").":"?></td>
	<td width="0%" nowrap>
		<?echo CalendarPeriod("find_date_end_1", htmlspecialcharsbx($find_date_end_1), "find_date_end_2", htmlspecialcharsbx($find_date_end_2), "form1","Y")?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap>
		<?echo GetMessage("STAT_F_ACTIVE")?>:</td>
	<td width="0%" nowrap>
		<?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap>
		<?echo GetMessage("STAT_F_STATISTIC")?>:</td>
	<td width="0%" nowrap>
		<?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_save_statistic", $arr, htmlspecialcharsbx($find_save_statistic), GetMessage("MAIN_ALL"));
		?></td>
</tr>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_IP")?>:</td>
	<td width="0%" nowrap>
		<input type="text" name="find_ip_1" value="<?echo htmlspecialcharsbx($find_ip_1)?>" size="6" >
		<input type="text" name="find_ip_2" value="<?echo htmlspecialcharsbx($find_ip_2)?>" size="6" >
		<input type="text" name="find_ip_3" value="<?echo htmlspecialcharsbx($find_ip_3)?>" size="6" >
		<input type="text" name="find_ip_4" value="<?echo htmlspecialcharsbx($find_ip_4)?>" size="7" ><?=ShowExactMatchCheckbox("find_ip")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_USER_AGENT")?>:</td>
	<td width="0%" nowrap><input type="text" name="find_user_agent" value="<?echo htmlspecialcharsbx($find_user_agent)?>" size="47"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_FROM")?>:</td>
	<td width="0%" nowrap><input type="text" name="find_url_from" value="<?echo htmlspecialcharsbx($find_url_from)?>" size="47"><?=ShowExactMatchCheckbox("find_url_from")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_TO")?>:</td>
	<td width="0%" nowrap><input type="text" name="find_url_to" value="<?echo htmlspecialcharsbx($find_url_to)?>" size="47"><?=ShowExactMatchCheckbox("find_url_to")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_REDIRECT")?>:</td>
	<td width="0%" nowrap><input type="text" name="find_url_redirect" value="<?echo htmlspecialcharsbx($find_url_redirect)?>" size="47"><?=ShowExactMatchCheckbox("find_url_redirect")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_MESSAGE")?>:</td>
	<td width="0%" nowrap><input type="text" name="find_message" value="<?echo htmlspecialcharsbx($find_message)?>" size="47"><?=ShowExactMatchCheckbox("find_message")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_COMMENTS")?>:</td>
	<td width="0%" nowrap><input type="text" name="find_comments" value="<?echo htmlspecialcharsbx($find_comments)?>" size="47"><?=ShowExactMatchCheckbox("find_comments")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?=ShowLogicRadioBtn()?>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?echo BeginNote();?>
<table border="0" width="100%" cellspacing="3" cellpadding="0">
	<tr valign="center" nowrap>
		<td width="0%"><div class="lamp-green"></div></td>
		<td width="100%">- <?echo GetMessage("STAT_GREEN_LAMP")?></td>
	</tr>
	<tr valign="center" nowrap>
		<td width="0%"><div class="lamp-red"></div></td>
		<td width="100%">- <?echo GetMessage("STAT_RED_LAMP")?></td>
	</tr>
</table>
<?echo EndNote();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
