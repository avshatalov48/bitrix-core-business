<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$sTableID = "tbl_vote_user";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
ClearVars();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

/********************************************************************
				Functions
********************************************************************/
function CheckFilter()
{
	global $message, $lAdmin, $arFilterFields;
	foreach ($arFilterFields as $s) global $$s;
	$bGotErr = false;

	$find_date_start_1 = trim($find_date_start_1);
	$find_date_start_2 = trim($find_date_start_2);
	$find_date_end_1 = trim($find_date_end_1);
	$find_date_end_2 = trim($find_date_end_2);

	if (strlen($find_date_start_1)>0 || strlen($find_date_start_2)>0)
	{
		$date_start_1_stm = MkDateTime(ConvertDateTime($find_date_start_1,"D.M.Y"),"d.m.Y");
		$date_start_2_stm = MkDateTime(ConvertDateTime($find_date_start_2,"D.M.Y")." 23:59:59","d.m.Y H:i:s");
		if (!$date_start_1_stm && strlen(trim($find_date_start_1))>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_START_DATE_FROM"));
		}
		if (!$date_start_2_stm && strlen(trim($find_date_start_2))>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_START_DATE_TILL"));
		}
		if (!$bGotErr && $date_start_2_stm <= $date_start_1_stm && strlen($date_start_2_stm)>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_START_FROM_TILL"));
		}
	}

	if (strlen($find_date_end_1)>0 || strlen($find_date_end_2)>0)
	{
		$date_end_1_stm = MkDateTime(ConvertDateTime($find_date_end_1,"D.M.Y"),"d.m.Y");
		$date_end_2_stm = MkDateTime(ConvertDateTime($find_date_end_2,"D.M.Y")." 23:59:59","d.m.Y H:i:s");
		if (!$date_end_1_stm && strlen(trim($find_date_end_1))>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_END_DATE_FROM"));
		}
		if (!$date_end_2_stm && strlen(trim($find_date_end_2))>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_END_DATE_TILL"));
		}
		if ($bGotErr && $date_end_2_stm <= $date_end_1_stm && strlen($date_end_2_stm)>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_END_FROM_TILL"));
		}
	}

	if ($bGotErr) return false; else return true;
}
/********************************************************************
				Actions
********************************************************************/
$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_date_start_1",
	"find_date_start_2",
	"find_date_end_1",
	"find_date_end_2",
	"find_counter_1",
	"find_counter_2",
	"find_user",
	"find_user_exact_match",
	"find_guest",
	"find_guest_exact_match",
	"find_ip",
	"find_ip_exact_match",
	"find_vote",
	"find_vote_exact_match",
	"find_vote_id"
	);

$lAdmin->InitFilter($arFilterFields);

InitBVar($find_id_exact_match);
InitBVar($find_user_exact_match);
InitBVar($find_guest_exact_match);
InitBVar($find_ip_exact_match);
InitBVar($find_vote_exact_match);

if (CheckFilter())
{
	$arFilter = Array(
		"ID"				=> $find_id,
		"ID_EXACT_MATCH"	=> $find_id_exact_match,
		"DATE_START_1"		=> $find_date_start_1,
		"DATE_START_2"		=> $find_date_start_2,
		"DATE_END_1"		=> $find_date_end_1,
		"DATE_END_2"		=> $find_date_end_2,
		"COUNTER_1"			=> $find_counter_1,
		"COUNTER_2"			=> $find_counter_2,
		"USER"				=> $find_user,
		"USER_EXACT_MATCH"	=> $find_user_exact_match,
		"GUEST"				=> $find_guest,
		"GUEST_EXACT_MATCH"	=> $find_guest_exact_match,
		"IP"				=> $find_ip,
		"IP_EXACT_MATCH"	=> $find_ip_exact_match,
		"VOTE"				=> $find_vote,
		"VOTE_EXACT_MATCH"	=> $find_vote_exact_match,
		"VOTE_ID"			=> $find_vote_id
		);
}

if(($arID = $lAdmin->GroupAction()) && $VOTE_RIGHT=="W" && check_bitrix_sessid())
{
	if($_REQUEST['action_target']=='selected')
	{
			$arID = Array();
			$rsData = CVoteUser::GetList($by, $order, $arFilter, $is_filtered);
			while($arRes = $rsData->Fetch())
					$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if (strlen($ID)<=0)
			continue;
		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!CVoteUser::Delete($ID))
				{
						$DB->Rollback();
						$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
				}
				$DB->Commit();
				break;
		}
	}
}

$rsData = CVoteUser::GetList($by, $order, $arFilter, $is_filtered);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("VOTE_PAGES")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
	array("id"=>"DATE_FIRST", "content"=>GetMessage("VOTE_DATE_START"), "sort"=>"s_date_start", "default"=>true),
	array("id"=>"DATE_LAST", "content"=>GetMessage("VOTE_DATE_END"), "sort"=>"s_date_end", "default"=>true),
	array("id"=>"AUTH_USER_ID", "content"=>GetMessage("VOTE_USER"), "sort"=>"s_user", "default"=>true),
	(CModule::IncludeModule("statistic") ? array("id"=>"STAT_GUEST_ID", "content"=>GetMessage("VOTE_VISITOR"), "sort"=>"s_stat_guest_id", "default"=>true) : null),
	array("id"=>"LAST_IP", "content"=>"IP", "sort"=>"s_ip", "default"=>true),
	array("id"=>"COUNTER", "content"=>GetMessage("VOTE_COUNTER"), "sort"=>"s_counter", "default"=>true, "align"=>"right"),
));

