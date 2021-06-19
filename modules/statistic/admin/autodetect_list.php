<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$sTableID = "tbl_autodetect_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if ($STAT_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$statDB = CDatabase::GetModuleConnection('statistic');
IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";

function checkIfBrowser($mask)
{
	$statDB = CDatabase::GetModuleConnection('statistic');
	$strSql = "
		SELECT
			count(ID) CNT
		FROM
			b_stat_browser
		WHERE
			upper('".$statDB->ForSql($mask, 255)."') like upper(USER_AGENT)
		and USER_AGENT is not null
		and ".$statDB->Length("USER_AGENT").">0
		";
	$z = $statDB->Query($strSql);
	$zr = $z->Fetch();
	return $zr["CNT"] > 0;
}

function checkIfSearcher($mask)
{
	$statDB = CDatabase::GetModuleConnection('statistic');
	$strSql = "
		SELECT
			count(ID) CNT
		FROM
			b_stat_searcher
		WHERE
			upper('".$statDB->ForSql($mask, 255)."') like ".$statDB->Concat("'%'","USER_AGENT","'%'")."
		and USER_AGENT is not null
		and ".$statDB->Length("USER_AGENT").">0
	";
	$z = $statDB->Query($strSql);
	$zr = $z->Fetch();
	return $zr["CNT"] > 0;
}

function addAsBrowser($mask)
{
	$statDB = CDatabase::GetModuleConnection('statistic');
	if(!checkIfBrowser($mask) && !checkIfSearcher($mask))
	{
		$arFields = array(
			"USER_AGENT" => "'%".$statDB->ForSql($mask, 255)."%'",
		);
		$statDB->Insert("b_stat_browser", $arFields);
		return 1;
	}
	else
	{
		return 0;
	}
}

function addAsSearcher($mask)
{
	$statDB = CDatabase::GetModuleConnection('statistic');
	if(!checkIfBrowser($mask) && !checkIfSearcher($mask))
	{
		$arFields = array(
			"ACTIVE" => "'Y'",
			"SAVE_STATISTIC" => "'Y'",
			"NAME" => "'".$statDB->ForSql($mask, 255)."'",
			"USER_AGENT" => "'".$statDB->ForSql($mask, 255)."'",
		);
		$statDB->Insert("b_stat_searcher", $arFields);
		return 1;
	}
	else
	{
		return 0;
	}
}

$added_browsers = 0;
$added_searchers = 0;

if($STAT_RIGHT >= "W" && check_bitrix_sessid())
{
	if($lAdmin->EditAction())
	{
		foreach($FIELDS as $ID => $arFields)
		{
			if ($arFields["type"] === "b")
				$added_browsers += addAsBrowser($arFields["FAKE_MASK"]);
			elseif ($arFields["type"] === "s")
				$added_searchers += addAsSearcher($arFields["FAKE_MASK"]);
		}
	}
	elseif ($arID = $lAdmin->GroupAction())
	{
		foreach($arID as $ID)
		{
			if($ID == '')
				continue;
			switch($_REQUEST['action'])
			{
			case "add_as_searcher":
				$added_searchers += addAsSearcher($_REQUEST["mask"]);
				break;
			case "add_as_browser":
				$added_browsers += addAsBrowser($_REQUEST["mask"]);
				break;
			}
		}
	}

	$lAdmin->BeginPrologContent();
	CAdminMessage::ShowMessage(array(
		"DETAILS" => GetMessage("STAT_ADDED_SEARCHERS")." <b>".$added_searchers."</b><br>".GetMessage("STAT_ADDED_BROWSERS")." <b>".$added_browsers."</b>",
		"HTML" => true,
		"TYPE" => "OK",
	));
	$lAdmin->EndPrologContent();
}


$arrExactMatch = array(
	"USER_AGENT_EXACT_MATCH" => "find_user_agent_exact_match",
);
$FilterArr = array(
	"find_last",
	"find_user_agent",
	"find_counter1",
	"find_counter2",
);
$arFilterFields = array_merge($FilterArr, array_values($arrExactMatch));

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"LAST" => $find_last,
	"USER_AGENT" => $find_user_agent,
	"COUNTER1" => $find_counter1,
	"COUNTER2" => $find_counter2,
);
$arFilter = array_merge($arFilter, array_convert_name_2_value($arrExactMatch));

