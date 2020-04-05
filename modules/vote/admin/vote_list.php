<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 - 2016 Bitrix           #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
 * @global Cmain $APPLICATION
 * @global CUser $USER
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

ClearVars();
$sTableID = "tbl_vote";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("vote");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if ($VOTE_RIGHT <= "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

$db_res = \Bitrix\Vote\Channel::getList(array(
	'select' => array("*"),
	'filter' => ($VOTE_RIGHT < "W" ? array(
		"ACTIVE" => "Y",
		"HIDDEN" => "N",
		">=PERMISSION.PERMISSION" => 1,
		"PERMISSION.GROUP_ID" => $USER->GetUserGroupArray()
	) : array()),
	'order' => array(
		'TITLE' => 'ASC'
	),
	'group' => array("ID")
));
$arChannels = array();
$arChannelsTitle = array();
if ($db_res && $res = $db_res->GetNext())
{
	do
	{
		$arChannels[$res["ID"]] = $res;
		$arChannelsTitle[$res["ID"]] = html_entity_decode($res["TITLE"]);
	} while ($res = $db_res->GetNext());
}
$channelsToAdmin = array();
if ($VOTE_RIGHT >= "W")
	$channelsToAdmin = array_keys($arChannels);
else
{
	$db_res = \Bitrix\Vote\Channel::getList(array(
		'select' => array("ID"),
		'filter' => array(
			"ACTIVE" => "Y",
			"HIDDEN" => "N",
			">=PERMISSION.PERMISSION" => 4,
			"PERMISSION.GROUP_ID" => $USER->GetUserGroupArray()
		),
		'order' => array(
			'TITLE' => 'ASC'
		),
		'group' => array("ID")
	));
	while ($res = $db_res->fetch())
	{
		$channelsToAdmin[] = $res["ID"];
	}
}
$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_active",
	"find_date_start_1",
	"find_date_start_2",
	"find_date_end_1",
	"find_date_end_2",
	"find_lamp",
	"find_channel",
	"find_channel_exact_match",
	"find_channel_id",
	"find_title",
	"find_title_exact_match",
	"find_description",
	"find_description_exact_match",
	"find_counter_1",
	"find_counter_2");
$arFilter = ($VOTE_RIGHT < "W" ? array("@CHANNEL_ID" => array_keys($arChannels)) : array());
$lAdmin->InitFilter($arFilterFields);
global $by, $order;
/********************************************************************
				Functions
********************************************************************/
function CheckFilter()
{
	global $lAdmin;
	$bGotErr = false;

	$request = \Bitrix\Main\Context::getCurrent()->getRequest();
	$find_date_start_1 = trim($request->getQuery("find_date_start_1"));
	$find_date_start_2 = trim($request->getQuery("find_date_start_2"));
	$find_date_end_1 = trim($request->getQuery("find_date_end_1"));
	$find_date_end_2 = trim($request->getQuery("find_date_end_2"));

	if (strlen($find_date_start_1)>0 || strlen($find_date_start_2)>0)
	{
		// start date
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
		// end date
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
		if (!$bGotErr && $date_end_2_stm <= $date_end_1_stm && strlen($date_end_2_stm)>0)
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_END_FROM_TILL"));
		}
	}

	return ($bGotErr ? false : true);
}

/********************************************************************
				ACTIONS
********************************************************************/
if (intval($request->getQuery("reset_id")) > 0 && $VOTE_RIGHT >= "W" && check_bitrix_sessid()):
	CVote::Reset($request->getQuery("reset_id"));
endif;

