<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
$statDB = CDatabase::GetModuleConnection('statistic');
$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","adv_list.php");

/***************************************************************************
				GET | POST handlers
****************************************************************************/
$ADV_ID = intval($ADV_ID);

if($context=="tab")
	$find_events=array();
$rs = CAdv::GetEventList($ADV_ID, ($by="s_def"),($order="desc"), array(), $v3);
while ($ar = $rs->Fetch())
{
	$arrEVENTS[$ar["ID"]] = $ar["EVENT"]." [".$ar["ID"]."]";
	if($context=="tab")
		$find_events[]=$ar["ID"];
}
$sTableID = "t_adv_graph_list";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

if (strlen($DATE1)>0) $find_date1 = $DATE1;
if (strlen($DATE2)>0) $find_date2 = $DATE2;

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	Array(
		GetMessage("STAT_F_SELECT_EVENTS"),
	)
);

if($lAdmin->IsDefaultFilter())
{
	if (is_array($arrEVENTS))
	{
		reset($arrEVENTS);
		while (list($key,$value)=each($arrEVENTS))
		{
			$i++;
			if ($i<=5) $find_events[] = $key;
		}
	}
	$find_date1_DAYS_TO_BACK=90;
	$set_filter = "Y";
}

$arShow= array(
	"find_sessions",
	"find_sessions_back",
	"find_guests",
	"find_guests_back",
	"find_new_guests",
	"find_hosts",
	"find_hosts_back",
	"find_hits",
	"find_hits_back",
	"find_show_money"
	);

$FilterArr = array(

	"find_date1",
	"find_date2",
	"find_events",
);

$lAdmin->InitFilter($FilterArr);

if ($set_show != "Y")
{
	$find_sessions="Y";
	$find_sessions_back="Y";
	$find_guests="Y";
	$find_guests_back="Y";
	$find_new_guests="Y";
	$find_hosts="Y";
	$find_hosts_back="Y";
}

if(is_array($find_events))
{
	$find_events_names = $find_events_tmp = array();

	foreach($find_events as $key => $value)
	{
		if (is_set($arrEVENTS, $value))
		{
			$find_events_names[]=$arrEVENTS[$value];
			$find_events_tmp[] = $value;
		}
		else
		{
			unset($find_events[$key]);
		}
	}
	$find_events = $find_events_tmp;
}
else
{
	$find_events =array();
	$find_events_names = array();
}

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

$arFilter = Array(
	"DATE1" => $find_date1,
	"DATE2" => $find_date2,
);

$strSql = "SELECT ID, REFERER1, REFERER2 FROM b_stat_adv WHERE ID = ".$ADV_ID;
$a = $statDB->Query($strSql,false,$err_mess.__LINE__);
if (!$ar = $a->Fetch())
{
	$message = new CAdminMessage(Array("MESSAGE" => GetMessage("STAT_INCORRECT_ADV_ID"), "TYPE"=>"ERROR"));
}
else
{
	$message = null;
	$ref1 = $ar["REFERER1"];
	$ref2 = $ar["REFERER2"];
}

$lAdmin->BeginCustomContent();

