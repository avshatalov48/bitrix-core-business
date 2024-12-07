<? 
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
$message = null;

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
	global $arFilterFields;
	reset($arFilterFields); foreach ($arFilterFields as $f) global $$f; 
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
			$arMsg[] = array("id"=>"find_date2", "text"=> GetMessage("SUP_FROM_TILL_DATE"));
			//$str.= GetMessage("SUP_FROM_TILL_DATE")."<br>";
	}

	if(!empty($arMsg))
	{
		$e = new CAdminException($arMsg);
		$GLOBALS["APPLICATION"]->ThrowException($e);
		return false;
	}

	return true;
}

/***************************************************************************
								Обработка GET | POST
****************************************************************************/



$sTableID = "t_report_graph";
$oSort = new CAdminSorting($sTableID);// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка

$filter = new CAdminFilter(
	"filter_id", 
	array(
		GetMessage("SUP_F_SITE"),
		GetMessage("SUP_F_RESPONSIBLE"),
		GetMessage("SUP_F_SLA"),
		GetMessage("SUP_F_CATEGORY"),
		GetMessage("SUP_F_CRITICALITY"),
		GetMessage("SUP_F_STATUS"),
		GetMessage("SUP_F_MARK"),
		GetMessage("SUP_F_SOURCE"),
		GetMessage("SUP_SHOW")
	)
);

if($lAdmin->IsDefaultFilter())
{
	//$find_date1_DAYS_TO_BACK=90;
	$find_date1_DAYS_TO_BACK=1;
	//$find_date2 = ConvertTimeStamp(time()-86400, "SHORT");
	$find_open = "Y";
	$find_close = "Y";
	$find_all = "Y";
	$find_mess = "Y";
	$find_overdue_mess = "Y";
	$set_filter = "Y";
}

$FilterArr1 = Array(
	"find_site",
	"find_responsible",
	"find_responsible_id",
	"find_responsible_exact_match",
	"find_category_id",
	"find_criticality_id",
	"find_status_id",
	"find_sla_id",
	"find_mark_id",
	"find_source_id",
	"find_date1",
	"find_date2",
	);
$FilterArr2 = Array(
	"find_open",
	"find_close",
	"find_all",
	"find_mess",
	"find_overdue_mess"
	);
$arFilterFields = array_merge($FilterArr1, $FilterArr2);


$lAdmin->InitFilter($arFilterFields);//инициализация фильтра




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
		"RESPONSIBLE_EXACT_MATCH"	=> $find_responsible_exact_match,		
		"SLA"						=> $find_sla_id,
		"CATEGORY"					=> $find_category_id,
		"CRITICALITY"				=> $find_criticality_id,
		"STATUS"					=> $find_status_id,
		"MARK"						=> $find_mark_id,
		"SOURCE"					=> $find_source_id,
		);
}
else
{
	if($e = $APPLICATION->GetException())
	{
		$message = new CAdminMessage(GetMessage("SUP_FILTER_ERROR"), $e);
		//$message = new CAdminMessage(GetMessage("SUP_FILTER_ERROR"), $e);
	}
}
global $by, $order;

$rsTickets = CTicket::GetList($by, $order, $arFilter, null, "Y", "N", "N");
$OPEN_TICKETS = $CLOSE_TICKETS = 0;
$arrTickets = array();
$arrTime = array();
$arrTime["1"] = 0;
$arrTime["1_2"] = 0;
$arrTime["2_3"] = 0;
$arrTime["3_4"] = 0;
$arrTime["4_5"] = 0;
$arrTime["5_6"] = 0;
$arrTime["6_7"] = 0;
$arrTime["7"] = 0;

$arrMess = array();
$arrMess["2_m"] = 0;
$arrMess["3_m"] = 0;
$arrMess["4_m"] = 0;
$arrMess["5_m"] = 0;
$arrMess["6_m"] = 0;
$arrMess["7_m"] = 0;
$arrMess["8_m"] = 0;
$arrMess["9_m"] = 0;
$arrMess["10_m"] = 0;
$arUsersID = array();