if (CheckFilter() && $request->getQuery("del_filter") != "Y")
{
	if ($request->getQuery("find_id"))
	{
		$m = ($request->getQuery("find_id_exact_match") == "Y" ? "" : "%");
		$arFilter[($request->getQuery("find_id_exact_match") == "Y" ? "" : "~")."ID"] = $m.$request->getQuery("find_id").$m;
	}
	foreach (array("CHANNEL", "TITLE", "DESCRIPTION") as $k)
	{
		$n = strtolower($k);
		if ($request->getQuery("find_".$n))
		{
			$arFilter[$k] = $request->getQuery("find_".$n);
			if ($request->getQuery("ajax_post") == "Y")
				CUtil::decodeURIComponent($arFilter[$k]);
			$arFilter[$k."_EXACT_MATCH"] = $request->getQuery("find_".$n."_exact_match");
		}
	}
	if ($request->getQuery("find_active"))
		$arFilter["ACTIVE"] = $request->getQuery("find_active");
	if ($request->getQuery("find_date_start_1"))
		$arFilter[">=DATE_START"] = $request->getQuery("find_date_start_1");
	if ($request->getQuery("find_date_start_2"))
		$arFilter["<=DATE_START"] = $request->getQuery("find_date_start_2");
	if ($request->getQuery("find_date_end_1"))
		$arFilter[">=DATE_END"] = $request->getQuery("find_date_end_1");
	if ($request->getQuery("find_date_end_2"))
		$arFilter["<=DATE_END"] = $request->getQuery("find_date_end_2");
	if ($request->getQuery("find_lamp"))
		$arFilter["LAMP"] = $request->getQuery("find_lamp");
	if ($request->getQuery("find_channel_id") > 0)
		$arFilter["CHANNEL_ID"] = $request->getQuery("find_channel_id");
	if ($request->getQuery("find_counter_1"))
		$arFilter[">=COUNTER"] = $request->getQuery("find_counter_1");
	if ($request->getQuery("find_counter_2"))
		$arFilter["<=COUNTER"] = $request->getQuery("find_counter_2");
}
if ($lAdmin->EditAction() && $VOTE_RIGHT >= "W" && check_bitrix_sessid())
{
	$FIELDS = $request->getPost("FIELDS");
	foreach ($FIELDS as $ID => $arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$ID = intval($ID);
		$arFieldsStore = array(
			"ACTIVE" => $arFields['ACTIVE'],
			"C_SORT" => $arFields['C_SORT'],
			"TITLE" => $arFields['TITLE'],
			"CHANNEL_ID" => $arFields['CHANNEL_ID']);
		if (!CVote::CheckFields("UPDATE", $arFieldsStore, $ID)):
			$err = $APPLICATION->GetException();
			$lAdmin->AddUpdateError($ID.": ".$err->GetString(), $ID);
		elseif (!CVote::Update($ID, $arFieldsStore)):
			$lAdmin->AddUpdateError($ID.": ".GetMessage("VOTE_SAVE_ERROR"), $ID);
		endif;
	}
}
// Group actions
if(($arID = $lAdmin->GroupAction()) && $VOTE_RIGHT>="W" && check_bitrix_sessid())
{
	if($_REQUEST['action_target'] == 'selected')
	{
		$arID = array();
		$rsData = CVote::GetListEx(array($by => $order), $arFilter);
		while ($arRes = $rsData->fetch())
			$arID[] = $arRes['ID'];
	}
	$arID = (is_array($arID) ? $arID : array($arID));
	foreach($arID as $ID)
	{
		$ID = intval($ID);
		if ($ID <= 0)
			continue;
		switch($_REQUEST['action'])
		{
			case "delete":
				CVote::Delete($ID);
			break;
			case "activate":
			case "deactivate":
				if (!CVote::Update($ID, array("ACTIVE" => ($_REQUEST['action'] == "activate"? "Y" : "N")))):
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("VOTE_SAVE_ERROR"), $ID);
				endif;
			break;
		}
	}
}
/********************************************************************
		/ACTIONS
 ********************************************************************/

/********************************************************************
		Data
 ********************************************************************/
$rsData = CVote::GetListEx(array($by => $order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("VOTE_PAGES")));
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"LAMP", "content"=>GetMessage("VOTE_LAMP"), "default"=>true),
	array("id"=>"DATE_START", "content"=>GetMessage("VOTE_DATE_START"), "sort"=>"date_start", "default"=>true),
	array("id"=>"DATE_END", "content"=>GetMessage("VOTE_DATE_END"), "sort"=>"date_end", "default"=>true),
	array("id"=>"CHANNEL_ID", "content"=>GetMessage("VOTE_CHANNEL"), "sort"=>"channel", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("VOTE_ACTIVE"), "sort"=>"active", "default"=>true),
	array("id"=>"C_SORT", "content"=>GetMessage("VOTE_C_SORT"), "sort"=>"c_sort", "default"=>true),
	array("id"=>"TITLE", "content"=>GetMessage("VOTE_TITLE"), "sort"=>"title", "default"=>true),
	array("id"=>"COUNTER", "content"=>GetMessage("VOTE_COUNTER"), "sort"=>"counter", "default"=>true),
));