$dynamic_days = CAdv::DynamicDays($ADV_ID, $arFilter["DATE1"], $arFilter["DATE2"]);
if ($dynamic_days<2)
{
	CAdminMessage::ShowMessage(GetMessage("STAT_NOT_ENOUGH_DATA"));
}
elseif (!$message)
{
	$width = COption::GetOptionString("statistic", "GRAPH_WEIGHT");
	$height = COption::GetOptionString("statistic", "GRAPH_HEIGHT");
	$str = "ADV_ID=".$ADV_ID."&find_date1=".urlencode($find_date1)."&find_date2=".urlencode($find_date2);
	$s = "";
	if ($find_sessions=="Y")
		$s .= "&find_sessions=Y";
	if ($find_sessions_back=="Y")
		$s .= "&find_sessions_back=Y";
	if ($find_guests=="Y")
		$s .= "&find_guests=Y";
	if ($find_guests_back=="Y")
		$s .= "&find_guests_back=Y";
	if ($find_new_guests=="Y")
		$s .= "&find_new_guests=Y";
	if ($find_hosts=="Y")
		$s .= "&find_hosts=Y";
	if ($find_hosts_back=="Y")
		$s .= "&find_hosts_back=Y";
	if ($find_hits=="Y")
		$s .= "&find_hits=Y";
	if ($find_hits_back=="Y")
		$s .= "&find_hits_back=Y";

	if (strlen($s)>0)
	{
		$graph_1 = "Y";
		$str .= $s;
		?>
		<?if($context=="tab"):?>
		<a href="/bitrix/admin/adv_dynamic_list.php?lang=<?=LANG?>&amp;find_adv_id=<?=$ADV_ID?>&amp;set_default=Y"><?=GetMessage("STAT_ALL_DYNAMICS")?></a><br>
		<a href="/bitrix/admin/adv_graph_list.php?lang=<?=LANG?>&amp;ADV_ID=<?=$ADV_ID?>"><?=GetMessage("STAT_ALL_GRAPHICS")?></a><br>
		<?endif;?>
		<div class="graph">
		<?=GetMessage("STAT_GRAPH_1")?>
		<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center"><tr>
			<td valign="top" class="graph">
				<img class="graph" src="adv_graph_1.php?rand=<?=rand()?>&amp;<?=$str?>&amp;width=<?=$width?>&amp;height=<?=$height?>&amp;lang=<?=LANGUAGE_ID?>" width="<?=$width?>" height="<?=$height?>">
			</td>
			<td valign="center">
				<table cellpadding="3" cellspacing="1" border="0" class="legend">
					<tr>
						<td align="center"><?=GetMessage("STAT_STRAIGHT")?></td>
						<td align="center"><?=GetMessage("STAT_BACK")?><font class="star">*</td>
						<td>&nbsp;</td>
					</tr>
					<?if ($find_hits=="Y" || $find_hits_back=="Y"):?>
					<tr>
						<td valign="center" class="color-line">
							<div style="background-color: <?="#".$arrColor["HITS"]?>"></div>
						</td>
						<td><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["HITS"]?>&dash=Y" width="45" height="2"></td>
						<td  nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_HITS")?></td>
					</tr>
					<?endif;?>
					<?if ($find_sessions=="Y" || $find_sessions_back=="Y"):?>
					<tr>
						<td valign="center" class="color-line">
							<div style="background-color: <?="#".$arrColor["SESSIONS"]?>"></div>
						</td>
						<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["SESSIONS"]?>&dash=Y" width="45" height="2"></td>
						<td  nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_SESSIONS")?></td>
					</tr>
					<?endif;?>
					<?if ($find_guests=="Y" || $find_guests_back=="Y"):?>
					<tr>
						<td valign="center" class="color-line">
							<div style="background-color: <?="#".$arrColor["GUESTS"]?>"></div>
						</td>
						<td><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["GUESTS"]?>&dash=Y" width="45" height="2"></td>
						<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_GUESTS")?></td>
					</tr>
					<?endif;?>
					<?if ($find_hosts=="Y" || $find_hosts_back=="Y"):?>
					<tr>
						<td valign="center" class="color-line">
							<div style="background-color: <?="#".$arrColor["HOSTS"]?>"></div>
						</td>
						<td><img src="/bitrix/admin/graph_legend.php?color=<?=$arrColor["HOSTS"]?>&dash=Y" width="45" height="2"></td>
						<td  nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_HOSTS")?></td>
					</tr>
					<?endif;?>
					<?if ($find_new_guests=="Y"):?>
					<tr>
						<td valign="center" class="color-line">
							<div style="background-color: <?="#".$arrColor["NEW_GUESTS"]?>"></div>
						</td>
						<td></td>
						<td  nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("STAT_NEW_GUESTS")?></td>
					</tr>
					<?endif;?>
				</table>
			</td>
		</tr></table>
		</div>
		<?
	}

	if (sizeof($find_events)>0)
	{
		$arF["ID"] = implode(" | ",$find_events);
		$arF["DATE1_PERIOD"] = $arFilter["DATE1"];
		$arF["DATE2_PERIOD"] = $arFilter["DATE2"];
		$events = CAdv::GetEventList($ADV_ID, $by, $order, $arF, $is_filtered);

		if ($er = $events->Fetch())
		{
			$graph_2 = "Y";
			$str = "ADV_ID=".$ADV_ID."&find_date1=".urlencode($find_date1)."&find_date2=".urlencode($find_date2). "&find_show_money=".$find_show_money;
			$s = "";
			foreach ($find_events as $eid)
				$s .= "&find_events[]=".$eid;
			$str .= $s;
			?>
			<div class="graph">
			<?=GetMessage("STAT_GRAPH_2")?>
			<table cellspacing="0" cellpadding="0" class="graph" border="0" align="center"><tr>
				<td valign="top" class="graph">
					<img class="graph" src="adv_graph_2.php?rand=<?=rand()?>&amp;<?=$str?>&amp;width=<?=$width?>&amp;height=<?=$height?>&amp;lang=<?=LANG?>" width="<?=$width?>" height="<?=$height?>">
				</td>
				<td valign="center">
					<table cellpadding="3" cellspacing="1" border="0" class="legend">
						<tr>
							<td align="center"><?=GetMessage("STAT_STRAIGHT")?></td>
							<td align="center"><?=GetMessage("STAT_BACK")?>*</td>
							<td>&nbsp;</td>
						</tr>
						<?
						reset($find_events);
						$total = sizeof($find_events);
						foreach ($find_events as $eid)
						{
							$color = GetNextRGB($color, $total);
						?>
						<tr>
							<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$color?>" width="45" height="2"></td>
							<td valign="center"><img src="/bitrix/admin/graph_legend.php?color=<?=$color?>&dash=Y" width="45" height="2"></td>
							<td  nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?
							$events = CStatEventType::GetByID($eid);
							$arEvent = $events->GetNext();
							echo "[<a href=\"/bitrix/admin/event_type_list.php?lang=".LANG."\">".$arEvent["ID"]."</a>] ".$arEvent["EVENT"];
							?></td>
						</tr>
						<?}?>
					</table>
				</td>
			</tr></table>
			</div>
			<?
		}
	}
}

