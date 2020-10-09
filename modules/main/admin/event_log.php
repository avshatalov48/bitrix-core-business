<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if(!$USER->CanDoOperation('view_event_log'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$bStatistic = CModule::IncludeModule('statistic');

$arAuditTypes = CEventLog::GetEventTypes();

$sTableID = "tbl_event_log";
$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"find",
	"find_type",
	"find_id",
	"find_timestamp_x_1",
	"find_timestamp_x_2",
	"find_severity",
	"find_audit_type_id",
	"find_audit_type",
	"find_module_id",
	"find_item_id",
	"find_site_id",
	"find_user_id",
	"find_guest_id",
	"find_remote_addr",
	"find_user_agent",
	"find_request_uri",
);
function CheckFilter()
{
	$str = "";
	if($_REQUEST["find_timestamp_x_1"] <> '')
	{
		if(!CheckDateTime($_REQUEST["find_timestamp_x_1"], CSite::GetDateFormat("FULL")))
			$str.= GetMessage("MAIN_EVENTLOG_WRONG_TIMESTAMP_X_FROM")."<br>";
	}
	if($_REQUEST["find_timestamp_x_2"] <> '')
	{
		if(!CheckDateTime($_REQUEST["find_timestamp_x_2"], CSite::GetDateFormat("FULL")))
			$str.= GetMessage("MAIN_EVENTLOG_WRONG_TIMESTAMP_X_TO")."<br>";
	}

	if($str <> '')
	{
		global $lAdmin;
		$lAdmin->AddFilterError($str);
		return false;
	}

	return true;
}

$arFilter = array();
$lAdmin->InitFilter($arFilterFields);
InitSorting();

if(CheckFilter())
{
	if(is_array($find_severity) && $find_severity[0] == "NOT_REF")
		$find_severity = "";

	if(is_array($find_audit_type) && $find_audit_type[0] == "NOT_REF")
	{
		$audit_type_id_op = "=";
		$audit_type_id_filter = false;
	}
	elseif($find_type == "audit_type_id" && $find != '')
	{
		$audit_type_id_op = "";
		$audit_type_id_filter = $find;
	}
	elseif(is_array($find_audit_type))
	{
		$audit_type_id_op = "=";
		$audit_type_id_filter = $find_audit_type;
	}
	else
	{
		$audit_type_id_op = "";
		$audit_type_id_filter = $find_audit_type;
	}

	if(!is_array($audit_type_id_filter) && mb_strlen($find_audit_type_id))
	{
		$audit_type_id_op = "";
		$audit_type_id_filter = "(".$audit_type_id_filter.")|(".$find_audit_type_id.")";
	}

	$arFilter = array(
		"ID" => $find_id,
		"TIMESTAMP_X_1" => $find_timestamp_x_1,
		"TIMESTAMP_X_2" => $find_timestamp_x_2,
		"SEVERITY" => (is_array($find_severity) && count($find_severity) > 0? implode("|", $find_severity): ""),
		$audit_type_id_op."AUDIT_TYPE_ID" => $audit_type_id_filter,
		"MODULE_ID" => $find_module_id,
		"ITEM_ID" => $find_item_id,
		"SITE_ID" => $find_site_id,
		"USER_ID" => ($find != '' && $find_type == "user_id" ? $find : $find_user_id),
		"GUEST_ID" => $find_guest_id,
		"REMOTE_ADDR" => ($find != '' && $find_type == "remote_addr" ? $find : $find_remote_addr),
		"REQUEST_URI" => $find_request_uri,
		"USER_AGENT" => ($find != '' && $find_type == "user_agent" ? $find : $find_user_agent),
	);
}

if(isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "excel")
	$arNavParams = false;
else
	$arNavParams = array("nPageSize"=>CAdminResult::GetNavSize($sTableID));

/** @global string $by  */
/** @global string $order  */
$rsData = CEventLog::GetList(array($by => $order), $arFilter, $arNavParams);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("MAIN_EVENTLOG_LIST_PAGE")));

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => GetMessage("MAIN_EVENTLOG_ID"),
		"sort" => "ID",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("MAIN_EVENTLOG_TIMESTAMP_X"),
		"sort" => "TIMESTAMP_X",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "SEVERITY",
		"content" => GetMessage("MAIN_EVENTLOG_SEVERITY"),
	),
	array(
		"id" => "AUDIT_TYPE_ID",
		"content" => GetMessage("MAIN_EVENTLOG_AUDIT_TYPE_ID"),
		"default" => true,
	),
	array(
		"id" => "MODULE_ID",
		"content" => GetMessage("MAIN_EVENTLOG_MODULE_ID"),
	),
	array(
		"id" => "ITEM_ID",
		"content" => GetMessage("MAIN_EVENTLOG_ITEM_ID"),
		"default" => true,
	),
	array(
		"id" => "REMOTE_ADDR",
		"content" => GetMessage("MAIN_EVENTLOG_REMOTE_ADDR"),
		"default" => true,
	),
	array(
		"id" => "USER_AGENT",
		"content" => GetMessage("MAIN_EVENTLOG_USER_AGENT"),
	),
	array(
		"id" => "REQUEST_URI",
		"content" => GetMessage("MAIN_EVENTLOG_REQUEST_URI"),
		"default" => true,
	),
	array(
		"id" => "SITE_ID",
		"content" => GetMessage("MAIN_EVENTLOG_SITE_ID"),
	),
	array(
		"id" => "USER_ID",
		"content" => GetMessage("MAIN_EVENTLOG_USER_ID"),
		"default" => true,
	),
	array(
		"id" => "DESCRIPTION",
		"content" => GetMessage("MAIN_EVENTLOG_DESCRIPTION"),
		"default" => true,
	),
);
if($bStatistic)
	$arHeaders[] = array(
		"id" => "GUEST_ID",
		"content" => GetMessage("MAIN_EVENTLOG_GUEST_ID"),
	);

