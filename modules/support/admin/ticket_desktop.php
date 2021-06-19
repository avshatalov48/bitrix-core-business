<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";

if($bAdmin!="Y" && $bSupportTeam!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

/***************************************************************************
								Функции
***************************************************************************/
function CheckFilter() // проверка введенных полей
{
	global $strError, $FilterArr;
	reset($FilterArr); foreach ($FilterArr as $f) global $$f;
	$str = "";
	$arMsg = Array();

	if (trim($find_date1) <> '' || trim($find_date2) <> '')
	{
		$date_1_ok = false;
		$date1_stm = MkDateTime(ConvertDateTime($find_date1,"D.M.Y"),"d.m.Y");
		$date2_stm = MkDateTime(ConvertDateTime($find_date2,"D.M.Y")." 23:59","d.m.Y H:i");

		if (!$date1_stm && trim($find_date1) <> '')
			//$str.= GetMessage("SUP_WRONG_DATE_FROM")."<br>";
			$arMsg[] = array("id"=>"find_date1", "text"=> GetMessage("SUP_WRONG_DATE_FROM"));
		else
			$date_1_ok = true;

		if (!$date2_stm && trim($find_date2) <> '')
			//$str.= GetMessage("SUP_WRONG_DATE_TILL")."<br>";
			$arMsg[] = array("id"=>"find_date2", "text"=> GetMessage("SUP_WRONG_DATE_TILL"));
		elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
			//$str.= GetMessage("SUP_FROM_TILL_DATE")."<br>";
			$arMsg[] = array("id"=>"find_date2", "text"=> GetMessage("SUP_FROM_TILL_DATE"));
	}

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;
}

function fill_all_values($sid, $type, $mess=false, $site=false)
{
	global $arrTickets, $arrT, $MESS;
	if(is_array($site))
	{
		$site = implode("|", $site);
	}
	$z = ($type=="SLA") ? CTicketSLA::GetDropDown($site) : CTicketDictionary::GetList("s_dropdown", "asc", array("TYPE" => $type, "SITE" => $site));
	if ($type!="SLA")
	{
		if ($mess===false) $mess = GetMessage("SUP_NO");
		$arrTickets[$sid][0]["NAME"] = "(".$mess.")";
		$arrTickets[$sid][0]["COUNTER_OPEN"] = $arrT[$sid][0]["COUNTER_OPEN"];
		$arrTickets[$sid][0]["COUNTER_OPEN_RED"] = $arrT[$sid][0]["COUNTER_OPEN_RED"];
		$arrTickets[$sid][0]["COUNTER_OPEN_GREEN"] = $arrT[$sid][0]["COUNTER_OPEN_GREEN"];
		$arrTickets[$sid][0]["COUNTER_CLOSE"] = $arrT[$sid][0]["COUNTER_CLOSE"];
		$arrTickets[$sid][0]["MESSAGES_OPEN"] = $arrT[$sid][0]["MESSAGES_OPEN"];
		$arrTickets[$sid][0]["OVERDUE_MESSAGES_OPEN"] = $arrT[$sid][0]["OVERDUE_MESSAGES_OPEN"];
		$arrTickets[$sid][0]["MESSAGES_CLOSE"] = $arrT[$sid][0]["MESSAGES_CLOSE"];
		$arrTickets[$sid][0]["OVERDUE_MESSAGES_CLOSE"] = $arrT[$sid][0]["OVERDUE_MESSAGES_CLOSE"];
	}
	while ($zr = $z->Fetch())
	{
		$arrTickets[$sid][$zr["ID"]]["NAME"] = "[<a title='".GetMessage("MAIN_ADMIN_MENU_EDIT")."' href='/bitrix/admin/ticket_dict_edit.php?ID=".$zr["ID"]."'>".$zr["ID"]."</a>] ".htmlspecialcharsbx($zr["NAME"]);
		$arrTickets[$sid][$zr["ID"]]["COUNTER_OPEN"] = $arrT[$sid][$zr["ID"]]["COUNTER_OPEN"];
		$arrTickets[$sid][$zr["ID"]]["COUNTER_OPEN_RED"] = $arrT[$sid][$zr["ID"]]["COUNTER_OPEN_RED"];
		$arrTickets[$sid][$zr["ID"]]["COUNTER_OPEN_GREEN"] = $arrT[$sid][$zr["ID"]]["COUNTER_OPEN_GREEN"];
		$arrTickets[$sid][$zr["ID"]]["COUNTER_CLOSE"] = $arrT[$sid][$zr["ID"]]["COUNTER_CLOSE"];
		$arrTickets[$sid][$zr["ID"]]["MESSAGES_OPEN"] = $arrT[$sid][$zr["ID"]]["MESSAGES_OPEN"];
		$arrTickets[$sid][$zr["ID"]]["OVERDUE_MESSAGES_OPEN"] = $arrT[$sid][$zr["ID"]]["OVERDUE_MESSAGES_OPEN"];
		$arrTickets[$sid][$zr["ID"]]["MESSAGES_CLOSE"] = $arrT[$sid][$zr["ID"]]["MESSAGES_CLOSE"];
		$arrTickets[$sid][$zr["ID"]]["OVERDUE_MESSAGES_CLOSE"] = $arrT[$sid][$zr["ID"]]["OVERDUE_MESSAGES_CLOSE"];
	}
}

function sup_sort($a,$b)
{
	$sort1 = intval($a["COUNTER_OPEN"])+intval($a["COUNTER_CLOSE"]);
	$sort2 = intval($b["COUNTER_OPEN"])+intval($b["COUNTER_CLOSE"]);
	if ($sort1==$sort2) return 0;
	if ($sort1<$sort2) return 1; else return -1;
}

/***************************************************************************
							Обработка GET | POST
****************************************************************************/

$sTableID = "t_ticket_desktop";
$oSort = new CAdminSorting($sTableID);// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка


$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("SUP_F_SITE"),
		GetMessage("SUP_F_RESPONSIBLE"),
	)
);

