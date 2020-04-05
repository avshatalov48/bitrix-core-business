<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var $APPLICATION CMain */

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$statDB = CDatabase::GetModuleConnection('statistic');
IncludeModuleLangFile(__FILE__);

$arDelay = array(20,30,60,120,300);
$delay = intval($_REQUEST["delay"]);

if ($delay > 0)
	$_SESSION["SESS_DELAY"] = $delay;
if (intval($_SESSION["SESS_DELAY"])>0)
	$delay = intval($_SESSION["SESS_DELAY"]);
if (!in_array($delay, $arDelay))
	$delay = 30;

$arSites = array();
$ref = $ref_id = array();
$v1 = "sort";
$v2 = "asc";
$rs = CSite::GetList($v1, $v2);
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$sTableID = "t_users_online";
/** @global $by string */
/** @global $order string */
$oSort = new CAdminSorting($sTableID,"s_session_time", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_ID"),
		GetMessage("STAT_F_GUEST_ID"),
		GetMessage("STAT_F_AUTH"),
		GetMessage("STAT_F_NEW_GUEST"),
		GetMessage("STAT_F_IP"),
		GetMessage("STAT_COUNTRY"),
		GetMessage("STAT_F_STOP"),
		GetMessage("STAT_F_STOP_LIST_ID"),
		GetMessage("STAT_F_HITS"),
		GetMessage("STAT_F_CAME_ADV"),
		GetMessage("STAT_F_ADV"),
		"referer1 / referer2",
		"referer3",
		GetMessage("STAT_F_ADV_BACK"),
		GetMessage("STAT_FIRST_FROM_PAGE"),
		GetMessage("STAT_F_URL_LAST"),
	)
);

$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_user",
	"find_guest_id",
	"find_guest_id_exact_match",
	"find_registered",
	"find_new_guest",
	"find_ip",
	"find_ip_exact_match",
	"find_country_id",
	"find_country",
	"find_country_exact_match",
	"find_stop",
	"find_stop_list_id",
	"find_stop_list_id_exact_match",
	"find_hits1",
	"find_hits2",
	"find_adv",
	"find_adv_id",
	"find_adv_id_exact_match",
	"find_referer1",
	"find_referer2",
	"find_referer12_exact_match",
	"find_referer3",
	"find_referer3_exact_match",
	"find_adv_back",
	"find_first_from",
	"find_first_from_exact_match",
	"find_last_site_id",
	"find_url_last_404",
	"find_url_last",
	"find_url_last_exact_match",
);

$adminFilter = $lAdmin->InitFilter($arFilterFields);
if (!$adminFilter)
	$adminFilter = array();