$lAdmin->AddHeaders($arHeaders);

$arUsersCache = array();
$arGroupsCache = array();
$arForumCache = array("FORUM" => array(), "TOPIC" => array(), "MESSAGE" => array());
$a_ID = $a_AUDIT_TYPE_ID = $a_GUEST_ID = $a_USER_ID = $a_ITEM_ID = $a_REQUEST_URI = $a_DESCRIPTION = $a_REMOTE_ADDR = '';
while($db_res = $rsData->NavNext(true, "a_"))
{
	$row =& $lAdmin->AddRow($a_ID, $db_res);
	$row->AddViewField("AUDIT_TYPE_ID", array_key_exists($a_AUDIT_TYPE_ID, $arAuditTypes)? preg_replace("/^\\[.*?\\]\\s+/", "", $arAuditTypes[$a_AUDIT_TYPE_ID]): $a_AUDIT_TYPE_ID);
	if($bStatistic && mb_strlen($a_GUEST_ID))
	{
		$row->AddViewField("GUEST_ID", '<a href="/bitrix/admin/hit_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_guest_id='.$a_GUEST_ID.'&amp;find_guest_id_exact_match=Y">'.$a_GUEST_ID.'</a>');
	}
	if($a_USER_ID)
	{
		if(!array_key_exists($a_USER_ID, $arUsersCache))
		{
			$rsUser = CUser::GetByID($a_USER_ID);
			if($arUser = $rsUser->GetNext())
			{
				$arUser["FULL_NAME"] = $arUser["NAME"].($arUser["NAME"] == '' || $arUser["LAST_NAME"] == ''?"":" ").$arUser["LAST_NAME"];
			}
			$arUsersCache[$a_USER_ID] = $arUser;
		}
		if($arUsersCache[$a_USER_ID])
			$row->AddViewField("USER_ID", '[<a href="user_edit.php?lang='.LANG.'&ID='.$a_USER_ID.'">'.$a_USER_ID.'</a>] '.$arUsersCache[$a_USER_ID]["FULL_NAME"]);
	}
	if($a_ITEM_ID)
	{
		switch($a_AUDIT_TYPE_ID)
		{
		case "USER_AUTHORIZE":
		case "USER_LOGOUT":
		case "USER_REGISTER":
		case "USER_INFO":
		case "USER_PASSWORD_CHANGED":
		case "USER_DELETE":
		case "USER_GROUP_CHANGED":
		case "USER_EDIT":
		case "USER_BLOCKED":
		case "SECURITY_OTP":
			if(!array_key_exists($a_ITEM_ID, $arUsersCache))
			{
				$rsUser = CUser::GetByID($a_ITEM_ID);
				if($arUser = $rsUser->GetNext())
				{
					$arUser["FULL_NAME"] = $arUser["NAME"].($arUser["NAME"] == '' || $arUser["LAST_NAME"] == ''?"":" ").$arUser["LAST_NAME"];
				}
				$arUsersCache[$a_ITEM_ID] = $arUser;
			}
			if($arUsersCache[$a_ITEM_ID])
				$row->AddViewField("ITEM_ID", '[<a href="user_edit.php?lang='.LANG.'&amp;ID='.$a_ITEM_ID.'">'.$a_ITEM_ID.'</a>] '.$arUsersCache[$a_ITEM_ID]["FULL_NAME"]);
			break;
		case "GROUP_POLICY_CHANGED":
		case "MODULE_RIGHTS_CHANGED":
			if(!array_key_exists($a_ITEM_ID, $arGroupsCache))
			{
				$rsGroup = CGroup::GetByID($a_ITEM_ID);
				if($arGroup = $rsGroup->GetNext())
					$arGroupsCache[$a_ITEM_ID] = $arGroup["NAME"];
				else
					$arGroupsCache[$a_ITEM_ID] = "";
			}
			$row->AddViewField("ITEM_ID", '[<a href="group_edit.php?lang='.LANG.'&amp;ID='.$a_ITEM_ID.'">'.$a_ITEM_ID.'</a>] '.$arGroupsCache[$a_ITEM_ID]);
			break;
		case "TASK_CHANGED":
			$rsTask = CTask::GetByID($a_ITEM_ID);
			if($arTask = $rsTask->GetNext())
				$row->AddViewField("ITEM_ID", '[<a href="task_edit.php?lang='.LANG.'&amp;ID='.$a_ITEM_ID.'">'.$a_ITEM_ID.'</a>] '.$arTask["NAME"]);
			break;
		case "FORUM_MESSAGE_APPROVE":
		case "FORUM_MESSAGE_UNAPPROVE":
		case "FORUM_MESSAGE_MOVE":
		case "FORUM_MESSAGE_EDIT":
			if (intval($a_ITEM_ID) <= 0):
				break;
			elseif (!array_key_exists($a_ITEM_ID, $arForumCache["MESSAGE"])):
				CModule::IncludeModule("forum");
				$res = CForumMessage::GetByID($a_ITEM_ID);
				$res["MESSAGE_ID"] = $res["ID"];
				$arForumCache["MESSAGE"][$a_ITEM_ID] = $res;
			else:
				$res = $arForumCache["MESSAGE"][$a_ITEM_ID];
			endif;
			if (!array_key_exists($res["FORUM_ID"], $arForumCache["FORUM"])):
				$arForumCache["FORUM"][$res["FORUM_ID"]] = CForumNew::GetByID($res["FORUM_ID"]);
				if ($arForumCache["FORUM"][$res["FORUM_ID"]]):
					$arSitesPath = CForumNew::GetSites($res["FORUM_ID"]);
					$arForumCache["FORUM"][$res["FORUM_ID"]]["PATH"] = array_shift($arSitesPath);
				endif;
			endif;
			if ($arForumCache["FORUM"][$res["FORUM_ID"]]["PATH"]):
				$sPath = CForumNew::PreparePath2Message($arForumCache["FORUM"][$res["FORUM_ID"]]["PATH"], $res);
				$row->AddViewField("ITEM_ID", '[<a href="'.$sPath.'">'.$a_ITEM_ID.'</a>] '.GetMessage("MAIN_EVENTLOG_FORUM_MESSAGE"));
			else:
				$row->AddViewField("ITEM_ID", '['.$a_ITEM_ID.'] '.GetMessage("MAIN_EVENTLOG_FORUM_MESSAGE"));
			endif;
			break;
		case "FORUM_TOPIC_APPROVE":
		case "FORUM_TOPIC_UNAPPROVE":
		case "FORUM_TOPIC_STICK":
		case "FORUM_TOPIC_UNSTICK":
		case "FORUM_TOPIC_OPEN":
		case "FORUM_TOPIC_CLOSE":
		case "FORUM_TOPIC_MOVE":
		case "FORUM_TOPIC_EDIT":
			if (intval($a_ITEM_ID) <= 0):
				break;
			elseif (!array_key_exists($a_ITEM_ID, $arForumCache["TOPIC"])):
				CModule::IncludeModule("forum");
				$res = CForumTopic::GetByID($a_ITEM_ID);
				$res["MESSAGE_ID"] = $res["LAST_MESSAGE_ID"];
				$res["TOPIC_ID"] = $res["ID"];
				$arForumCache["TOPIC"][$a_ITEM_ID] = $res;
			else:
				$res = $arForumCache["TOPIC"][$a_ITEM_ID];
			endif;
			if (!array_key_exists($res["FORUM_ID"], $arForumCache["FORUM"])):
				$arForumCache["FORUM"][$res["FORUM_ID"]] = CForumNew::GetByID($res["FORUM_ID"]);
				if ($arForumCache["FORUM"][$res["FORUM_ID"]]):
					$arSitesPath = CForumNew::GetSites($res["FORUM_ID"]);
					$arForumCache["FORUM"][$res["FORUM_ID"]]["PATH"] = array_shift($arSitesPath);
				endif;
			endif;
			if ($arForumCache["FORUM"][$res["FORUM_ID"]]["PATH"]):
				$sPath = CForumNew::PreparePath2Message($arForumCache["FORUM"][$res["FORUM_ID"]]["PATH"], $res);
				$row->AddViewField("ITEM_ID", '[<a href="'.$sPath.'">'.$a_ITEM_ID.'</a>] '.GetMessage("MAIN_EVENTLOG_FORUM_TOPIC"));
			else:
				$row->AddViewField("ITEM_ID", '['.$a_ITEM_ID.'] '.GetMessage("MAIN_EVENTLOG_FORUM_TOPIC"));
			endif;
			break;
		case "FORUM_MESSAGE_DELETE":
			$row->AddViewField("ITEM_ID", '['.$a_ITEM_ID.'] '.GetMessage("MAIN_EVENTLOG_FORUM_MESSAGE"));
			break;
		case "FORUM_TOPIC_DELETE":
			$row->AddViewField("ITEM_ID", '['.$a_ITEM_ID.'] '.GetMessage("MAIN_EVENTLOG_FORUM_TOPIC"));
			break;
		case "IBLOCK_SECTION_ADD":
		case "IBLOCK_SECTION_EDIT":
		case "IBLOCK_SECTION_DELETE":
		case "IBLOCK_ELEMENT_ADD":
		case "IBLOCK_ELEMENT_EDIT":
		case "IBLOCK_ELEMENT_DELETE":
		case "IBLOCK_ADD":
		case "IBLOCK_EDIT":
		case "IBLOCK_DELETE":
			$elementLink = CIBlock::GetAdminElementListLink($a_ITEM_ID, array('filter_section'=>-1));
			parse_str($elementLink);
			if (empty($type))
			{
				$a_ITEM_ID = GetMessage("MAIN_EVENTLOG_IBLOCK_DELETE");
			}
			else
			{
				if(CModule::IncludeModule('iblock'))
					$a_ITEM_ID = '<a href="'.htmlspecialcharsbx($elementLink).'">'.$a_ITEM_ID.'</a>';
			}

			$row->AddViewField("ITEM_ID", '['.$a_ITEM_ID.'] '.GetMessage("MAIN_EVENTLOG_IBLOCK"));
			break;
		}
	}
	if($a_REQUEST_URI <> '')
	{
		$row->AddViewField("REQUEST_URI", htmlspecialcharsbx($a_REQUEST_URI));
	}
	if($a_DESCRIPTION <> '')
	{
		if(strncmp("==", $a_DESCRIPTION, 2) === 0)
		{
			$DESCRIPTION = htmlspecialcharsbx(base64_decode(mb_substr($a_DESCRIPTION, 2)));
		}
		else
		{
			$DESCRIPTION = $a_DESCRIPTION;
		}
		//htmlspecialcharsback for <br> <BR> <br/>
		$DESCRIPTION = preg_replace("#(&lt;)(\\s*br\\s*/{0,1})(&gt;)#is", "<\\2>", $DESCRIPTION);
		$row->AddViewField("DESCRIPTION", $DESCRIPTION);
	}
	if($bStatistic && $a_REMOTE_ADDR)
	{
		$arr = explode(".", $a_REMOTE_ADDR);
		if(count($arr)==4)
		{
			$row->AddViewField("REMOTE_ADDR", $a_REMOTE_ADDR.'<br><a href="stoplist_edit.php?lang='.LANGUAGE_ID.'&amp;net1='.intval($arr[0]).'&amp;net2='.intval($arr[1]).'&amp;net3='.intval($arr[2]).'&amp;net4='.intval($arr[3]).'">['.GetMessage("MAIN_EVENTLOG_STOP_LIST").']<a>');
		}
	}
}