if ($set_default=="Y" && !isset($_SESSION["SESS_ADMIN"][$sTableID]) || empty($_SESSION["SESS_ADMIN"][$sTableID]) )
{
	$find_date1_DAYS_TO_BACK=1;
	//$find_date2 = ConvertTimeStamp(time()-86400, "SHORT");
	$set_filter = "Y";
}

$FilterArr = Array(
	"find_date1","find_date2",
	"find_site",
	"find_responsible_id","find_responsible","find_responsible_exact_match",
);

$lAdmin->InitFilter($FilterArr);//инициализация фильтра




if ($bAdmin!="Y" && $bDemo!="Y") $find_responsible_id = $USER->GetID();

InitBVar($find_responsible_exact_match);
if (CheckFilter())
{
	$arFilter = Array(
		"SITE"						=> $find_site,
		"DATE_CREATE_1"				=> $find_date1,
		"DATE_CREATE_2"				=> $find_date2,
		"RESPONSIBLE_ID"			=> $find_responsible_id,
		"RESPONSIBLE"				=> $find_responsible,
		"RESPONSIBLE_EXACT_MATCH"	=> $find_responsible_exact_match
		);
}
else
{
	if($e = $APPLICATION->GetException())
		$message = new CAdminMessage(GetMessage("SUP_FILTER_ERROR"), $e);
}

global $by, $order;

$rsTickets = CTicket::GetList($by, $order, $arFilter, null, "Y", "N", "N");
$OPEN_TICKETS = $CLOSE_TICKETS = 0;
$arrTickets = array();
$arrValues = array(
	"STATUS",
	"CATEGORY",
	"CRITICALITY",
	"SOURCE",
	"MARK",
	"SLA",
	"DIFFICULTY"
	);
