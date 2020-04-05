<?
##############################################
# Bitrix Site Manager Forum					 #
# Copyright (c) 2002-2010 Bitrix			 #
# http://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$voteId = intval($request->getQuery("VOTE_ID"));
$sTableID = "tbl_vote_votes_table".$voteId;
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");

CModule::IncludeModule("vote");
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE", array("#ID#" => $voteId)));

try
{
	$vote = \Bitrix\Vote\Vote::loadFromId($voteId);
	global $USER;
	if (!$vote->canRead($USER->GetID()))
		throw new \Bitrix\Main\ArgumentException(GetMessage("ACCESS_DENIED"), "Access denied.");
}
catch(Exception $e)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($e->getMessage());
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

/********************************************************************
				Functions
********************************************************************/
function CheckFilter()
{
	global $arFilterFields,$lAdmin;
	foreach ($arFilterFields as $s) global $$s;

	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	$bGotErr = false;
	$find_date_1 = trim($request->getQuery("find_date_1"));
	$find_date_2 = trim($request->getQuery("find_date_2"));

	if (strlen($find_date_1)>0 || strlen($find_date_2)>0)
	{
		$date_1_stm = MkDateTime(ConvertDateTime($find_date_1,"D.M.Y"),"d.m.Y");
		$date_2_stm = MkDateTime(ConvertDateTime($find_date_2,"D.M.Y")." 23:59:59","d.m.Y H:i:s");
		if (!$date_1_stm && strlen(trim($find_date_1))>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_DATE_FROM"));
		}

		if (!$date_2_stm && strlen(trim($find_date_2))>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_DATE_TILL"));
		}

		if (!$bGotErr && $date_2_stm <= $date_1_stm && strlen($date_2_stm)>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_FROM_TILL"));
		}
	}
	return !$bGotErr;
}

/***************************************************************************
				Actions
****************************************************************************/
$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_valid",
	"find_date_1",
	"find_date_2",
	"find_vote_user",
	"find_vote_user_exact_match",
	"find_session",
	"find_session_exact_match",
	"find_ip",
	"find_ip_exact_match"
	);

InitBVar($find_id_exact_match);
InitBVar($find_vote_exact_match);
InitBVar($find_vote_user_exact_match);
InitBVar($find_session_exact_match);
InitBVar($find_ip_exact_match);

$lAdmin->InitFilter($arFilterFields);
if (CheckFilter())
{
	$arFilter = Array(
		"ID"						=> $find_id,
		"ID_EXACT_MATCH"			=> $find_id_exact_match,
		"VALID"						=> $find_valid,
		"VOTE_ID"					=> $voteId,
		"DATE_1"					=> $find_date_1,
		"DATE_2"					=> $find_date_2,
		"VOTE_USER"					=> $find_vote_user,
		"VOTE_USER_EXACT_MATCH"		=> $find_vote_user_exact_match,
		"SESSION"					=> $find_session,
		"SESSION_EXACT_MATCH"		=> $find_session_exact_match,
		"IP"						=> $find_ip,
		"IP_EXACT_MATCH"			=> $find_ip_exact_match
		);
}
// if submit "Save"
if ($lAdmin->EditAction() && $VOTE_RIGHT>="W" && check_bitrix_sessid())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = IntVal($ID);
		InitBVar($arFields["VALID"]);
		$arFieldsStore = Array(
			"VALID"	=> "'$arFields[VALID]'",
			);
		if (!$DB->Update("b_vote_event",$arFieldsStore,"WHERE ID='$ID'",$err_mess.__LINE__))
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".GetMessage("VOTE_SAVE_ERROR"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}
// Groups action
if(($arID = $lAdmin->GroupAction()) && $VOTE_RIGHT=="W" && check_bitrix_sessid())
{
		if($_REQUEST['action_target']=='selected')
		{
				$arID = Array();
				$rsData = CVoteEvent::GetList($by, $order, $arFilter, $is_filtered);
				while($arRes = $rsData->Fetch())
						$arID[] = $arRes['ID'];
		}

		foreach($arID as $ID)
		{
				if(strlen($ID)<=0)
						continue;
				$ID = IntVal($ID);
				switch($_REQUEST['action'])
				{
				case "delete":
						if(!CVoteEvent::Delete($ID)):
							$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
						endif;
						break;
				case "validate":
				case "devalidate":
						$varVALID = ($_REQUEST['action']=="validate"?"Y":"N");
						CVoteEvent::SetValid($ID, $varVALID);
						break;
				}
		}
}


/************** Initial list - Get data ****************************/
$nameFormat = CSite::GetNameFormat(false);
$rsData = new CAdminResult(CVoteEvent::GetList($by, $order, $arFilter, $is_filtered, "Y"), $sTableID);
$rsData->NavStart();