$aContext = array(
	array(
		"TEXT"	=> GetMessage("eventlog_notifications"),
		"LINK"	=> "log_notifications.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("eventlog_notifications_title"),
	),
);
$lAdmin->AddAdminContextMenu($aContext);

$APPLICATION->SetTitle(GetMessage("MAIN_EVENTLOG_PAGE_TITLE"));
$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$arFilterNames = array(
	"find_id" => GetMessage("MAIN_EVENTLOG_ID"),
	"find_timestamp_x" => GetMessage("MAIN_EVENTLOG_TIMESTAMP_X"),
	"find_severity" => GetMessage("MAIN_EVENTLOG_SEVERITY"),
	"find_audit_type_id" => GetMessage("MAIN_EVENTLOG_AUDIT_TYPE_ID"),
	"find_module_id" => GetMessage("MAIN_EVENTLOG_MODULE_ID"),
	"find_item_id" => GetMessage("MAIN_EVENTLOG_ITEM_ID"),
	"find_site_id" => GetMessage("MAIN_EVENTLOG_SITE_ID"),
	"find_user_id" => GetMessage("MAIN_EVENTLOG_USER_ID"),
	"find_guest_id" => GetMessage("MAIN_EVENTLOG_GUEST_ID"),
	"find_remote_addr" => GetMessage("MAIN_EVENTLOG_REMOTE_ADDR"),
	"find_user_agent" => GetMessage("MAIN_EVENTLOG_USER_AGENT"),
	"find_request_uri" => GetMessage("MAIN_EVENTLOG_REQUEST_URI"),
);
if(!$bStatistic)
	unset($arFilterNames["find_guest_id"]);