while ($arTicket = $rsTickets->Fetch())
{
	if ($arTicket["DATE_CREATE_SHORT"]!=$PREV_CREATE && $PREV_CREATE <> '')
	{
		$show_graph = "Y";
	}

	if ($arTicket["DATE_CLOSE"] == '')
	{
		$OPEN_TICKETS++;
	}
	else
	{
		$CLOSE_TICKETS++;
		$day_sec = 86400;
		$TT = $arTicket["TICKET_TIME"];

		if ($TT<$day_sec) $arrTime["1"] += 1;
		if ($TT>$day_sec && $TT<=2*$day_sec) $arrTime["1_2"] += 1;
		if ($TT>2*$day_sec && $TT<=3*$day_sec) $arrTime["2_3"] += 1;
		if ($TT>3*$day_sec && $TT<=4*$day_sec) $arrTime["3_4"] += 1;
		if ($TT>4*$day_sec && $TT<=5*$day_sec) $arrTime["4_5"] += 1;
		if ($TT>5*$day_sec && $TT<=6*$day_sec) $arrTime["5_6"] += 1;
		if ($TT>6*$day_sec && $TT<=7*$day_sec) $arrTime["6_7"] += 1;
		if ($TT>7*$day_sec) $arrTime["7"] += 1;		

		$MC = $arTicket["MESSAGES"];

		if ($MC<=2) $arrMess["2_m"] += 1;
		elseif ($MC>=10) $arrMess["10_m"] += 1;
		else $arrMess[$MC."_m"] += 1;
	}

	$PREV_CREATE = $arTicket["DATE_CREATE_SHORT"];

	if (intval($arTicket["RESPONSIBLE_USER_ID"])>0)
	{
		$arUsersID[] = intval($arTicket["RESPONSIBLE_USER_ID"]);
		/*$rsUser = CUser::GetByID($arTicket["RESPONSIBLE_USER_ID"]);
		$arUser = $rsUser->Fetch();
		$arrSupportUser[$arTicket["RESPONSIBLE_USER_ID"]] = $arUser;*/
	}
}

//================
$arrSupportUser = array();
$arUsersID = array_unique($arUsersID);
$strUsers = implode("|", $arUsersID);
$rs = CUser::GetList('id', 'asc', array( "ID" => $strUsers), array("FIELDS"=>array("NAME","LAST_NAME","LOGIN","ID")));
while($ar = $rs->Fetch())
{
	$arrSupportUser[$ar["ID"]] = $ar;
}

//================

$lAdmin->BeginCustomContent();

?>


<?

if ($message)
	echo $message->Show();

?>
<p><?echo GetMessage("SUP_SERVER_TIME")."&nbsp;".GetTime(time(),"FULL")?></p>
<h2><?=GetMessage("SUP_GRAPH_ALT")?></h2>
<?
if (!function_exists("ImageCreate")) : 
CAdminMessage::ShowMessage(GetMessage("SUP_GD_NOT_INSTALLED"));
elseif ($show_graph=="Y") :

$width = "576";
$height = "400";
?>
<div class="graph">


<table border="0" cellspacing="0" cellpadding="0" class="graph">
	<tr>
		<td>

<table border="0" cellspacing="1" cellpadding="0">
	<tr>
		<td> 
			<table cellpadding="1" cellspacing="0" border="0">
				<tr>
					<td valign="center" nowrap><img src="/bitrix/admin/ticket_graph.php?<?=GetFilterParams($arFilterFields)?>&width=<?=$width?>&height=<?=$height?>&lang=<?echo LANG?>" width="<?=$width?>" height="<?=$height?>"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>


		</td>
	</tr>
	<tr>
		<td> 
			<table cellpadding="3" cellspacing="1" border="0" class="legend">
				<?if ($find_open=="Y"):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/ticket_graph_legend.php?color=<?=$arrColor["OPEN_TICKET"]?>" width="45" height="2"></td>
					<td nowrap><?=GetMessage("SUP_OPEN_TICKET")?></td>
				</tr>
				<?endif;?>
				<?if ($find_close=="Y"):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/ticket_graph_legend.php?color=<?=$arrColor["CLOSE_TICKET"]?>" width="45" height="2"></td>
					<td nowrap><?=GetMessage("SUP_CLOSE_TICKET")?></td>
				</tr>
				<?endif;?>
				<?if ($find_all=="Y"):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/ticket_graph_legend.php?color=<?=$arrColor["ALL_TICKET"]?>" width="45" height="2"></td>
					<td nowrap><?=GetMessage("SUP_ALL_TICKET")?></td>
				</tr>
				<?endif;?>
				<?if ($find_mess=="Y"):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/ticket_graph_legend.php?color=<?=$arrColor["MESSAGES"]?>" width="45" height="2"></td>
					<td nowrap><?=GetMessage("SUP_MESSAGES")?></td>
				</tr>
				<?endif;?>
				<?if ($find_overdue_mess=="Y"):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/ticket_graph_legend.php?color=<?=$arrColor["OVERDUE_MESSAGES"]?>" width="45" height="2"></td>
					<td nowrap><?=GetMessage("SUP_OVERDUE_MESSAGES")?></td>
				</tr>
				<?endif;?>
			</table>
		</td>
	</tr>