/************** Initial list - Navigation **************************/
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("VOTE_PAGES")));
$headers = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
	array("id"=>"VOTE_USER_ID", "content"=>GetMessage("VOTE_VISITOR"), "sort"=>"s_vote_user", "default"=>true),
	array("id"=>"USER", "content"=>GetMessage("VOTE_USER"), "default"=>true),
	array("id"=>"STAT_SESSION_ID", "content"=>GetMessage("VOTE_SESSION"), "sort"=>"s_session", "default"=>true),
	array("id"=>"IP", "content"=>"IP", "sort"=>"s_ip", "default"=>true),
	array("id"=>"DATE_VOTE", "content"=>GetMessage("VOTE_DATE"), "sort"=>"s_date", "default"=>true),
	array("id"=>"VALID", "content"=>GetMessage("VOTE_VALID"), "sort"=>"s_valid", "default"=>true)
);
$by = 'c_sort';
$order = 'asc';
$arAllQuestions = array();
$rsQuestions = CVoteQuestion::GetList($voteId, $by, $order, array(), $is_filtered);
while ($arQuestion = $rsQuestions->Fetch())
{
	$headers[] = array(
		"id"=>"Q".$arQuestion["ID"],
		"content"=>htmlspecialcharsbx($arQuestion["QUESTION"]),
		"sort"=>'',
		"default"=>true);

	$arAllAnswers = array();
	$rsAnswers = CVoteAnswer::GetList($arQuestion["ID"]);
	while ($arAnswer = $rsAnswers->Fetch())
	{
		$arAllAnswers[$arAnswer['ID']]=$arAnswer;
	}
	$arAllQuestions[] = array('ID' => $arQuestion["ID"], 'ANSWERS' => $arAllAnswers);
}

$lAdmin->AddHeaders($headers);

$arrUsers = array();
while($res = $rsData->getNext())
{
	$row =& $lAdmin->AddRow($res["ID"], $res);

	$row->AddViewField("VALID", ($res["VALID"] == "Y" ? GetMessage("MAIN_YES") : GetMessage("MAIN_NO")));
	$row->AddViewField("VOTE_USER_ID", "<a href=\"vote_user_list.php?lang=".LANGUAGE_ID."&find_id={$res["VOTE_USER_ID"]}&set_filter=Y\">{$res["VOTE_USER_ID"]}</a>");
	if ($res["AUTH_USER_ID"] > 0)
		$row->AddViewField("USER", "[<a href=\"user_admin.php?lang=".LANGUAGE_ID."&ID={$res["AUTH_USER_ID"]}&apply_filter=Y\">{$res["AUTH_USER_ID"]}</a>] " . CUser::FormatName($nameFormat, $res, true, false));
	else
		$row->AddViewField("USER", GetMessage("VOTE_NONAUTHORIZED"));

	if (CModule::IncludeModule("statistic"))
		$row->AddViewField("STAT_SESSION_ID","<a title=\"".GetMessage("VOTE_SESSIONU_LIST_TITLE")."\" href=\"session_list.php?lang=".LANGUAGE_ID."&find_id={$res["STAT_SESSION_ID"]}&set_filter=Y\">{$res["STAT_SESSION_ID"]}</a>");

	if (strlen($res["TITLE"])>0)
		$txt = "[<a title='".GetMessage("VOTE_EDIT_TITLE")."' href='vote_edit.php?lang=".LANGUAGE_ID."&ID={$res["VOTE_ID"]}'>{$res["VOTE_ID"]}</a>] {$res["TITLE"]}";
	elseif ($res["DESCRIPTION_TYPE"]=="html")
		$txt = "[<a title='".GetMessage("VOTE_EDIT_TITLE")."' href='vote_edit.php?lang=".LANGUAGE_ID."&ID={$res["VOTE_ID"]}'>{$res["VOTE_ID"]}</a>] ".TruncateText(strip_tags(htmlspecialcharsback($res["DESCRIPTION"])),50);
	else
		$txt = "[<a href='vote_edit.php?lang=".LANGUAGE_ID."&ID={$res["VOTE_ID"]}'>{$res["VOTE_ID"]}</a>] ".TruncateText($res["DESCRIPTION"],50);

	$row->AddViewField("TITLE", $txt);

	foreach ($arAllQuestions as $arQuestion)
	{
		$txt = '';
		foreach($arQuestion["ANSWERS"] as $arAnswer)
		{
			if ($msg = CVoteEvent::GetAnswer($res['ID'], $arAnswer['ID']))
			{
				if (
					($arAnswer['FIELD_TYPE'] < 4) // not a string
					&& (intval($msg) > 0)
				)
					$msg = $arAnswer['MESSAGE'];
				$txt .= htmlspecialcharsbx($msg).'<br />';
			}
		}
		$row->AddViewField("Q".$arQuestion["ID"], $txt);
	}

	$arActions = Array();
		$arActions[] = array("DEFAULT"=>true, "ICON"=>"view", "TEXT"=>GetMessage("VOTE_RESULT"), "ACTION"=>$lAdmin->ActionRedirect("vote_user_results_table.php?lang=".LANGUAGE_ID."&VOTE_ID=".$res["VOTE_ID"]."&EVENT_ID=".$res["ID"]));

	if ($VOTE_RIGHT=="W")
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("VOTE_DELETE_U"),
			"ACTION" => "if(confirm('".GetMessage('VOTE_DELETE_CONFIRMATION')."')) ".$lAdmin->ActionDoGroup($res["ID"], "delete", 'VOTE_ID='.$voteId));

		$row->AddActions($arActions);
}