$oFilter = new CAdminFilter($sTableID."_filter", $arFilterNames);
$oFilter->Begin();
?>
<tr>
	<td><b><?echo GetMessage("MAIN_EVENTLOG_SEARCH")?>:</b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>">
		<select name="find_type">
			<option value="audit_type_id"<?if($find_type=="audit_type_id") echo " selected"?>><?echo GetMessage("MAIN_EVENTLOG_AUDIT_TYPE_ID")?></option>
			<option value="user_id"<?if($find_type=="user_id") echo " selected"?>><?echo GetMessage("MAIN_EVENTLOG_USER_ID")?></option>
			<option value="remote_addr"<?if($find_type=="remote_addr") echo " selected"?>><?echo GetMessage("MAIN_EVENTLOG_REMOTE_ADDR")?></option>
			<option value="user_agent"<?if($find_type=="user_agent") echo " selected"?>><?echo GetMessage("MAIN_EVENTLOG_USER_AGENT")?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_ID")?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_TIMESTAMP_X")?>:</td>
	<td><?echo CAdminCalendar::CalendarPeriod("find_timestamp_x_1", "find_timestamp_x_2", $find_timestamp_x_1, $find_timestamp_x_2, false, 15, true)?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_SEVERITY")?>:</td>
	<td><?echo SelectBoxMFromArray("find_severity[]", array(
			"REFERENCE"    => array("SECURITY", "ERROR", "WARNING", "INFO", "DEBUG"),
			"REFERENCE_ID" => array("SECURITY", "ERROR", "WARNING", "INFO", "DEBUG"),
		), $find_severity, GetMessage("MAIN_ALL"))?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_AUDIT_TYPE_ID")?>:</td>
	<td>
		<input type="text" name="find_audit_type_id" size="47" value="<?echo htmlspecialcharsbx($find_audit_type_id)?>">&nbsp;<?=ShowFilterLogicHelp()?><br>
		<?echo SelectBoxMFromArray("find_audit_type[]", array("reference"=>array_values($arAuditTypes),"reference_id"=>array_keys($arAuditTypes)), $find_audit_type, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_MODULE_ID")?>:</td>
	<td><input type="text" name="find_module_id" size="47" value="<?echo htmlspecialcharsbx($find_module_id)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_ITEM_ID")?>:</td>
	<td><input type="text" name="find_item_id" size="47" value="<?echo htmlspecialcharsbx($find_item_id)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$arSiteDropdown = array("reference" => array(), "reference_id" => array());