$arUsersID = array();
$arrT = array();
while ($arTicket = $rsTickets->Fetch())
{
	$mess_count = $arTicket["MESSAGES"];
	$mess_overdue_count = $arTicket["OVERDUE_MESSAGES"];
	if (intval($arTicket["RESPONSIBLE_USER_ID"])>0)
	{
		/*$rsUser = CUser::GetByID($arTicket["RESPONSIBLE_USER_ID"]);
		$arUser = $rsUser->Fetch();
		$arrSupportUser[$arTicket["RESPONSIBLE_USER_ID"]] = $arUser;*/
		$arUsersID[] = intval($arTicket["RESPONSIBLE_USER_ID"]);
		//$R_NAME = "[<a href='/bitrix/admin/user_edit.php?ID=".$arTicket["RESPONSIBLE_USER_ID"]."'>".$arTicket["RESPONSIBLE_USER_ID"]."</a>] (".htmlspecialcharsbx($arUser["LOGIN"]).") ".htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);
	}
	else
	{
		//$R_NAME = "(".GetMessage("SUP_NO").")";
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["NAME"] = "(".GetMessage("SUP_NO").")";
	}


	reset($arrValues);
	foreach ($arrValues as $v)
		$arrT[$v][intval($arTicket[$v."_ID"])]["ID"] = intval($arTicket[$v."_ID"]);

	if ($arTicket["DATE_CLOSE"] == '')
	{
		$OPEN_TICKETS++;
		$OPEN_MESSAGES += $mess_count;
		$OPEN_OVERDUE_MESSAGES += $mess_overdue_count;
		reset($arrValues);
		foreach ($arrValues as $v)
		{
			$arrT[$v][intval($arTicket[$v."_ID"])]["COUNTER_OPEN"] += 1;

			if ($arTicket["LAST_MESSAGE_BY_SUPPORT_TEAM"]=="Y")
				$arrT[$v][intval($arTicket[$v."_ID"])]["COUNTER_OPEN_GREEN"] += 1;
			elseif ($arTicket["LAST_MESSAGE_BY_SUPPORT_TEAM"]=="N")
				$arrT[$v][intval($arTicket[$v."_ID"])]["COUNTER_OPEN_RED"] += 1;
		}
		reset($arrValues);
		foreach ($arrValues as $v)
		{
			$arrT[$v][intval($arTicket[$v."_ID"])]["MESSAGES_OPEN"] += $mess_count;
			$arrT[$v][intval($arTicket[$v."_ID"])]["OVERDUE_MESSAGES_OPEN"] += $mess_overdue_count;
		}
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["COUNTER_OPEN"] += 1;
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["MESSAGES_OPEN"] += $mess_count;
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["OVERDUE_MESSAGES_OPEN"] += $mess_overdue_count;

		if ($arTicket["LAST_MESSAGE_BY_SUPPORT_TEAM"]=="Y")
		{
			$OPEN_TICKETS_GREEN++;
			$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["COUNTER_OPEN_GREEN"] += 1;
		}
		elseif ($arTicket["LAST_MESSAGE_BY_SUPPORT_TEAM"]!="Y")
		{
			$OPEN_TICKETS_RED++;
			$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["COUNTER_OPEN_RED"] += 1;
		}

	}
	else
	{
		$CLOSE_TICKETS++;
		$CLOSE_MESSAGES += $mess_count;
		$CLOSE_OVERDUE_MESSAGES += $mess_overdue_count;
		reset($arrValues);
		foreach ($arrValues as $v)
			$arrT[$v][intval($arTicket[$v."_ID"])]["COUNTER_CLOSE"] += 1;
		reset($arrValues);
		foreach ($arrValues as $v)
		{
			$arrT[$v][intval($arTicket[$v."_ID"])]["MESSAGES_CLOSE"] += $mess_count;
			$arrT[$v][intval($arTicket[$v."_ID"])]["OVERDUE_MESSAGES_CLOSE"] += $mess_overdue_count;
		}
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["COUNTER_CLOSE"] += 1;
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["MESSAGES_CLOSE"] += $mess_count;
		$arrT["RESPONSIBLE"][intval($arTicket["RESPONSIBLE_USER_ID"])]["OVERDUE_MESSAGES_CLOSE"] += $mess_overdue_count;
	}
}

if(count($arUsersID) > 0)
{
	$arrSupportUser = array();
	$arUsersID = array_unique($arUsersID);
	$strUsers = implode("|", $arUsersID);
	$rs = CUser::GetList('id', 'asc', array( "ID" => $strUsers), array("FIELDS"=>array("NAME","LAST_NAME","LOGIN","ID")));
	while($ar = $rs->Fetch())
	{
		$arrT["RESPONSIBLE"][$ar["ID"]]["NAME"] = "[<a href='/bitrix/admin/user_edit.php?ID=" . $ar["ID"] . "'>" . $ar["ID"] . "</a>] " .
			htmlspecialcharsbx("(" . $ar["LOGIN"] . ") " . $ar["NAME"] . " " . $ar["LAST_NAME"]);
		$arrSupportUser[$ar["ID"]] = $ar;
	}
}

// сортировка порядка вывода таблиц
$arrTickets["RESPONSIBLE"] = $arrT["RESPONSIBLE"];
fill_all_values("CRITICALITY", "K", false, $find_site);
fill_all_values("STATUS", "S", false, $find_site);
fill_all_values("DIFFICULTY", "D", false, $find_site);
fill_all_values("CATEGORY", "C", false, $find_site);
fill_all_values("SOURCE", "SR", "web", $find_site);
fill_all_values("MARK", "M", false, $find_site);
fill_all_values("SLA", "SLA", false, $find_site);