$nameFormat = CSite::GetNameFormat(false);
while ($res = $rsData->getNext())
{
	$row =& $lAdmin->AddRow($res["ID"], $res);

	if ($res["AUTH_USER_ID"]>0)
		$txt="[<a title=\"".GetMessage("VOTE_EDIT_USER")."\" href=\"user_admin.php?lang=".LANGUAGE_ID."&ID={$res["AUTH_USER_ID"]}&apply_filter=Y\">{$res["AUTH_USER_ID"]}</a>] ".CUser::FormatName($nameFormat, $res, true, false);
	else
		$txt=GetMessage("VOTE_NOT_AUTHORIZED");
	$row->AddViewField("AUTH_USER_ID", $txt);

	if ($res["STAT_GUEST_ID"] > 0)
		$row->AddViewField("STAT_GUEST_ID", "[<a title=\"".GetMessage("VOTE_GUEST_USER_INFO")."\" href=\"guest_list.php?lang=".LANGUAGE_ID."&find_id={$res["STAT_GUEST_ID"]}&set_filter=Y\">{$res["STAT_GUEST_ID"]}</a>]");

	$row->AddViewField("COUNTER", "<a title=\"".GetMessage("VOTE_USER_VOTES")."\" href=\"vote_user_votes.php?find_vote_user_id={$res["ID"]}&lang=".LANGUAGE_ID."&set_filter=Y\">{$res["COUNTER"]}</a>");

	if ($VOTE_RIGHT=="W")
	{
		$row->AddActions(
			array(
				array(
					"ICON" => "delete",
					"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
					"ACTION" => "if(confirm('".GetMessage("VOTE_CONFIRM_DEL")."')) window.location='vote_user_list.php?lang=".LANGUAGE_ID."&action=delete&ID={$res["ID"]}&".bitrix_sessid_get()."'"
				)
			)
		);
	}
}

$lAdmin->AddFooter(
	array(
		array(
			"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value"=>$rsData->SelectedRowsCount()
		),
		array(
			"counter"=>true,
			"title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value"=>"0"
		)
	)
);

if ($VOTE_RIGHT=="W")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("VOTE_DELETE"),
		)
	);

$lAdmin->AddAdminContextMenu(array());

$lAdmin->CheckListMode();
/********************************************************************
				Form
********************************************************************/
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<a name="tb"></a>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("VOTE_FL_ID"),
		GetMessage("VOTE_FL_DATE_ST"),
		GetMessage("VOTE_FL_DATE_LS"),
		GetMessage("VOTE_FL_ID_STAT"),
		GetMessage("VOTE_FL_IP"),
		GetMessage("VOTE_FL_COUNTER"),
		GetMessage("VOTE_FL_VOTE")
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?echo GetMessage("VOTE_F_USER")?></b></td>
	<td><input type="text" name="find_user" size="47" value="<?echo htmlspecialcharsbx($find_user)?>"><?=InputType("checkbox", "find_user_exact_match", "Y", $find_user_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>ID:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_DATE_START").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_date_start_1", htmlspecialcharsbx($find_date_start_1), "find_date_start_2", htmlspecialcharsbx($find_date_start_2), "form1","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_DATE_END").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_date_end_1", htmlspecialcharsbx($find_date_end_1), "find_date_end_2", htmlspecialcharsbx($find_date_end_2), "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("VOTE_F_GUEST_ID")?></td>
	<td><input type="text" name="find_guest" size="47" value="<?echo htmlspecialcharsbx($find_guest)?>"><?=InputType("checkbox", "find_guest_exact_match", "Y", $find_guest_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("VOTE_F_IP")?></td>
	<td><input type="text" name="find_ip" size="47" value="<?echo htmlspecialcharsbx($find_ip)?>"><?=InputType("checkbox", "find_ip_exact_match", "Y", $find_ip_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_COUNTER")?></td>
	<td nowrap><input type="text" name="find_counter_1" value="<?=htmlspecialcharsbx($find_counter_1)?>" size="10"><?echo "&nbsp;".GetMessage("VOTE_TILL")."&nbsp;"?><input type="text" name="find_counter_2" value="<?=htmlspecialcharsbx($find_counter_2)?>" size="10"></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_VOTE")?></td>
	<td nowrap><input type="text" name="find_vote" size="47" value="<?echo htmlspecialcharsbx($find_vote)?>"><?=InputType("checkbox", "find_vote_exact_match", "Y", $find_vote_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?><br><?echo SelectBox("find_vote_id", CVote::GetDropDownList(), GetMessage("VOTE_ALL"), htmlspecialcharsbx($find_vote_id));?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$oFilter->End();
#############################################################
?>
</form>
<?
$lAdmin->DisplayList();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>