$arFilter = array(
	"ID" => $adminFilter["find_id"],
	"USER" => $adminFilter["find_user"],
	"NEW_GUEST" => $adminFilter["find_new_guest"],
	"GUEST_ID" => $adminFilter["find_guest_id"],
	"IP" => $adminFilter["find_ip"],
	"REGISTERED" => $adminFilter["find_registered"],
	"HITS1" => $adminFilter["find_hits1"],
	"HITS2" => $adminFilter["find_hits2"],
	"ADV" => $adminFilter["find_adv"],
	"ADV_ID" => $adminFilter["find_adv_id"],
	"ADV_BACK" => $adminFilter["find_adv_back"],
	"REFERER1" => $adminFilter["find_referer1"],
	"REFERER2" => $adminFilter["find_referer2"],
	"REFERER3" => $adminFilter["find_referer3"],
	"COUNTRY_ID" => $adminFilter["find_country_id"],
	"COUNTRY" => $adminFilter["find_country"],
	"STOP" => $adminFilter["find_stop"],
	"STOP_LIST_ID" => $adminFilter["find_stop_list_id"],
	"FIRST_URL_FROM" => $adminFilter["find_first_from"],
	"LAST_SITE_ID" => $adminFilter["find_last_site_id"],
	"URL_LAST" => $adminFilter["find_url_last"],
	"URL_LAST_404" => $adminFilter["find_url_last_404"],
	"ID_EXACT_MATCH" => $adminFilter["find_id_exact_match"]=="Y"? "Y": "N",
	"USER_EXACT_MATCH" => $adminFilter["find_user_exact_match"]=="Y"? "Y": "N",
	"GUEST_ID_EXACT_MATCH" => $adminFilter["find_guest_id_exact_match"]=="Y"? "Y": "N",
	"IP_EXACT_MATCH" => $adminFilter["find_ip_exact_match"]=="Y"? "Y": "N",
	"ADV_ID_EXACT_MATCH" => $adminFilter["find_adv_id_exact_match"]=="Y"? "Y": "N",
	"REFERER1_EXACT_MATCH" => $adminFilter["find_referer12_exact_match"]=="Y"? "Y": "N",
	"REFERER2_EXACT_MATCH" => $adminFilter["find_referer12_exact_match"]=="Y"? "Y": "N",
	"REFERER3_EXACT_MATCH" => $adminFilter["find_referer3_exact_match"]=="Y"? "Y": "N",
	"USER_AGENT_EXACT_MATCH" => $adminFilter["find_user_agent_exact_match"]=="Y"? "Y": "N",
	"COUNTRY_EXACT_MATCH" => $adminFilter["find_country_exact_match"]=="Y"? "Y": "N",
	"COUNTRY_ID_EXACT_MATCH" => $adminFilter["find_country_exact_match"]=="Y"? "Y": "N",
	"STOP_LIST_ID_EXACT_MATCH" => $adminFilter["find_stop_list_id_exact_match"]=="Y"? "Y": "N",
	"URL_LAST_EXACT_MATCH" => $adminFilter["find_url_last_exact_match"]=="Y"? "Y": "N",
	"FIRST_URL_FROM_EXACT_MATCH" => $adminFilter["find_first_from_exact_match"]=="Y"? "Y": "N",
);

$guest_count = 0;
$session_count = 0;
$rsData = CUserOnline::GetList($guest_count, $session_count, array($by=>$order), $arFilter);

$lAdmin->onLoadScript = "BX.adminPanel.setTitle('".GetMessageJS("STAT_TITLE", array(
	"#SESSIONS#" => $session_count,
	"#GUESTS#" => $guest_count,
))."');";

$APPLICATION->SetTitle(GetMessage("STAT_TITLE", array(
	"#SESSIONS#" => $session_count,
	"#GUESTS#" => $guest_count,
)));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_USERS_PAGES")));

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => GetMessage("STAT_VIEW_SESSION"),
		"default" => false,
		"sort" => "s_id",
	),
	array(
		"id" => "ADV_ID",
		"content" => GetMessage("STAT_VIEW_ADV"),
		"default" => true,
		"sort" => "s_adv_id",
	),
	array(
		"id" => "HITS",
		"content" => GetMessage("STAT_HITS"),
		"default" => true,
		"sort" => "s_hits",
	),
	array(
		"id" => "SESSION_TIME",
		"content" => GetMessage("STAT_SESSION_TIME"),
		"default" => true,
		"sort" => "s_session_time",
	),
	array(
		"id" => "LAST_USER_ID",
		"content" => GetMessage("STAT_USER"),
		"default" => true,
		"sort" => "s_guest_id",
	),
	array(
		"id" => "IP_LAST",
		"content" => GetMessage("STAT_IP"),
		"default" => true,
		"sort" => "s_ip",
	),
	array(
		"id" => "COUNTRY_ID",
		"content" => GetMessage("STAT_COUNTRY"),
		"default" => true,
		"sort" => "s_country_id",
	),
	array(
		"id" => "REGION_NAME",
		"content" => GetMessage("STAT_REGION"),
		"default" => false,
	),
	array(
		"id" => "CITY_ID",
		"content" => GetMessage("STAT_CITY"),
		"default" => true,
	),
	array(
		"id" => "URL_LAST",
		"content" => GetMessage("STAT_LAST_TO_PAGE"),
		"default" => true,
		"sort" => "s_url_last",
	),
	array(
		"id" => "FIRST_URL_FROM",
		"content" => GetMessage("STAT_FIRST_FROM_PAGE"),
		"default" => true,
	),
	array(
		"id" => "URL_FROM",
		"content" => GetMessage("STAT_LAST_FROM_PAGE"),
		"default" => false,
	),
);