$v1 = "sort";
$v2 = "asc";
$rs = CSite::GetList($v1, $v2);
while ($ar = $rs->Fetch())
{
	$arSiteDropdown["reference_id"][] = $ar["ID"];
	$arSiteDropdown["reference"][]    = "[".$ar["ID"]."] ".$ar["NAME"];
}
?>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_SITE_ID")?>:</td>
	<td><?echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("MAIN_ALL"), "");?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_USER_ID")?>:</td>
	<td><input type="text" name="find_user_id" size="47" value="<?echo htmlspecialcharsbx($find_user_id)?>"></td>
</tr>
<?if($bStatistic):?>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_GUEST_ID")?>:</td>
	<td><input type="text" name="find_guest_id" size="47" value="<?echo htmlspecialcharsbx($find_guest_id)?>"></td>
</tr>
<?endif?>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_REMOTE_ADDR")?>:</td>
	<td><input type="text" name="find_remote_addr" size="47" value="<?echo htmlspecialcharsbx($find_remote_addr)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_USER_AGENT")?>:</td>
	<td><input type="text" name="find_user_agent" size="47" value="<?echo htmlspecialcharsbx($find_user_agent)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_EVENTLOG_REQUEST_URI")?>:</td>
	<td><input type="text" name="find_request_uri" size="47" value="<?echo htmlspecialcharsbx($find_request_uri)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