$rsData = CAutoDetect::GetList('', '', $arFilter);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_USER_AGENT_PAGES")));

$lAdmin->AddHeaders(array(
	array(
		"id" => "USER_AGENT",
		"content" => GetMessage("STAT_USER_AGENT"),
		"sort" => "",
		"default" => true,
	),
	array(
		"id" => "COUNTER",
		"content" => GetMessage("STAT_SESSIONS"),
		"sort" => "",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "FAKE_MASK",
		"content" => GetMessage("STAT_MASK"),
		"sort" => "",
		"default" => true,
	),
	array(
		"id" => "FAKE_SRCH_S",
		"content" => GetMessage("STAT_SEARCHER"),
		"align" => "center",
		"default" => true,
	),
	array(
		"id" => "FAKE_SRCH_B",
		"content" => GetMessage("STAT_BROWSER"),
		"align" => "center",
		"default" => true,
	),
));

$i = 0;
while($arRes = $rsData->NavNext(true, "f_"))
{
	$arRes["ID"] = $f_ID = $i++;
	$arRes["FAKE_MASK"] = preg_replace("/[0-9\\\'\"]/", "_", $arRes["USER_AGENT"]);

	$row = $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("COUNTER","<a title=\"".GetMessage("STAT_SESS_LIST")."\" href=\"/bitrix/admin/session_list.php?lang=".LANGUAGE_ID."&find_user_agent=".urlencode("\"".str_replace(array("\\", "\'", "\""), "_", $f_USER_AGENT)."\"")."&set_filter=Y\">$f_COUNTER</a>");
	$row->AddInputField("FAKE_MASK", array("size"=>35));
	$row->AddEditField("FAKE_SRCH_S", "<input type=\"radio\" name=\"".htmlspecialcharsbx("FIELDS[".$f_ID."][type]")."\" value=\"s\" checked> ");
	$row->AddEditField("FAKE_SRCH_B", "<input type=\"radio\" name=\"".htmlspecialcharsbx("FIELDS[".$f_ID."][type]")."\" value=\"b\"> ");

	$arActions = array(
		array(
			"TEXT" => GetMessage("STAT_ADD_AS_SEARCHER"),
			"ACTION" => $lAdmin->ActionDoGroup($f_ID, "add_as_searcher", "&mask=".urlencode($arRes["FAKE_MASK"]))
		),
		array(
			"TEXT" => GetMessage("STAT_ADD_AS_BROWSER"),
			"ACTION" => $lAdmin->ActionDoGroup($f_ID, "add_as_browser", "&mask=".urlencode($arRes["FAKE_MASK"]))
		),
	);
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsData->SelectedRowsCount(),
	),
	array(
		"counter" => true,
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => "0",
	),
));
$lAdmin->AddGroupActionTable(
	array(
	),
	array(
		"disable_action_target" => true,
	)
);

$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<a name="tb"></a>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("STAT_FL_DAY"),
		GetMessage("STAT_FL_SESS"),
	)
);

$oFilter->Begin();
?>
<tr>
	<td nowrap><b><?echo GetMessage("STAT_F_USER_AGENT")?></b></td>
	<td><input type="text" name="find_user_agent" size="28" value="<?echo htmlspecialcharsbx($find_user_agent)?>"><?=ShowExactMatchCheckbox("find_user_agent")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_LAST_DAY")?></td>
	<td>
		<?
		$arr = array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_last", $arr, htmlspecialcharsbx($find_last), GetMessage("MAIN_ALL"));
		?></td>
</tr>
<tr>
	<td>
		<?echo GetMessage("STAT_F_COUNTER")?></td>
	<td>
		<input type="text" name="find_counter1" size="10" value="<?echo htmlspecialcharsbx($find_counter1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_counter2" size="10" value="<?echo htmlspecialcharsbx($find_counter2)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()));
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