// сортировка значений внутри таблиц
if (is_array($arrTickets["RESPONSIBLE"]))	uasort($arrTickets["RESPONSIBLE"], "sup_sort");
if (is_array($arrTickets["CRITICALITY"]))	uasort($arrTickets["CRITICALITY"], "sup_sort");
if (is_array($arrTickets["STATUS"]))		uasort($arrTickets["STATUS"], "sup_sort");
if (is_array($arrTickets["DIFFICULTY"]))		uasort($arrTickets["DIFFICULTY"], "sup_sort");
if (is_array($arrTickets["CATEGORY"]))		uasort($arrTickets["CATEGORY"], "sup_sort");
if (is_array($arrTickets["SOURCE"]))		uasort($arrTickets["SOURCE"], "sup_sort");
if (is_array($arrTickets["MARK"]))			uasort($arrTickets["MARK"], "sup_sort");
if (is_array($arrTickets["SLA"]))			uasort($arrTickets["SLA"], "sup_sort");


// filter parameters for ticket list urls
$tlist_filter_pass = '';
$tlist_filter_pass_array = array();

foreach ($_GET as $k => $v)
{
	if (mb_strpos($k, 'find_') === 0)
	{
		$tlist_filter_pass_array[$k] = $v;
	}
}

if (count($tlist_filter_pass_array))
{
	$tlist_filter_pass = '&' . http_build_query($tlist_filter_pass_array);
	$tlist_filter_pass = str_replace('find_date', 'find_date_create', $tlist_filter_pass);
}

$lAdmin->BeginCustomContent();?>

<h2><?echo GetMessage("SUP_SERVER_TIME")."&nbsp;".GetTime(time(),"FULL")?></h2>

<?
if ($message)
	echo $message->Show();

foreach ($arrTickets as $key => $arrR):
	$w1 = 45;
	$w2 = round((100-$w1)/8);
	if ($find_show_messages!="Y") $w2 = round((100-$w1)/5);
?>