$lAdmin->EndCustomContent();

$aContext = array(
	array(
		"TEXT" => GetMessage("STAT_ADV_LIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/adv_list.php?lang=".LANG,
	),
);

if (!$message)
{
	$aContext[] = array(
		"TEXT" => str_replace("#ID#",$ADV_ID,GetMessage("STAT_DYNAMIC")),
		//"ICON" => "btn_list",
		"LINK" =>"/bitrix/admin/adv_dynamic_list.php?lang=".LANG."&find_adv_id=".$ADV_ID."&find_date1=".urlencode($arFilter["DATE1"])."&find_date2=".urlencode($arFilter["DATE2"])."&set_filter=Y",
	);
}

if($context<>"tab")
	$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$strTitle = str_replace("#ID#",$ar["ID"],GetMessage("STAT_RECORDS_LIST"));
$APPLICATION->SetTitle($strTitle." (".$ref1." / ".$ref2.")");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?$filter->Begin();?>
<tr valign="center">
	<td  width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?>
	</td>
</tr>

<tr valign="top">
	<td><?=GetMessage("STAT_F_SELECT_EVENTS")?>:</td>
	<td>
	<?
		echo SelectBoxMFromArray("find_events[]",array("REFERENCE"=>$find_events_names, "REFERENCE_ID"=>$find_events), $find_events,"",false,"10", "style=\"width:300px;\"");
	?>
	<script language="Javascript">
	function selectEventType(form, field)
	{
		jsUtils.OpenWindow('event_multiselect.php?lang=<?=LANG?>&form='+form+'&field='+field, 600, 600);
	}
	jsSelectUtils.sortSelect('find_events[]');
	jsSelectUtils.selectAllOptions('find_events[]');
	</script>
	<br>
	<input type="button" OnClick="selectEventType('find_form','find_events[]')" value="<?=GetMessage("MAIN_ADMIN_MENU_ADD")?>...">&nbsp;
	<input type="button" OnClick="jsSelectUtils.deleteSelectedOptions('find_events[]');" value="<?=GetMessage("MAIN_ADMIN_MENU_DELETE")?>">


	</td>
</tr>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage()."?ADV_ID=".$ADV_ID, "form"=>"form1"));$filter->End();?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?if (!$message):?>
<form method="get" action="<?=$APPLICATION->GetCurPage();?>">

<input type="hidden" name="find_date1" value="<?=htmlspecialcharsbx($find_date1)?>">
<input type="hidden" name="find_date2" value="<?=htmlspecialcharsbx($find_date2)?>">
<input type="hidden" name="ADV_ID" value="<?=$ADV_ID?>">
<input type="hidden" name="set_show" value="Y">
<?foreach($find_events as $val):?>
<input type="hidden" name="find_events[]" value="<?=htmlspecialcharsbx($val)?>">
<?endforeach?>
<div class="graph">
<table class="graph"><tr><td>
<table cellpadding="3" cellspacing="1" border="0" class="legend">
	<tr>
		<td><?echo GetMessage("STAT_SHOW")?></td>
		<td align="center"><?echo GetMessage("STAT_STRAIGHT")?></td>
		<td align="center"><?echo GetMessage("STAT_BACK")?>*</td>
	</tr>
	<tr>
		<td nowrap><?=GetMessage("STAT_HITS")?></td>
		<td align="center"><?echo InputType("checkbox","find_hits","Y",$find_hits,false); ?></td>
		<td align="center"><?echo InputType("checkbox","find_hits_back","Y",$find_hits_back,false); ?></td>
	</tr>
	<tr>
		<td nowrap><?=GetMessage("STAT_SESSIONS")?></td>
		<td align="center"><?echo InputType("checkbox","find_sessions","Y",$find_sessions,false); ?></td>
		<td align="center"><?echo InputType("checkbox","find_sessions_back","Y",$find_sessions_back,false); ?></td>
	</tr>
	<tr>
		<td nowrap><?=GetMessage("STAT_GUESTS")?></td>
		<td align="center"><?echo InputType("checkbox","find_guests","Y",$find_guests,false); ?></td>
		<td align="center"><?echo InputType("checkbox","find_guests_back","Y",$find_guests_back,false); ?></td>
	</tr>
	<tr>
		<td nowrap><?=GetMessage("STAT_HOSTS")?></td>
		<td align="center"><?echo InputType("checkbox","find_hosts","Y",$find_hosts,false); ?></td>
		<td align="center"><?echo InputType("checkbox","find_hosts_back","Y",$find_hosts_back,false); ?></td>
	</tr>
	<tr>
		<td nowrap><?=GetMessage("STAT_NEW_GUESTS")?></td>
		<td align="center"><?echo InputType("checkbox","find_new_guests","Y",$find_new_guests,false); ?></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="3"><input type="submit" value="<?=GetMessage("STAT_F_SET_PERIOD");?>"></td>
	</tr>
</table>
</td></tr></table>
</div>
</form>
<?endif?>

<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
