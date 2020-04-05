<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");

IncludeModuleLangFile(__FILE__);

$arrParams = array(
	"hour" => array(
		GetMessage("STAT_PAGES_HOURS"),
		GetMessage("STAT_GRAPH_BY_HOURS"),
		GetMessage("STAT_TRAFFIC_HOUR_GRAPH_TITLE"),
		GetMessage("STAT_TRAFFIC_HOUR_TABLE_TITLE"),
	),
	"date" => array(
		GetMessage("STAT_PAGES_DATES"),
		GetMessage("STAT_GRAPH_BY_DATES"),
		GetMessage("STAT_TRAFFIC_DATE_GRAPH_TITLE"),
		GetMessage("STAT_TRAFFIC_DATE_TABLE_TITLE"),
	),
	"weekday" => array(
		GetMessage("STAT_PAGES_WEEKDAYS"),
		GetMessage("STAT_GRAPH_BY_WEEKDAYS"),
		GetMessage("STAT_TRAFFIC_WEEKDAY_GRAPH_TITLE"),
		GetMessage("STAT_TRAFFIC_WEEKDAY_TABLE_TITLE"),
	),
	"month" => array(
		GetMessage("STAT_PAGES_MONTHS"),
		GetMessage("STAT_GRAPH_BY_MONTHS"),
		GetMessage("STAT_TRAFFIC_MONTH_GRAPH_TITLE"),
		GetMessage("STAT_TRAFFIC_MONTH_TABLE_TITLE"),
	),
);

if(isset($graph_type))
{
	if($graph_type!="date" && $graph_type!="hour" && $graph_type!="weekday" && $graph_type!="month")
		$graph_type="date";
	$saved_graph_type = $graph_type;
}
else
{
	$graph_type=false;//no setting (will be read later from session)
}

$sTableID = "tbl_traf_list";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

$ref = $ref_id = array();
$rs = CSite::GetList(($v1="sort"), ($v2="asc"));
while ($ar = $rs->Fetch())
{
	$ref[] = "[".$ar["ID"]."] ".$ar["NAME"];
	$ref_id[] = $ar["ID"];
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

if($lAdmin->IsDefaultFilter())
{
	$find_date1_DAYS_TO_BACK=90;
	$find_date2 = ConvertTimeStamp(time()-86400, "SHORT");
	$find_host = "Y";
	$find_session = "Y";
	$find_event = "Y";
	$find_guest = "Y";
	$find_new_guest = "Y";
	$set_filter = "Y";
}

$FilterArr1 = array(
	"find_hit",
	"find_host",
	"find_session",
	"find_event",
	"find_guest",
	"find_new_guest",
);
$FilterArr2 = array(
	"find_site_id",
	"find_date1",
	"find_date2",
);

$FilterArr = array_merge($FilterArr1, $FilterArr2);

$lAdmin->InitFilter($FilterArr);

//Restore & Save settings (windows registry like)
$arSettings = array ("saved_graph_type");
InitFilterEx($arSettings, $sTableID."_settings", "get");
if($graph_type===false)//Restore saved setting
	$graph_type=$saved_graph_type;
if($graph_type!="date" && $graph_type!="hour" && $graph_type!="weekday" && $graph_type!="month")
	$graph_type="date";
if($saved_graph_type!=$graph_type)//Set if changed
	$saved_graph_type=$graph_type;
InitFilterEx($arSettings, $sTableID."_settings", "set");

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SITE_ID"	=> $find_site_id,
);

if(is_array($find_site_id))
{
	$site_filtered = count($find_site_id) > 0;
}
else
{
	$site_filtered = (strlen($find_site_id) > 0 && $find_site_id != "NOT_REF");
}

$lAdmin->BeginPrologContent();
?>
<?
/***************************************************************************
			HTML form
****************************************************************************/
$days = CTraffic::DynamicDays($arFilter["DATE1"], $arFilter["DATE2"], $arFilter["SITE_ID"]);
//echo "1".$days."1";
if ($days<2) :
	CAdminMessage::ShowMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));

elseif (!function_exists("ImageCreate")) :
	CAdminMessage::ShowMessage(GetMessage("STAT_GD_NOT_INSTALLED"));