<table border="0" cellspacing="1" cellpadding="3" width="100%" class="list-table">
	<?if ($find_show_messages!="Y"):?>

	<tr class="head">
		<td align="center" style="width:100%"><b><?echo GetMessage("SUP_".$key)?></b></td>
		<td align="center"><div class="lamp-red" title="<?echo GetMessage("SUP_RED_ALT")?>"></div></td>
		<td align="center"><div class="lamp-green" title="<?echo GetMessage("SUP_GREEN_ALT")?>"></div></td>
		<td  align="center"><div class="lamp-grey" title="<?echo GetMessage("SUP_GREY_ALT")?>"></div>
		</td>
		<td align="center"><?=GetMessage("SUP_OPEN")?></td>
		<td align="center"><?echo GetMessage("SUP_TOTAL")?></td>
	</tr>


	<?else:?>


	<tr class="head">
		<td align="center" style="width:100%"><b><?echo GetMessage("SUP_".$key)?></b></td>
		<td align="center"><div class="lamp-red" title="<?echo GetMessage("SUP_RED_ALT")?>"></div>
		</td>
		<td align="center"><div class="lamp-green" title="<?echo GetMessage("SUP_GREEN_ALT")?>"></div></td>
		<td  align="center"><div class="lamp-grey" title="<?echo GetMessage("SUP_GREY_ALT")?>"></div></td>

		<td align="center" colspan="2"><?=GetMessage("SUP_MESSAGES")?></td>

		<td align="center"><?=GetMessage("SUP_OPEN")?></td>

		<td align="center" colspan="2"><?=GetMessage("SUP_MESSAGES")?></td>

		<td align="center"><?echo GetMessage("SUP_TOTAL")?></td>

		<td align="center" colspan="2"><?=GetMessage("SUP_MESSAGES")?></td>
	</tr>

	<?endif;?>

	<?
	if (is_array($arrR) && count($arrR)>0) :
	foreach ($arrR as $id => $arr):
		if (intval($CLOSE_TICKETS)>0)
		{
			$procent = round(($counter*100)/$CLOSE_TICKETS,2);
		}
		else
		{
			$procent = 0;
		}
	?>
	<tr valign="top">
		<td><?=$arr["NAME"]?></td>

		<td align="right">&nbsp;<a title="<?echo ($OPEN_TICKETS>0?round(($arr["COUNTER_OPEN_RED"]*100)/$OPEN_TICKETS,2):"0")."% ".GetMessage("SUP_FROM_OPEN_TICKETS")?>" href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_close=N&find_lamp[]=red&find_lamp[]=yellow&set_filter=Y<?=$tlist_filter_pass?>"><?=(intval($arr["COUNTER_OPEN_RED"])>0) ? $arr["COUNTER_OPEN_RED"] : ""?></a></td>

		<td align="right">&nbsp;<a title="<?echo ($OPEN_TICKETS>0?round(($arr["COUNTER_OPEN_GREEN"]*100)/$OPEN_TICKETS,2):"0")."% ".GetMessage("SUP_FROM_OPEN_TICKETS")?>" href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_close=N&find_lamp[]=green&find_lamp[]=green_s&set_filter=Y<?=$tlist_filter_pass?>"><?=intval($arr["COUNTER_OPEN_GREEN"])>0 ? $arr["COUNTER_OPEN_GREEN"] : ""?></a></td>


		<td align="right">&nbsp;<a title="<?echo ($CLOSE_TICKETS>0 ? round(($arr["COUNTER_CLOSE"]*100)/$CLOSE_TICKETS,2) : "0")."% ".GetMessage("SUP_FROM_CLOSE_TICKETS")?>" href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_close=Y&set_filter=Y<?=$tlist_filter_pass?>"><?=intval($arr["COUNTER_CLOSE"])>0 ? $arr["COUNTER_CLOSE"] : ""?></a></td>

		<?if ($find_show_messages=="Y"):?>
		<td align="right">&nbsp;<span title="<?echo ($CLOSE_MESSAGES>0 ? round(($arr["MESSAGES_CLOSE"]*100)/$CLOSE_MESSAGES,2):"0")."% ".GetMessage("SUP_FROM_CLOSE_MESSAGES")?>"><?echo (intval($arr["MESSAGES_CLOSE"])>0) ? $arr["MESSAGES_CLOSE"] : ""?></span></td>

		<td align="right"><span title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>">&nbsp;<?
			if (intval($arr["OVERDUE_MESSAGES_CLOSE"])>0):
				?><a title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>"  href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_overdue_messages1=1&find_close=Y&set_filter=Y<?=$tlist_filter_pass?>" ><?=$arr["OVERDUE_MESSAGES_CLOSE"]?></a><?
			endif;
			?></span></td>
		<?endif;?>

		<td align="right">&nbsp;<a title="<?echo ($OPEN_TICKETS>0?round(($arr["COUNTER_OPEN"]*100)/$OPEN_TICKETS,2):"0")."% ".GetMessage("SUP_FROM_OPEN_TICKETS")?>" href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_close=N&set_filter=Y<?=$tlist_filter_pass?>"><?=intval($arr["COUNTER_OPEN"])>0 ? $arr["COUNTER_OPEN"] : ""?></a></td>

		<?if ($find_show_messages=="Y"):?>
		<td align="right"><span title="<?echo ($OPEN_MESSAGES>0 ? round(($arr["MESSAGES_OPEN"]*100)/$OPEN_MESSAGES,2) : "0")."% ".GetMessage("SUP_FROM_OPEN_MESSAGES")?>">&nbsp;<?=intval($arr["MESSAGES_OPEN"])>0 ? $arr["MESSAGES_OPEN"] : ""?></span></td>

		<td align="right">&nbsp;<?
			if (intval($arr["OVERDUE_MESSAGES_OPEN"])>0):
				?><a title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>"  href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_overdue_messages1=1&find_close=N&set_filter=Y<?=$tlist_filter_pass?>" ><?=$arr["OVERDUE_MESSAGES_OPEN"]?></a><?
			endif;
			?></td>
		<?endif;?>


		<td align="right">&nbsp;<a title="<?=(($OPEN_TICKETS+$CLOSE_TICKETS)>0 ? round((($arr["COUNTER_OPEN"]+$arr["COUNTER_CLOSE"])*100)/($OPEN_TICKETS+$CLOSE_TICKETS),2): "0")."% ".GetMessage("SUP_FROM_ALL_TICKETS")?>" href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&set_filter=Y<?=$tlist_filter_pass?>"><?=(intval($arr["COUNTER_OPEN"]+$arr["COUNTER_CLOSE"])>0) ? $arr["COUNTER_OPEN"]+$arr["COUNTER_CLOSE"] : ""?></a></td>

		<?if ($find_show_messages=="Y"):?>
		<td nowrap><span title="<?echo (($OPEN_MESSAGES+ $CLOSE_MESSAGES)>0 ? round((($arr["MESSAGES_OPEN"]+$arr["MESSAGES_CLOSE"])*100)/($OPEN_MESSAGES+ $CLOSE_MESSAGES),2): "0")."% ".GetMessage("SUP_FROM_ALL_MESSAGES")?>">&nbsp;<?=intval($arr["MESSAGES_OPEN"]+$arr["MESSAGES_CLOSE"])>0 ? $arr["MESSAGES_OPEN"]+$arr["MESSAGES_CLOSE"] : ""?></span></td>

		<td align="right">&nbsp;<?
			if (intval($arr["OVERDUE_MESSAGES_OPEN"]+$arr["OVERDUE_MESSAGES_CLOSE"])>0):
				?><a title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>"  href="ticket_list.php?lang=<?=LANGUAGE_ID?>&find_<?= mb_strtolower($key)?>_id=<?=$id?>&find_overdue_messages1=1&set_filter=Y<?=$tlist_filter_pass?>" ><?=$arr["OVERDUE_MESSAGES_OPEN"]+$arr["OVERDUE_MESSAGES_CLOSE"]?></a><?
			endif;
			?></td>
		<?endif;?>
	</tr>
	<?
	endforeach;
	endif;
	?>
	<?if ($key=="RESPONSIBLE" && ($bAdmin=="Y" || $bDemo=="Y")):?>
	<tr valign="top" class="head">
		<td align="right"><?=GetMessage("SUP_TOTAL")?>:</td>

		<td align="right">&nbsp;<?if (intval($OPEN_TICKETS_RED)>0):?><a href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_lamp[]=red&find_lamp[]=yellow&set_filter=Y<?=$tlist_filter_pass?>"><?echo intval($OPEN_TICKETS_RED)?></a><?endif;?></td>

		<td align="right">&nbsp;<?if(intval($OPEN_TICKETS_GREEN)>0):?><a href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_lamp[]=green&find_lamp[]=green_s&set_filter=Y<?=$tlist_filter_pass?>"><?echo intval($OPEN_TICKETS_GREEN)?></a><?endif;?></td>



		<td align="right">&nbsp;<?if(intval($CLOSE_TICKETS)>0):?><a href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_close=Y&set_filter=Y<?=$tlist_filter_pass?>"><?=$CLOSE_TICKETS?></a><?endif;?></td>

		<?if ($find_show_messages=="Y"):?>
		<td align="right">&nbsp;<?=intval($CLOSE_MESSAGES)>0 ? intval($CLOSE_MESSAGES) : ""?></td>

		<td align="right">&nbsp;<?
			if (intval($CLOSE_OVERDUE_MESSAGES)>0):
				?><a title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>" href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_overdue_messages1=1&find_close=Y&set_filter=Y<?=$tlist_filter_pass?>" ><?=intval($CLOSE_OVERDUE_MESSAGES)?></a><?
			endif;
			?></td>
		<?endif;?>

		<td align="right">&nbsp;<?if(intval($OPEN_TICKETS)>0):?><a href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_close=N&set_filter=Y<?=$tlist_filter_pass?>"><?=$OPEN_TICKETS?></a><?endif;?></td>

		<?if ($find_show_messages=="Y"):?>
		<td nowrap>&nbsp;<?if(intval($OPEN_MESSAGES)>0):?><?=intval($OPEN_MESSAGES)?><?endif;?></td>

		<td align="right">&nbsp;<?
			if(intval($OPEN_OVERDUE_MESSAGES)>0):
				?><a title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>" href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_overdue_messages1=1&find_close=N&set_filter=Y<?=$tlist_filter_pass?>" ><?=intval($OPEN_OVERDUE_MESSAGES)?></a><?
			endif;
			?></td>
		<?endif;?>


		<td align="right">&nbsp;<?if (intval($OPEN_TICKETS+$CLOSE_TICKETS)>0):?><a href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&del_filter=Y<?=$tlist_filter_pass?>"><?=$OPEN_TICKETS+$CLOSE_TICKETS?></a><?endif;?></td>

		<?if ($find_show_messages=="Y"):?>
		<td align="right">&nbsp;<?=intval($OPEN_MESSAGES+$CLOSE_MESSAGES)>0 ? intval($OPEN_MESSAGES+$CLOSE_MESSAGES) : ""?></td>

		<td align="right">&nbsp;<?
			if (intval($OPEN_OVERDUE_MESSAGES+$CLOSE_OVERDUE_MESSAGES)>0):
				?><a title="<?=GetMessage("SUP_OVERDUE_MESSAGES")?>" href="/bitrix/admin/ticket_list.php?lang=<?=LANGUAGE_ID?>&find_overdue_messages1=1&set_filter=Y<?=$tlist_filter_pass?>" ><?=intval($OPEN_OVERDUE_MESSAGES+$CLOSE_OVERDUE_MESSAGES)?></a><?
			endif;
			?></td>
		<?endif;?>
	</tr>
	<?endif;?>
