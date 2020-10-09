<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */

/**
 * Bitrix vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if(!$USER->CanDoOperation('edit_all_users'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$sTableID = "tbl_profile_history";
$sorting = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $sorting);

$arFilterFields = array(
	"find_user_id",
	"find_date_insert_1",
	"find_date_insert_2",
	"find_event_type",
	"find_remote_addr",
	"find_user_agent",
	"find_request_uri",
	"find_field",
);

function CheckFilter()
{
	$error = '';
	$request = Main\Context::getCurrent()->getRequest();

	if($request["find_date_insert_1"] <> '' && Main\Type\DateTime::tryParse($request["find_date_insert_1"]) === null)
	{
		$error .= Loc::getMessage("main_profile_history_error_date1")."<br>";
	}
	if($request["find_date_insert_2"] <> '' && Main\Type\DateTime::tryParse($request["find_date_insert_2"]) === null)
	{
		$error .= Loc::getMessage("main_profile_history_error_date2")."<br>";
	}
	if($error <> '')
	{
		global $lAdmin;
		$lAdmin->AddFilterError($error);
		return false;
	}

	return true;
}

$arFilter = array();
$lAdmin->InitFilter($arFilterFields);
InitSorting();

global $find_user_id,
	$find_date_insert_1,
	$find_date_insert_2,
	$find_event_type,
	$find_remote_addr,
	$find_user_agent,
	$find_request_uri,
	$find_field;

if(CheckFilter())
{
	if($find_user_id <> '')
	{
		$arFilter["=USER_ID"] = $find_user_id;
	}
	if($find_date_insert_1 <> '')
	{
		$arFilter[">=DATE_INSERT"] = $find_date_insert_1;
	}
	if($find_date_insert_2 <> '')
	{
		$arFilter["<=DATE_INSERT"] = $find_date_insert_2;
	}
	if($find_event_type <> '')
	{
		$arFilter["=EVENT_TYPE"] = $find_event_type;
	}
	if($find_remote_addr <> '')
	{
		$arFilter["%REMOTE_ADDR"] = $find_remote_addr;
	}
	if($find_user_agent <> '')
	{
		$arFilter["%USER_AGENT"] = $find_user_agent;
	}
	if($find_request_uri <> '')
	{
		$arFilter["%REQUEST_URI"] = $find_request_uri;
	}
	if($find_field <> '')
	{
		$arFilter['=\Bitrix\Main\UserProfileRecordTable:HISTORY.FIELD'] = $find_field;
	}
}

$sortOrder = mb_strtoupper($sorting->getOrder());
if($sortOrder <> "DESC")
{
	$sortOrder = "ASC";
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-history");

$listParams = array(
	'filter' => $arFilter,
	'order' => array("ID" => $sortOrder),
	'count_total' => true,
);

if(!(isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "excel"))
{
	$listParams['offset'] = $nav->getOffset();
	$listParams['limit'] = $nav->getLimit();
}

$historyList = Main\UserProfileHistoryTable::getList($listParams);

$nav->setRecordCount($historyList->getCount());

$lAdmin->setNavigation($nav, Loc::getMessage("main_profile_history_records"));

$lAdmin->AddHeaders(array(
	array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true, "align" => "right"),
	array("id" => "DATE_INSERT", "content" => Loc::getMessage("main_profile_history_date"), "default" => true),
	array("id" => "USER", "content" => Loc::getMessage("main_profile_history_user"), "default" => true),
	array("id" => "EVENT_TYPE", "content" => Loc::getMessage("main_profile_history_event"), "default" => true),
	array("id" => "UPDATED_BY", "content" => Loc::getMessage("main_profile_history_updated_by"), "default" => true),
	array("id" => "FIELDS", "content" => Loc::getMessage("main_profile_history_changes"), "default" => true),
	array("id" => "REMOTE_ADDR", "content" => Loc::getMessage("main_profile_history_ip"), "default" => false),
	array("id" => "USER_AGENT", "content" => Loc::getMessage("main_profile_history_browser"), "default" => false),
	array("id" => "REQUEST_URI", "content" => Loc::getMessage("main_profile_history_url"), "default" => false),
));

$eventTypes = array(
	Main\UserProfileHistoryTable::TYPE_ADD => Loc::getMessage("main_profile_history_adding"),
	Main\UserProfileHistoryTable::TYPE_UPDATE => Loc::getMessage("main_profile_history_updating"),
	Main\UserProfileHistoryTable::TYPE_DELETE => Loc::getMessage("main_profile_history_deleting"),
);

$userCache = array();

$converter = Main\Text\Converter::getHtmlConverter();
while($history = $historyList->fetch($converter))
{
	if(!isset($userCache[$history["USER_ID"]]))
	{
		$userCache[$history["USER_ID"]] = $history["USER_ID"];
		$user = CUser::GetByID($history["USER_ID"])->Fetch();
		if($user)
		{
			$userCache[$history["USER_ID"]] = '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$history["USER_ID"].'">'.$history["USER_ID"].'</a>] '.CUser::FormatName("#NAME# #LAST_NAME#", $user);
		}
	}
	if($history["UPDATED_BY_ID"] > 0 && !isset($userCache[$history["UPDATED_BY_ID"]]))
	{
		$userCache[$history["UPDATED_BY_ID"]] = $history["UPDATED_BY_ID"];
		$user = CUser::GetByID($history["UPDATED_BY_ID"])->Fetch();
		if($user)
		{
			$userCache[$history["UPDATED_BY_ID"]] = '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&amp;ID='.$history["UPDATED_BY_ID"].'">'.$history["UPDATED_BY_ID"].'</a>] '.CUser::FormatName("#NAME# #LAST_NAME#", $user);
		}
	}

	$fields = '';
	if($history["EVENT_TYPE"] == Main\UserProfileHistoryTable::TYPE_UPDATE)
	{
		$records = Main\UserProfileRecordTable::getList(array("filter" => array("=HISTORY_ID" => $history["ID"])));
		while($record = $records->fetch())
		{
			$fields .= HtmlFilter::encode($record["FIELD"]).': <span style="color:red">'.
				HtmlFilter::encode(var_export($record["DATA"]["before"], true)).'</span> => <span style="color:green">'.
				HtmlFilter::encode(var_export($record["DATA"]["after"], true)).'</span><br>';
		}
	}

	$row = &$lAdmin->AddRow($history["ID"], $history);

	$row->AddViewField("ID", $history["ID"]);
	$row->AddViewField("DATE_INSERT", $history["DATE_INSERT"]);
	$row->AddViewField("USER", $userCache[$history["USER_ID"]]);
	$row->AddViewField("EVENT_TYPE", $eventTypes[$history["EVENT_TYPE"]]);
	$row->AddViewField("UPDATED_BY", $userCache[$history["UPDATED_BY_ID"]]);
	$row->AddViewField("FIELDS", $fields);
	$row->AddViewField("REMOTE_ADDR", $history["REMOTE_ADDR"]);
	$row->AddViewField("USER_AGENT", $history["USER_AGENT"]);
	$row->AddViewField("REQUEST_URI", $history["REQUEST_URI"]);
}

$lAdmin->AddAdminContextMenu();

$APPLICATION->SetTitle(Loc::getMessage("main_profile_history_title"));

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$arFilterNames = array(
	"find_user_id" => Loc::getMessage("main_profile_history_filter_id"),
	"find_event_type" => Loc::getMessage("main_profile_history_filter_event"),
	"find_date_insert" => Loc::getMessage("main_profile_history_filter_date"),
	"find_remote_addr" => Loc::getMessage("main_profile_history_filter_ip"),
	"find_user_agent" => Loc::getMessage("main_profile_history_filter_browser"),
	"find_request_uri" => Loc::getMessage("main_profile_history_filter_url"),
	"find_field" => Loc::getMessage("main_profile_history_filter_field"),
);

$oFilter = new CAdminFilter($sTableID."_filter", $arFilterNames);
$oFilter->Begin();
?>
<tr>
	<td><?echo $arFilterNames["find_user_id"]?>:</td>
	<td><input type="text" name="find_user_id" size="47" value="<?echo htmlspecialcharsbx($find_user_id)?>"></td>
</tr>
<tr>
	<td><?echo $arFilterNames["find_event_type"]?>:</td>
	<td><select name="find_event_type">
			<option value=""><?echo Loc::getMessage("main_profile_history_filter_all")?></option>
			<?foreach($eventTypes as $value => $name):?>
			<option value="<?=$value?>"<?if($find_event_type == $value) echo " selected"?>><?=$name?></option>
			<?endforeach;?>
		</select></td>
</tr>
<tr>
	<td><?echo $arFilterNames["find_date_insert"]?>:</td>
	<td><?echo CAdminCalendar::CalendarPeriod("find_date_insert_1", "find_date_insert_2", $find_date_insert_1, $find_date_insert_2, false, 15, true)?></td>
</tr>
<tr>
	<td><?echo $arFilterNames["find_remote_addr"]?>:</td>
	<td><input type="text" name="find_remote_addr" size="47" value="<?echo htmlspecialcharsbx($find_remote_addr)?>"></td>
</tr>
<tr>
	<td><?echo $arFilterNames["find_user_agent"]?>:</td>
	<td><input type="text" name="find_user_agent" size="47" value="<?echo htmlspecialcharsbx($find_user_agent)?>"></td>
</tr>
<tr>
	<td><?echo $arFilterNames["find_request_uri"]?>:</td>
	<td><input type="text" name="find_request_uri" size="47" value="<?echo htmlspecialcharsbx($find_request_uri)?>"></td>
</tr>
<tr>
	<td><?echo $arFilterNames["find_field"]?>:</td>
	<td><input type="text" name="find_field" size="47" value="<?echo htmlspecialcharsbx($find_field)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?

$lAdmin->DisplayList();

echo BeginNote();
echo Loc::getMessage("main_profile_history_note");
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