</table>
</div>
<?
else: 
	CAdminMessage::ShowMessage(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_GRAPH"));
	//ShowError(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_GRAPH"));
endif;
?>

<h2><?=GetMessage("SUP_DIAGRAM_TIME_TITLE")?></h2>
<?
if (!function_exists("ImageCreate")) :
	CAdminMessage::ShowMessage(GetMessage("SUP_GD_NOT_INSTALLED"));
	//ShowError(GetMessage("SUP_GD_NOT_INSTALLED")."<br>");
elseif ($CLOSE_TICKETS<=0) :
	CAdminMessage::ShowMessage(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_DIAGRAM"));
	//ShowError(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_DIAGRAM"));
else :
	$diameter = 180;
?>
<div class="graph">
<table cellspacing=0 cellpadding=10 class="graph">
	<tr>
		<td valign="top"><img src="/bitrix/admin/ticket_diagram_time.php?<?=GetFilterParams($FilterArr1)?>&diameter=<?=$diameter?>&lang=<?=LANG?>" width="<?=$diameter?>" height="<?=$diameter?>"></td>
		<td valign="top">
			<table cellpadding=2 cellspacing=0 border=0 class="legend">
				<?
				$s = GetFilterParams($FilterArr);
				$i=0;
				foreach ($arrTime as $key => $counter):
					$i++;
					$procent = round(($counter*100)/$CLOSE_TICKETS,2);
					$color = $arrColor[$key];
					switch ($key)
					{
						case "1":
							$f = "find_ticket_time_2=1";
							break;
						case "1_2":
							$f = "find_ticket_time_1=1&find_ticket_time_2=2";
							break;
						case "2_3":
							$f = "find_ticket_time_1=2&find_ticket_time_2=3";
							break;
						case "3_4":
							$f = "find_ticket_time_1=3&find_ticket_time_2=4";
							break;
						case "4_5":
							$f = "find_ticket_time_1=4&find_ticket_time_2=5";
							break;
						case "5_6":
							$f = "find_ticket_time_1=5&find_ticket_time_2=6";
							break;
						case "6_7":
							$f = "find_ticket_time_1=6&find_ticket_time_2=7";
							break;
						case "7":
							$f = "find_ticket_time_1=7";
							break;
					}
				?>
				<tr>
					<td align="right" nowrap><?=$i."."?></td>
					<td valign="center">
						<table cellspacing=0 cellpadding=0>
							<tr>
								<td style="background-color: <?="#".$color?>"><img src="/bitrix/images/1.gif" width="12" height="12" border=0></td>
							</tr>
						</table>
					</td>
					<td align="right" nowrap><?echo sprintf("%01.2f", $procent)."%"?></td>
					<td  nowrap><a href="/bitrix/admin/ticket_list.php?<?=GetFilterParams($FilterArr1)?>&find_close=Y&lang=<?=LANG?>&<?echo $f?>&set_filter=Y"><?=$counter?></a></td>
					<td nowrap><?echo GetMessage("SUP_DIAGRAM_".$key);?></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
</table>
</div>
<?endif;?>

<h2><?=GetMessage("SUP_DIAGRAM_MESS_TITLE")?></h2>
<?
if (!function_exists("ImageCreate")) : 
	CAdminMessage::ShowMessage(GetMessage("SUP_GD_NOT_INSTALLED"));
	//ShowError(GetMessage("SUP_GD_NOT_INSTALLED")."<br>");
elseif ($CLOSE_TICKETS<=0) :
	CAdminMessage::ShowMessage(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_DIAGRAM"));
	//ShowError(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_DIAGRAM"));
else :
	$diameter = 180;
?>
<div class="graph">
<table cellspacing=0 cellpadding=10 class="graph">
	<tr>
		<td valign="top"><img src="/bitrix/admin/ticket_diagram_mess.php?<?=GetFilterParams($FilterArr1)?>&diameter=<?=$diameter?>&lang=<?=LANG?>" width="<?=$diameter?>" height="<?=$diameter?>"></td>
		<td valign="top">
			<table cellpadding=2 cellspacing=0 border=0 class="legend">
				<?
				$s = GetFilterParams($FilterArr);
				$i=0;
				foreach ($arrMess as $key => $counter):
					$i++;
					$procent = round(($counter*100)/$CLOSE_TICKETS,2);
					$color = $arrColor[$key];
					$key = intval($key);
					if ($key=="2") $f = "find_messages2=2";
					elseif ($key=="10") $f = "find_messages1=10";
					else $f = "find_messages1=".$key."&find_messages2=".$key;
				?>
				<tr>
					<td align="right" nowrap><?=$i."."?></td>
					<td valign="center">
						<table cellspacing=0 cellpadding=0>
							<tr>
								<td style="background-color: <?="#".$color?>"><img src="/bitrix/images/1.gif" width="12" height="12" border=0></td>
							</tr>
						</table>
					</td>
					<td align="right" nowrap><?echo sprintf("%01.2f", $procent)."%"?></td>
					<td  nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><a href="/bitrix/admin/ticket_list.php?<?=GetFilterParams($FilterArr1)?>&find_close=Y&lang=<?=LANG?>&<?echo $f?>&set_filter=Y"><?=$counter?></a></td>
					<td nowrap><?echo GetMessage("SUP_DIAGRAM_MESS_".$key);?></td>
				</tr>
				<?endforeach;?>
			</table>
		</td>
	</tr>
</table>
</div>
<?endif;

$lAdmin->EndCustomContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SUP_PAGE_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr>
	<td><?echo GetMessage("SUP_F_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
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
				ksort($arrSupportUser);
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
			?><br><input class="typeinput" type="text" name="find_responsible" size="47" value="<?=htmlspecialcharsbx($find_responsible)?>"><?=InputType("checkbox", "find_responsible_exact_match", "Y", $find_responsible_exact_match, false, "", "title='".GetMessage("SUP_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?><?
		else : 
			?>[<a href="/bitrix/admin/user_edit.php?ID=<?=$USER->GetID()?>"><?=$USER->GetID()?></a>] (<?=htmlspecialcharsEx($USER->GetLogin())?>) <?=htmlspecialcharsEx($USER->GetFullName())?><?
		endif;
		?></td>
</tr>



<tr> 
	<td nowrap>
		<?=GetMessage("SUP_F_SLA")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicketSLA::GetDropDown();
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_sla_id", $arr, $find_sla_id, GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr> 
	<td nowrap>
		<?=GetMessage("SUP_F_CATEGORY")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicketDictionary::GetDropDown("C");
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_category_id", $arr, $find_category_id, GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr> 
	<td nowrap>
		<?=GetMessage("SUP_F_CRITICALITY")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicketDictionary::GetDropDown("K");
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_criticality_id", $arr, $find_criticality_id, GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr> 
	<td nowrap>
		<?=GetMessage("SUP_F_STATUS")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicketDictionary::GetDropDown("S");
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_status_id", $arr, $find_status_id, GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr> 
	<td nowrap>
		<?=GetMessage("SUP_F_MARK")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
		$z = CTicketDictionary::GetDropDown("M");
		while ($zr = $z->Fetch())
		{
			$ref[] = $zr["REFERENCE"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_mark_id", $arr, $find_mark_id, GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr> 
	<td nowrap>
		<?=GetMessage("SUP_F_SOURCE")?>:</td>
	<td><?
		$ref = array(); $ref_id = array();
		$ref[] = "web"; $ref_id[] = "0";
		$z = CTicketDictionary::GetDropDown("SR");
		while ($zr = $z->Fetch())
		{
			$ref[] = "[".$zr["ID"]."] (".$zr["SID"].") ".$zr["NAME"];
			$ref_id[] = $zr["REFERENCE_ID"];
		}
		$arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
		echo SelectBoxFromArray("find_source_id", $arr, $find_source_id, GetMessage("SUP_ALL"));
		?></td>
</tr>
<tr valign="top">
	<td width="0%" nowrap><?=GetMessage("SUP_SHOW")?>:</td>
	<td width="0%" nowrap  valign="top">
		<table border="0" cellspacing="2" cellpadding="0" width="0%" style="margin-left: 12px">
			<tr>
				<td valign="top" align="center">
					<table border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td  valign="top"> 
								<table cellpadding="3" cellspacing="1" border="0">
									<tr>
										<td nowrap><?=GetMessage("SUP_OPEN_TICKET")?></td>
										<td align="center"><?echo InputType("checkbox","find_open","Y",$find_open,false); ?></td>
									</tr>
									<tr>
										<td nowrap><?=GetMessage("SUP_CLOSE_TICKET")?></td>
										<td align="center"><?echo InputType("checkbox","find_close","Y",$find_close,false); ?></td>
									</tr>
									<tr>
										<td nowrap><?=GetMessage("SUP_ALL_TICKET")?></td>
										<td align="center"><?echo InputType("checkbox","find_all","Y",$find_all,false); ?></td>
									</tr>
									<tr>
										<td nowrap><?=GetMessage("SUP_MESSAGES")?></td>
										<td align="center"><?echo InputType("checkbox","find_mess","Y",$find_mess,false); ?></td>
									</tr>
									<tr>
										<td nowrap><?=GetMessage("SUP_OVERDUE_MESSAGES")?></td>
										<td align="center"><?echo InputType("checkbox","find_overdue_mess","Y",$find_overdue_mess,false); ?></td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?$lAdmin->DisplayList();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>