</table><br>
<?endforeach;?>

<?
$lAdmin->EndCustomContent();

$aContext = array(
	array(
		"TEXT"=>GetMessage("SUP_F_SHOW_MESSAGES_S").": ".($find_show_messages=="Y" ? GetMessage("MAIN_YES") :GetMessage("MAIN_NO")),
		"MENU"=>array(
			array(
				"TEXT"=>GetMessage("MAIN_YES"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_show_messages=Y"),
				"ICON"=>($find_show_messages=="Y"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("MAIN_NO"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_show_messages=N"),
				"ICON"=>($find_show_messages!="Y"?"checked":""),
			),
		),
	),
);
$lAdmin->AddAdminContextMenu($aContext, false, false);



$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SUP_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("SUP_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<tr valign="top">
	<td valign="top"><?=GetMessage("SUP_F_SITE")?>:</td>
	<td><?
		$ref = array();
		$ref_id = array();
		$rs = CSite::GetList();
		while ($ar = $rs->Fetch())
		{
			$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
			$ref_id[] = $ar["ID"];
		}
		echo SelectBoxMFromArray("find_site[]", array("reference" => $ref, "reference_id" => $ref_id), $find_site, "",false,"3");
		?></td>
</tr>
<tr>
	<td nowrap valign="top"><?=GetMessage("SUP_F_RESPONSIBLE")?>:</td>
	<td><?
		if ($bAdmin=="Y" || $bDemo=="Y")	:
			$ref = array(); $ref_id = array();
			$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
			$z = CTicket::GetSupportTeamList();
			while ($zr = $z->Fetch())
			{
				$ref[] = $zr["REFERENCE"];
				$ref_id[] = $zr["REFERENCE_ID"];
			}
			if (is_array($arrSupportUser) && count($arrSupportUser)>0)
			{
				foreach ($arrSupportUser as $key => $arUser)
				{
					if (!in_array($key,$ref_id))
					{
						$ref[] = $arUser["LAST_NAME"]." ".$arUser["NAME"]." (".$arUser["LOGIN"].") "."[".$key."]";
						$ref_id[] = $key;
					}
				}
			}
			$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
			echo SelectBoxFromArray("find_responsible_id", $arr, htmlspecialcharsbx($find_responsible_id), GetMessage("SUP_ALL"));
			?><br><input type="text" name="find_responsible" size="47" value="<?=htmlspecialcharsbx($find_responsible)?>"><?=InputType("checkbox", "find_responsible_exact_match", "Y", $find_responsible_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?><?
		else :
			?>[<a href="/bitrix/admin/user_edit.php?ID=<?=$USER->GetID()?>"><?=$USER->GetID()?></a>] (<?=htmlspecialcharsEx($USER->GetLogin())?>) <?=htmlspecialcharsEx($USER->GetFullName())?><?
		endif;
		?></td>
</tr>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>




<?$lAdmin->DisplayList();?>

<?echo BeginNote();?>
<table border="0" cellspacing="6" cellpadding="0">
	<tr>
		<td valign="center" nowrap><div class="lamp-red"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_RED_ALT")?></td>
	</tr>
	<tr>
		<td valign="center" nowrap><div class="lamp-green"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREEN_ALT")?></td>
	</tr>
	<tr>
		<td valign="center" nowrap><div class="lamp-grey"></div></td>
		<td valign="center" nowrap><?echo GetMessage("SUP_GREY_ALT")?></td>
	</tr>
</table>
<?echo EndNote();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>