$lAdmin->AddHeaders($arHeaders);

$arrUsers = array();
while($arRes = $rsData->Fetch())
{
	$row = $lAdmin->AddRow($arRes["ID"], $arRes);

	$str = '<a target="_blank" title="'.GetMessage("STAT_VIEW_SESSION").'" href="'.htmlspecialcharsbx(CHTTP::urlAddParams("session_list.php", array(
			'lang' => LANGUAGE_ID,
			'find_id' => $arRes['ID'],
			'find_id_exact_match' => 'Y',
			'set_filter' => 'Y',
		), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["ID"]).'</a>';
	$row->AddViewField("ID", $str);

	if ($arRes["ADV_ID"] > 0)
	{
		$str = '[<a title="'.GetMessage("STAT_VIEW_ADV").'" href="'.htmlspecialcharsbx(CHTTP::urlAddParams("adv_list.php", array(
				'lang' => LANGUAGE_ID,
				'find_id' => $arRes["ADV_ID"],
				'find_id_exact_match' => 'Y',
				'set_filter' => 'Y',
			), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["ADV_ID"]).'</a>]';

		$str .= ($arRes["ADV_BACK"] == "Y"? "* ": " ");

		if ($arRes["REFERER1"] != '')
		{
			$str .= ' / <a title="'.GetMessage("STAT_VIEW_REFERER_1").'" href="'.htmlspecialcharsbx(CHTTP::urlAddParams("session_list.php", array(
					'lang' => LANGUAGE_ID,
					'find_referer1' => '"'.$arRes["REFERER1"].'"',
					'find_referer1_exact_match' => 'Y',
					'set_filter' => 'Y',
				), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["REFERER1"]).'</a>';
		}

		if ($arRes["REFERER2"] != '')
		{
			$str .= ' / <a title="'.GetMessage("STAT_VIEW_REFERER_2").'" href="'.htmlspecialcharsbx(CHTTP::urlAddParams("session_list.php", array(
					'lang' => LANGUAGE_ID,
					'find_referer2' => '"'.$arRes["REFERER2"].'"',
					'find_referer2_exact_match' => 'Y',
					'set_filter' => 'Y',
				), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["REFERER2"]).'</a>';
		}

		if ($arRes["REFERER3"] != '')
		{
			$str .= ' / <a title="'.GetMessage("STAT_VIEW_REFERER_3").'" href="'.htmlspecialcharsbx(CHTTP::urlAddParams("session_list.php", array(
					'lang' => LANGUAGE_ID,
					'find_referer3' => '"'.$arRes["REFERER3"].'"',
					'find_referer3_exact_match' => 'Y',
					'set_filter' => 'Y',
				), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["REFERER3"]).'</a>';
		}

		$row->AddViewField("ADV_ID", $str);
	}

	$str = "";
	if ($arRes["LAST_USER_ID"] > 0)
	{
		$str .= '[<a target="_blank" title="'.GetMessage("STAT_EDIT_USER").'" href="'.htmlspecialcharsbx(CHTTP::urlAddParams("user_edit.php", array(
				'lang' => LANGUAGE_ID,
				'ID' => $arRes["LAST_USER_ID"],
			), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["LAST_USER_ID"]).'</a>]';

		if (!array_key_exists($arRes["LAST_USER_ID"], $arrUsers))
		{
			$rsUser = CUser::GetByID($arRes["LAST_USER_ID"]);
			$arUser = $rsUser->GetNext();
			$arrUsers[$arRes["LAST_USER_ID"]] = array(
				"USER_NAME" => $arUser["NAME"] . " " . $arUser["LAST_NAME"],
				"LOGIN" => $arUser["LOGIN"],
			);
		}
		$USER_NAME = $arrUsers[$arRes["LAST_USER_ID"]]["USER_NAME"];
		$LOGIN = $arrUsers[$arRes["LAST_USER_ID"]]["LOGIN"];

		if (strlen($LOGIN) > 0)
			$str .= " (".$LOGIN.")".$USER_NAME;

		if ($arRes["USER_AUTH"] != "Y")
			$str .= '<br><span class="stat_notauth">'.GetMessage("STAT_NOT_AUTH").'</span>';
	}
	else
	{
		$str .= GetMessage("STAT_NOT_REGISTERED");
		if ($arRes["STOP_LIST_ID"] > 0)
			$str .= '<br><span class="stat_attention">'.GetMessage("STAT_STOP").'</span>';
	}

	$str .= "<br>";
	if ($arRes["NEW_GUEST"] == "Y")
		$str .= '<span class="stat_newguest">'.GetMessage("STAT_NEW_GUEST").'</span>';
	else
		$str .= '<span class="stat_oldguest">'.GetMessage("STAT_OLD_GUEST").'</span>';

	$str .=  '&nbsp;[<a href="'.htmlspecialcharsbx(CHTTP::urlAddParams("guest_list.php", array(
			'lang' => LANGUAGE_ID,
			'find_id' => $arRes["GUEST_ID"],
			'find_id_exact_match' => 'Y',
			'set_filter' => 'Y',
		), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["GUEST_ID"]).'</a>]';

	$row->AddViewField("LAST_USER_ID", $str);

	$row->AddViewField("URL_LAST", StatAdminListFormatURL($arRes["URL_LAST"], array(
		"new_window" => true,
		"attention" => $arRes["URL_LAST_404"] == "Y",
		"max_display_chars" => "default",
		"chars_per_line" => "default",
		"kill_sessid" => $STAT_RIGHT < "W",
	)));

	if($arRes["URL_FROM"] != '')
		$row->AddViewField("URL_FROM", StatAdminListFormatURL($arRes["URL_FROM"], array(
			"new_window" => true,
			"max_display_chars" => "default",
			"chars_per_line" => "default",
			"kill_sessid" => $STAT_RIGHT < "W",
		)));

	if($arRes["FIRST_URL_FROM"] != '')
	{
		$row->AddViewField("FIRST_URL_FROM", StatAdminListFormatURL($arRes["FIRST_URL_FROM"], array(
			"new_window" => true,
			"max_display_chars" => "default",
			"chars_per_line" => "default",
			"kill_sessid" => $STAT_RIGHT < "W",
		)));
	}

	$row->AddViewField("HITS", '<a href="'.htmlspecialcharsbx(CHTTP::urlAddParams("hit_list.php", array(
			'lang' => LANGUAGE_ID,
			'find_guest_id' => $arRes["GUEST_ID"],
			'find_guest_id_exact_match' => 'Y',
			'set_filter' => 'Y',
		), array("encode" => true))).'">'.htmlspecialcharsEx($arRes["HITS"]).'</a>');

	$row->AddViewField("IP_LAST", GetWhoisLink($arRes["IP_LAST"]));

	if ($arRes["COUNTRY_ID"] != '')
		$row->AddViewField("COUNTRY_ID", htmlspecialcharsEx("[".$arRes["COUNTRY_ID"]."] ".$arRes["COUNTRY_NAME"]));

	if ($arRes["CITY_ID"] > 0)
		$row->AddViewField("CITY_ID", htmlspecialcharsEx("[".$arRes["CITY_ID"]."] ".$arRes["CITY_NAME"]));

	$str = "";
	$duration = $arRes["SESSION_TIME"];
	$hours = intval($duration/3600);
	if ($hours > 0)
	{
		$str .= $hours . "&nbsp;" . GetMessage("STAT_HOUR") . " ";
		$duration = $duration - $hours * 3600;
	}
	$str .= intval($duration/60)."&nbsp;".GetMessage("STAT_MIN")." ";
	$str .= intval($duration%60)."&nbsp;".GetMessage("STAT_SEC");

	$row->AddViewField("SESSION_TIME", $str);

	$arr = explode(".", $arRes["IP_LAST"], 4);
	$arActions = array(
		array(
			"ICON" => "list",
			"TEXT" => GetMessage("STAT_DETAIL"),
			"ACTION" => "javascript:jsUtils.OpenWindow('".CUtil::JSEscape(CHTTP::urlAddParams("guest_detail.php", array(
					'lang' => LANGUAGE_ID,
					'ID' => $arRes["GUEST_ID"],
					), array("encode" => true)))."', '700', '550');",
			"DEFAULT" => "Y",
		),
		array("SEPARATOR" => true),
		array(
			"ICON" => "delete",
			"TITLE" => GetMessage("STAT_ADD_TO_STOPLIST_TITLE"),
			"TEXT" => GetMessage("STAT_STOP"),
			"ACTION" => $lAdmin->ActionRedirect(CHTTP::urlAddParams("stoplist_edit.php", array(
					'lang' => LANGUAGE_ID,
					'net1' => $arr[0],
					'net2' => $arr[1],
					'net3' => $arr[2],
					'net4' => $arr[3],
				), array("encode" => true))),
		),
	);
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsData->SelectedRowsCount()),
));

$lAdmin->BeginPrologContent();?>

<p><?=GetMessage("STAT_REFRESH_TIME");?>
<?
	foreach($arDelay as $value)
	{
		if($value != $delay)
		{
			?> <a target="_top" href="javascript:Refresh(<?echo $value?>);"><?echo $value?></a> / <?
		}
		else
		{
			?> <? echo $value ?> / <?
		}
	}
	echo GetMessage("STAT_SEC");
?>
&nbsp;/&nbsp;<a target="_top" href="javascript:Refresh(<?=$delay?>);"><?=GetMessage("STAT_REFRESH");?></a>

&nbsp;(<span id="counter"><?=$delay;?></span>)
</p>
<?
$lAdmin->EndPrologContent();

$lAdmin->AddAdminContextMenu();
$lAdmin->CheckListMode();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<script language="JavaScript">
var timeID = null;
var timeCounterID = null;

function Refresh(delay)
{
	delay = parseInt(delay);
	if (delay <= 0)
		delay = 30;
	if (timeID) clearTimeout(timeID);
	if (timeCounterID) clearTimeout(timeCounterID);
	<?=$sTableID?>.GetAdminList('/bitrix/admin/users_online.php?delay='+delay+'&lang=<?=LANG?>');
	timeID = setTimeout('Refresh('+delay+')', delay*1000);
	timeCounterID = setTimeout('ShowCounter('+delay+')',950);
}

function ShowCounter(counter)
{
	document.getElementById("counter").innerHTML = counter;
	if(counter == 0)
		return;
	counter--;
	timeCounterID = setTimeout('ShowCounter('+counter+')', 950);
}

timeID = setTimeout('Refresh("<?echo $delay?>")',<?echo $delay?>000);
BX.ready(function(){ShowCounter(<?echo $delay?>);});

</script>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$filter->Begin();
$arrYN = array(
	"reference" => array(GetMessage("STAT_YES"), GetMessage("STAT_NO")),
	"reference_id" => array("Y","N")
);
?>

<tr>
	<td><?echo GetMessage("STAT_F_USER")?>:</td>
	<td><input type="text" name="find_user" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_user"])?>"><?=ShowExactMatchCheckbox("find_user")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_F_ID")?>:</td>
	<td><input type="text" name="find_id" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_id"])?>"><?=ShowExactMatchCheckbox("find_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_GUEST_ID")?>:</td>
	<td><input type="text" name="find_guest_id" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_guest_id"])?>"><?=ShowExactMatchCheckbox("find_guest_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_F_AUTH")?>:</td>
	<td><?echo SelectBoxFromArray("find_registered", $arrYN, $adminFilter["find_registered"], GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_NEW_GUEST")?>:</td>
	<td><?
		$arr = array(
			"reference" => array(GetMessage("STAT_NEW_GUEST_1"), GetMessage("STAT_OLD_GUEST_1")),
			"reference_id" => array("Y", "N")
		);
		echo SelectBoxFromArray("find_new_guest", $arr, $adminFilter["find_new_guest"], GetMessage("MAIN_ALL"));
	?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_IP")?>:</td>
	<td><input type="text" name="find_ip" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_ip"])?>"><?=ShowExactMatchCheckbox("find_ip")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_COUNTRY")?>:</td>
	<td>[&nbsp;<input type="text" name="find_country_id" size="5" value="<?echo htmlspecialcharsbx($adminFilter["find_country_id"])?>">&nbsp;]<?
		?>&nbsp;<input type="text" name="find_country" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_country"])?>"><?=ShowExactMatchCheckbox("find_country")?>&nbsp;<?=ShowFilterLogicHelp()?>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_STOP")?>:</td>
	<td><?echo SelectBoxFromArray("find_stop", $arrYN, $adminFilter["find_stop"], GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_STOP_LIST_ID")?>:</td>
	<td><input type="text" name="find_stop_list_id" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_stop_list_id"])?>"><?=ShowExactMatchCheckbox("find_stop_list_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_HITS")?>:</td>
	<td><input type="text" name="find_hits1" size="10" value="<?echo htmlspecialcharsbx($adminFilter["find_hits1"])?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_hits2" size="10" value="<?echo htmlspecialcharsbx($adminFilter["find_hits2"])?>"></td>
</tr>

<tr>
	<td><?echo GetMessage("STAT_F_CAME_ADV")?>:</td>
	<td><?echo SelectBoxFromArray("find_adv", $arrYN, htmlspecialcharsbx($adminFilter["find_adv"]), GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ADV")?>:</td>
	<td><input type="text" name="find_adv_id" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_adv_id"])?>"><?=ShowExactMatchCheckbox("find_adv_id")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>referer1 / referer2:</td>
	<td><input type="text" name="find_referer1" size="14" value="<?echo htmlspecialcharsbx($adminFilter["find_referer1"])?>">&nbsp;/&nbsp;<input type="text" name="find_referer2" size="14" value="<?echo htmlspecialcharsbx($adminFilter["find_referer2"])?>"><?=ShowExactMatchCheckbox("find_referer12")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>referer3:</td>
	<td><input type="text" name="find_referer3" size="30" value="<?echo htmlspecialcharsbx($adminFilter["find_referer3"])?>"><?=ShowExactMatchCheckbox("find_referer3")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_ADV_BACK")?>:</td>
	<td><?echo SelectBoxFromArray("find_adv_back", $arrYN, $adminFilter["find_adv_back"], GetMessage("MAIN_ALL"));?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_FIRST_FROM_PAGE")?>:</td>
	<td><input type="text" name="find_first_from" size="34" value="<?echo htmlspecialcharsbx($adminFilter["find_first_from"])?>"><?=ShowExactMatchCheckbox("find_first_from")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_URL_LAST")?>:</td>
	<td><?
		echo SelectBoxFromArray("find_last_site_id", $arSiteDropdown, $adminFilter["find_last_site_id"], GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_url_last_404", $arr, $adminFilter["find_url_last_404"], GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_url_last" size="34" value="<?echo htmlspecialcharsbx($adminFilter["find_url_last"])?>"><?=ShowExactMatchCheckbox("find_url_last")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<?
$filter->Buttons(array(
	"table_id" => $sTableID,
	"url" => $APPLICATION->GetCurPage(),
	"form" => "form1",
));
$filter->End();?>
</form>

<?
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