elseif (count($lAdmin->arFilterErrors)==0) :
		$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
		$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
	?>
<div class="graph">
<?echo $arrParams[$graph_type][2]?>
<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center">
<tr>
	<td valign="center" class="graph">
		<img class="graph" src="/bitrix/admin/traffic_graph.php?<?=GetFilterParams($FilterArr1)?>&<?=GetFilterParams($FilterArr2)?>&width=<?=$width?>&height=<?=$height?>&lang=<?=LANG?>&rand=<?=rand()?>&find_graph_type=<?=$graph_type?>" width="<?=$width?>" height="<?=$height?>">
	</td>
	<td valign="center">
	<table cellpadding="2" cellspacing="0" border="0" class="legend">
		<?if ($find_hit=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["HITS"]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_HITS_2")?></td>
		</tr>
		<?endif;?>
		<?if ($find_host=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["HOSTS"]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_HOSTS_2")?></td>
		</tr>
		<?endif;?>
		<?if ($find_session=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["SESSIONS"]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_SESSIONS_2")?></td>
		</tr>
		<?endif;?>
		<?if ($find_event=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["EVENTS"]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_EVENTS_2")?></td>
		</tr>
		<?endif;?>
		<?if ($find_guest=="Y" && !$site_filtered):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["GUESTS"]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_GUESTS_2")?></td>
		</tr>
		<?endif;?>
		<?if ($find_new_guest=="Y" && !$site_filtered):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["NEW_GUESTS"]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_NEW_GUESTS_2")?></td>
		</tr>
		<?endif;?>
	</table>
	</td>
</tr>
</table>
</div>
<?endif;?>
<h2><?echo $arrParams[$graph_type][3]?></h2>
<?
$lAdmin->EndPrologContent();

if($graph_type=="date")
{
	$rsData = CTraffic::GetDailyList($by, $order, $arMaxMin, $arFilter, $is_filtered);
}
else
{
	$temp_graph_type = $graph_type;
	$rs = CTraffic::GetSumList($temp_graph_type, $arFilter);
	$ar = $rs->Fetch();
	switch ($graph_type)
	{
		case "hour":
			$start = 0; $end = 23; break;
		case "weekday":
			$start = 0; $end = 6; break;
		case "month":
			$start = 1; $end = 12; break;
	}
	$graph_type_upper = ToUpper($graph_type);
	$ra=array();
	for ($i=$start; $i<=$end; $i++)
	{
		$ra[] = array (
			"ID"=>$i,
			"HITS"=>$ar[$graph_type_upper."_HIT_".$i],
			"C_HOSTS"=>$ar[$graph_type_upper."_HOST_".$i],
			"SESSIONS"=>$ar[$graph_type_upper."_SESSION_".$i],
			"C_EVENTS"=>$ar[$graph_type_upper."_EVENT_".$i],
			"GUESTS"=>$ar[$graph_type_upper."_GUEST_".$i],
			"NEW_GUESTS"=>$ar[$graph_type_upper."_NEW_GUEST_".$i],
		);
	}

	$rsData = new CDBResult;
	$rsData->InitFromArray($ra);
}

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint($arrParams[$graph_type][0]));

$arHeaders = array();
switch($graph_type)
{
	case "date":
		$arHeaders[]=
			array(	"id"		=>"ID",
				"content"	=>"ID",
				"sort"		=>"s_id",
				"align"		=>"right",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"DATE_STAT",
				"content"	=>GetMessage("STAT_DATE"),
				"sort"		=>"s_date",
				"default"	=>true,
			);
		$arHeaders[]=
			array(	"id"		=>"WDAY",
				"content"	=>GetMessage("STAT_WEEKDAY"),
				"sort"		=>false,
				"default"	=>true,
			);
		break;
	case "weekday":
		$arHeaders[]=
			array(	"id"		=>"ID",
				"content"	=>GetMessage("STAT_WEEKDAY"),
				"sort"		=>false,
				"default"	=>true,
			);
		break;
	case "hour":
		$arHeaders[]=
			array(	"id"		=>"ID",
				"content"	=>GetMessage("STAT_HOUR"),
				"sort"		=>false,
				"align"		=>"right",
				"default"	=>true,
			);
		break;
	case "month":
		$arHeaders[]=
			array(	"id"		=>"ID",
				"content"	=>GetMessage("STAT_MONTH"),
				"sort"		=>false,
				"align"		=>"right",
				"default"	=>true,
			);
		break;
}

$arHeaders[]=
	array(	"id"		=>"HITS",
		"content"	=>GetMessage("STAT_HITS"),
		"sort"		=>"s_hits",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"C_HOSTS",
		"content"	=>GetMessage("STAT_HOSTS"),
		"sort"		=>"s_hosts",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"SESSIONS",
		"content"	=>GetMessage("STAT_SESSIONS"),
		"sort"		=>"s_sessions",
		"align"		=>"right",
		"default"	=>true,
	);
$arHeaders[]=
	array(	"id"		=>"C_EVENTS",
		"content"	=>GetMessage("STAT_EVENTS"),
		"sort"		=>"s_events",
		"align"		=>"right",
		"default"	=>true,
	);
if(!$site_filtered):
	$arHeaders[]=
		array(	"id"		=>"GUESTS",
			"content"	=>GetMessage("STAT_GUESTS"),
			"sort"		=>"s_guests",
			"align"		=>"right",
			"default"	=>true,
		);
	$arHeaders[]=
		array(	"id"		=>"NEW_GUESTS",
			"content"	=>GetMessage("STAT_NEW_GUESTS_S"),
			"sort"		=>"s_new_guests",
			"align"		=>"right",
			"default"	=>true,
		);
endif;
$lAdmin->AddHeaders($arHeaders);


while($arRes = $rsData->NavNext(true, "f_")):
	if ($f_WDAY==6) $f_WDAY = 0; else $f_WDAY++;
	$row =& $lAdmin->AddRow($f_ID, $arRes);
	switch($graph_type)
	{
		case "date":
		case "hour":
		case "month":
			$strHTML=$f_ID;
			break;
		case "weekday":
			if($f_ID==0)
				$strHTML='<span class="required">'.GetMessage("STAT_WEEKDAY_".$f_ID."_S").'</span>';
			else
				$strHTML=GetMessage("STAT_WEEKDAY_".$f_ID."_S");
			break;
	}
	$row->AddViewField("ID",$strHTML);

	if($f_WDAY==0)
		$strHTML='<span class="required">'.GetMessage("STAT_WEEKDAY_".$f_WDAY."_S").'</span>';
	else
		$strHTML=GetMessage("STAT_WEEKDAY_".$f_WDAY."_S");
	$row->AddViewField("WDAY",$strHTML);

	if($graph_type=="date")
		$strHTML='<a href="hit_list.php?lang='.LANG.'&amp;find_date1='.$f_DATE_STAT.'&amp;find_date2='.$f_DATE_STAT.'&amp;set_filter=Y">'.$f_HITS.'</a>';
	else
		$strHTML=$f_HITS;
	$row->AddViewField("HITS",$strHTML);

	if($graph_type=="date")
		$strHTML='<a href="session_list.php?lang='.LANG.'&amp;find_date1='.$f_DATE_STAT.'&amp;find_date2='.$f_DATE_STAT.'&amp;set_filter=Y">'.$f_SESSIONS.'</a>';
	else
		$strHTML=$f_SESSIONS;
	$row->AddViewField("SESSIONS",$strHTML);

	if($graph_type=="date")
		$strHTML='<a href="event_list.php?lang='.LANG.'&amp;find_date1='.$f_DATE_STAT.'&amp;find_date2='.$f_DATE_STAT.'&amp;set_filter=Y">'.$f_C_EVENTS.'</a>';
	else
		$strHTML=$f_C_EVENTS;
	$row->AddViewField("C_EVENTS",$strHTML);

	if (!$site_filtered)
	{
		if($graph_type=="date")
			$strHTML='<a href="guest_list.php?lang='.LANG.'&amp;find_period_date1='.$f_DATE_STAT.'&amp;find_period_date2='.$f_DATE_STAT.'&amp;set_filter=Y">'.$f_GUESTS.'</a>';
		else
			$strHTML=$f_GUESTS;
		$row->AddViewField("GUESTS",$strHTML);

		if($graph_type=="date")
			$strHTML='<a href="guest_list.php?lang='.LANG.'&amp;find_period_date1='.$f_DATE_STAT.'&amp;find_period_date2='.$f_DATE_STAT.'&amp;find_sess2=1&amp;set_filter=Y">'.$f_NEW_GUESTS.'</a>';
		else
			$strHTML=$f_NEW_GUESTS;
		$row->AddViewField("NEW_GUESTS",$strHTML);
	}

endwhile;

$arFooter = array();
$arFooter[] = array(
	"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
	"value"=>$rsData->SelectedRowsCount(),
	);
$lAdmin->AddFooter($arFooter);

$aContext = array(
		array(
			"TEXT"=>GetMessage("STAT_GROUPED")." ".$arrParams[$graph_type][1],
			"MENU"=>array(
				array(
					"TEXT"=>GetMessage("STAT_GROUP_BY")." ".$arrParams["date"][1],
					"ACTION"=>$lAdmin->ActionDoGroup(0, "", "graph_type=date"),
					"ICON"=>($graph_type=="date"?"checked":""),
				),
				array(
					"TEXT"=>GetMessage("STAT_GROUP_BY")." ".$arrParams["hour"][1],
					"ACTION"=>$lAdmin->ActionDoGroup(0, "", "graph_type=hour"),
					"ICON"=>($graph_type=="hour"?"checked":""),
				),
				array(
					"TEXT"=>GetMessage("STAT_GROUP_BY")." ".$arrParams["weekday"][1],
					"ACTION"=>$lAdmin->ActionDoGroup(0, "", "graph_type=weekday"),
					"ICON"=>($graph_type=="weekday"?"checked":""),
				),
				array(
					"TEXT"=>GetMessage("STAT_GROUP_BY")." ".$arrParams["month"][1],
					"ACTION"=>$lAdmin->ActionDoGroup(0, "", "graph_type=month"),
					"ICON"=>($graph_type=="month"?"checked":""),
				),
			),
		),
	);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_PAGE_TITLE"));

/***************************************************************************
			HTML form
****************************************************************************/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sTableID."_filter",array(
	GetMessage("STAT_SITE"),
	GetMessage("STAT_SHOW"),
));
?>

<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter->Begin();
?>
<tr>
	<td><?echo GetMessage("STAT_PERIOD")." (".FORMAT_DATE."):"?></td>
	<td><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "find_form", "Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_SITE")?>:</td>
	<td><?echo SelectBoxMFromArray("find_site_id[]", $arSiteDropdown, $find_site_id, "", "");?></td>
</tr>
<tr valign="top">
	<td><?=GetMessage("STAT_SHOW")?>:</td>
	<td>
		<?echo InputType("checkbox","find_hit","Y",$find_hit,false,false,'id="find_hit"');?>
		<label for="find_hit"><?=GetMessage("STAT_HITS_2")?></label><br>
		<?echo InputType("checkbox","find_host","Y",$find_host,false,false,'id="find_host"'); ?>
		<label for="find_host"><?=GetMessage("STAT_HOSTS_2")?></label><br>
		<?echo InputType("checkbox","find_session","Y",$find_session,false,false,'id="find_session"'); ?>
		<label for="find_session"><?=GetMessage("STAT_SESSIONS_2")?></label><br>
		<?echo InputType("checkbox","find_event","Y",$find_event,false,false,'id="find_event"'); ?>
		<label for="find_event"><?=GetMessage("STAT_EVENTS_2")?></label><br>
		<?echo InputType("checkbox","find_guest","Y",$find_guest,false,false,'id="find_guest"'); ?>
		<label for="find_guest"><?=GetMessage("STAT_GUESTS_2")?></label><br>
		<?echo InputType("checkbox","find_new_guest","Y",$find_new_guest,false,false,'id="find_new_guest"'); ?>
		<label for="find_new_guest"><?=GetMessage("STAT_NEW_GUESTS_2")?></label><br>
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form" => "find_form", "report"=>true));
$oFilter->End();
?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