while($res = $rsData->getNext())
{
	$row =& $lAdmin->AddRow($res["ID"], $res);
	$arActions = array();
	if (in_array($res["CHANNEL_ID"], $channelsToAdmin))
	{
		$row->AddViewField("ID", "<a href='vote_edit.php?lang=".LANGUAGE_ID."&ID={$res["ID"]}' title='".GetMessage("VOTE_EDIT_TITLE")."'>{$res["ID"]}</a>");
		$row->AddSelectField("CHANNEL_ID", $arChannelsTitle);
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("C_SORT");
		$row->AddInputField("TITLE", array());
		$row->AddViewField("COUNTER",
			"<a href=\"vote_user_votes.php?lang=".LANGUAGE_ID."&find_vote_id={$res["ID"]}&find_valid=Y&set_filter=Y\" title=\"".GetMessage("VOTE_VOTES_TITLE")."\">{$res["COUNTER"]}</a>&nbsp;".
			" [ <a href=\"vote_user_votes.php?lang=".LANGUAGE_ID."&find_vote_id={$res["ID"]}&export=xls&filename=vote{$res["ID"]}.xls\">xls</a> ]"
		);
		$arActions[] = array("DEFAULT"=>"Y", "ICON"=>"edit", "TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"), "ACTION"=>$lAdmin->ActionRedirect("vote_edit.php?ID=".$res["ID"]));
		$arActions[] = array(
			"ICON" => "copy", "TEXT" => GetMessage("VOTE_COPY"),
			"ACTION"=>$lAdmin->ActionRedirect("vote_edit.php?lang=".LANGUAGE_ID."&COPY_ID=".$res["ID"]."&CHANNEL_ID=" . $res["CHANNEL_ID"]));

		$arActions[] = array(
			"ICON" => "reset", "TEXT" => GetMessage("VOTE_RESET_NULL"),
			"ACTION" => "if(confirm('".GetMessage("VOTE_CONFIRM_RESET_VOTE")."')) window.location='vote_list.php?lang=".LANGUAGE_ID."&find_channel_id=".$arFilter["CHANNEL_ID"]."&reset_id={$res["ID"]}&".bitrix_sessid_get()."'");
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON" => "delete", "TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage("VOTE_CONFIRM_DEL_VOTE")."')) window.location='vote_list.php?lang=".LANGUAGE_ID."&find_channel_id=".$arFilter["CHANNEL_ID"]."&action=delete&ID={$res["ID"]}&".bitrix_sessid_get()."'");
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("TEXT" => GetMessage("VOTE_PREVIEW"), "TITLE" => GetMessage("VOTE_PREVIEW_TITLE"),
			"ACTION" => $lAdmin->ActionRedirect("vote_preview.php?lang=".LANGUAGE_ID."&VOTE_ID={$res["ID"]}"));
		$arActions[] = array("TEXT" => GetMessage("VOTE_RESULTS"), "TITLE" => GetMessage("VOTE_RESULTS_TITLE"),
			"ACTION" => $lAdmin->ActionRedirect("vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID={$res["ID"]}"));
		$arActions[] = array("TEXT" => GetMessage("VOTE_QUESTIONS"), "TITLE" => GetMessage("VOTE_QUESTIONS_TITLE"),
			"ACTION" => $lAdmin->ActionRedirect("vote_question_list.php?lang=".LANGUAGE_ID."&VOTE_ID={$res["ID"]}"));

	}
	else
	{
		$row->AddViewField("ID", "<a href='vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID={$res["ID"]}' title='".GetMessage("VOTE_RESULTS")."'>{$res["ID"]}</a>");
		$row->bReadOnly = true;
		$arActions[] = array("DEFAULT"=>"Y", "ICON"=>"read", "TEXT"=>GetMessage("VOTE_RESULTS"), "ACTION"=>$lAdmin->ActionRedirect("vote_results.php?lang=".LANGUAGE_ID."&VOTE_ID={$res["ID"]}"));
	}
	$row->AddActions($arActions);

	$lamp = $res["LAMP"];
	if ($res["LAMP"] == "yellow")
		$res["LAMP"] = ($res["ID"] == CVote::GetActiveVoteId($res["CHANNEL_ID"]) ? "green" : "red");
	if ($res["LAMP"] == "green")
		$lamp = "<div class=\"lamp-green\" title=\"".GetMessage("VOTE_LAMP_ACTIVE")."\"></div>";
	elseif ($res["LAMP"] == "red")
		$lamp = "<div class=\"lamp-red\" title=\"".($res["ACTIVE"] != 'Y' ? GetMessage("VOTE_NOT_ACTIVE") : GetMessage("VOTE_ACTIVE_RED_LAMP"))."\"></div>";

	$row->AddViewField("LAMP", $lamp);
}