/************** Initial list - Footer ******************************/
$lAdmin->AddFooter(
		array(
				array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
				array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
);
/************** Initial list - Buttons *****************************/
$lAdmin->AddAdminContextMenu(array(), true);
if ($VOTE_RIGHT=="W")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("VOTE_DELETE"),
		"validate"=>GetMessage("VOTE_VALIDATE"),
		"devalidate"=>GetMessage("VOTE_DEVALIDATE"),
	));
/************** Initial list - Check AJAX **************************/
$lAdmin->CheckListMode();

/********************************************************************
				Html form
********************************************************************/
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$toolbar = array(
		array(
			"TEXT"	=> GetMessage("VOTE_BACK_TO_VOTE"),
			"LINK"	=> ($vote->canEdit($USER->GetID()) ? "/bitrix/admin/vote_edit.php?lang=".LANGUAGE_ID."&ID=".$voteId : "/bitrix/admin/vote_preview.php?lang=".LANGUAGE_ID."&VOTE_ID=".$voteId),
			"ICON" => "btn_list"
		)
	);
if ($vote["COUNTER"] > 0)
{
	array_push($toolbar, array(
			"TEXT" => GetMessage("VOTE_VOTES_DROPDOWN", array("COUNTER" => $vote["COUNTER"])),
			"MENU" => array(
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_GOTO_VIEW"),
					"LINK"	=> "/bitrix/admin/vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID=".$voteId),
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_EXPORT"),
					"LINK"	=> "vote_user_votes.php?lang=".LANGUAGE_ID."&find_vote_id=$voteId&export=xls",
					"ICON" => "btn_excel"),
				array(
					"TEXT"	=> GetMessage("VOTE_VOTES_EXPORT_2"),
					"LINK"	=> "vote_user_votes_table.php?lang=".LANGUAGE_ID."&VOTE_ID=$voteId&mode=excel",
					"ICON" => "btn_excel"),

			))
	);
}
(new CAdminContextMenu($toolbar))->Show();

?>
<a name="tb"></a>

<?echo ShowError($strError);?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
		GetMessage("VOTE_FL_USER_ID"),
		GetMessage("VOTE_FL_SESS_ID"),
		GetMessage("VOTE_FL_IP"),
		GetMessage("VOTE_FL_DATE"),
		GetMessage("VOTE_FL_VALID"),
		)
);

$oFilter->Begin();
?>
<tr>
	<td><b>ID</b></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("VOTE_F_USER")?></td>
	<td><input type="text" name="find_vote_user" size="47" value="<?echo htmlspecialcharsbx($find_vote_user)?>"><?=InputType("checkbox", "find_vote_user_exact_match", "Y", $find_vote_user_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("VOTE_F_SESSION")?></td>
	<td><input type="text" name="find_session" size="47" value="<?echo htmlspecialcharsbx($find_session)?>"><?=InputType("checkbox", "find_session_exact_match", "Y", $find_session_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>IP</td>
	<td><input type="text" name="find_ip" size="47" value="<?echo htmlspecialcharsbx($find_ip)?>"><?=InputType("checkbox", "find_ip_exact_match", "Y", $find_ip_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_DATE").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_date_1", $find_date_1, "find_date_2", $find_date_2, "form1","Y")?></td>
</tr>
<tr valign="top">
	<td nowrap><?echo GetMessage("VOTE_F_VALID_TITLE")?></td>
	<td nowrap><input type="checkbox" name="find_valid" id="find_valid" value="Y" <?=($find_valid == "Y" ? "checked='checked'" : "")?> />
		<label for="find_valid"><?=GetMessage("VOTE_F_VALID")?></label><?
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPageParam(), "form"=>"form1"));
$oFilter->End();
#############################################################
?>
</form>
<?
/************** Initial list - Display list ************************/
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