/************** Footer *********************************************/
$lAdmin->AddFooter(array(
	array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
	array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0")));

if ($VOTE_RIGHT >= "W" || !empty($channelsToAdmin)):
	$lAdmin->AddGroupActionTable(Array(
		"delete" => GetMessage("VOTE_DELETE"),
		"activate" => GetMessage("VOTE_ACTIVATE"),
		"deactivate" => GetMessage("VOTE_DEACTIVATE")));
	if (array_key_exists("CHANNEL_ID", $arFilter)&& in_array($arFilter["CHANNEL_ID"], $channelsToAdmin))
		$lAdmin->AddAdminContextMenu(array(array(
			"TEXT" => GetMessage("VOTE_CREATE"),
			"TITLE" => GetMessage("VOTE_ADD_LIST"),
			"LINK" => "vote_edit.php?lang=" . LANG . "&CHANNEL_ID=" . $arFilter["CHANNEL_ID"],
			"ICON" => "btn_new")));
endif;
$lAdmin->CheckListMode();
/********************************************************************
				/Data
********************************************************************/

$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<a name="tb"></a>
<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="ajax_post" value="Y" />
<?

$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
		GetMessage("VOTE_FL_ID"),
		GetMessage("VOTE_FL_LAMP"),
		GetMessage("VOTE_FL_DATE_START"),
		GetMessage("VOTE_FL_DATE_END"),
		GetMessage("VOTE_FL_CHANNEL"),
		GetMessage("VOTE_FL_ACTIVE"),
		GetMessage("VOTE_FL_COUNTER"))
);

$oFilter->Begin();

?>

<tr>
	<td nowrap><b><?=GetMessage("VOTE_F_TITLE")?></b></td>
	<td nowrap><input type="text" name="find_title" value="<?echo htmlspecialcharsbx($arFilter["TITLE"])?>" size="47"><?=InputType("checkbox", "find_title_exact_match", "Y", $arFilter["TITLE_EXACT_MATCH"], false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td>ID:</td>
	<td><input type="text" name="find_id" size="47" value="<?=htmlspecialcharsbx($arFilter["ID"]?:$arFilter["ID"])?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $request->getQuery("find_id_exact_match"), false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?=GetMessage("VOTE_F_LAMP")?></td>
	<td><?
		$arr = array("reference"=>array(GetMessage("VOTE_RED"), GetMessage("VOTE_GREEN")), "reference_id"=>array("red","green"));
		echo SelectBoxFromArray("find_lamp", $arr, htmlspecialcharsbx($arFilter["LAMP"]), GetMessage("VOTE_ALL"));
		?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_DATE_START").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_date_start_1", $arFilter[">=DATE_START"], "find_date_start_2", $arFilter["<=DATE_START"], "find_form","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_DATE_END").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_date_end_1", $arFilter[">=DATE_END"], "find_date_end_2", $arFilter["<=DATE_END"], "find_form","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_CHANNEL")?></td>
	<td nowrap><input type="text" name="find_channel" value="<?echo htmlspecialcharsbx($arFilter["CHANNEL"])?>" size="47"><?=InputType("checkbox", "find_channel_exact_match", "Y", $arFilter["CHANNEL_EXACT_MATCH"], false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
	<tr>
		<td nowrap><?echo GetMessage("VOTE_F_CHANNEL_ID")?></td>
		<td nowrap><?=SelectBoxFromArray("find_channel_id", array("reference" => array_values($arChannelsTitle), "reference_id" => array_keys($arChannelsTitle)), $arFilter["CHANNEL_ID"], GetMessage("VOTE_ALL"))?></td>
	</tr>

<tr>
	<td nowrap><?echo GetMessage("VOTE_F_ACTIVE")?></td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("VOTE_YES"), GetMessage("VOTE_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($arFilter["ACTIVE"]), GetMessage("VOTE_ALL"));
		?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_COUNTER")?></td>
	<td nowrap><input type="text" name="find_counter_1" value="<?=htmlspecialcharsbx($arFilter[">=COUNTER"])?>" size="10"><?echo "&nbsp;".GetMessage("VOTE_TILL")."&nbsp;"?><input type="text" name="find_counter_2" value="<?=htmlspecialcharsbx($arFilter["<=COUNTER"])?>" size="10"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
#############################################################
?>

</form>
<?
$lAdmin->DisplayList();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
