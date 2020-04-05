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
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/include.php");

$isAdmin = CAdvContract::IsAdmin();
$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();

if(!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/admin/adv_stat_list.php");
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

/***************************************************************************
							Обработка GET | POST
****************************************************************************/
$strError = '';
$banner_ref = array();
$banner_ref_id = array();
$group_ref_id = array();
$group_ref = array();

$rsBanns = CAdvBanner::GetList($v1="s_dropdown", $v2="desc", array(), $v3);

while ($arBann = $rsBanns->Fetch())
{
	$banner_ref_id[] = $arBann["ID"];
	$banner_ref[] = "[".$arBann["ID"]."] ".$arBann["NAME"];
	if (!in_array($arBann["GROUP_SID"], $group_ref_id) && strlen($arBann["GROUP_SID"])>0)
	{
		$group_ref_id[] = $arBann["GROUP_SID"];
		$group_ref[] = $arBann["GROUP_SID"];
	}
	if (strlen($find_type_sid)>0)
	{
		if ($arBann["TYPE_SID"]==$find_type_sid) $find_banner_id[] = $arBann["ID"];
	}
}
if(empty($banner_ref))
	$strError = GetMessage("ADV_NO_BANNERS_FOR_GRAPHIC");

$man = false;

if ((!isset($_SESSION["SESS_ADMIN"]["AD_STAT_BANNER_GRAPH"]) || empty($_SESSION["SESS_ADMIN"]["AD_STAT_BANNER_GRAPH"])) && strlen($find_date1)<=0 && strlen($find_date2)<=0 && !is_array($find_banner_id) && strlen($find_banner_summa)<=0 && !is_array($find_what_show))
{
	$find_banner_id = $banner_ref_id;
	$find_banner_summa = "Y";
	$find_what_show = Array("ctr");
	$man = true;
	$set_filter = "Y";
}

$FilterArr = Array(
	"find_date1",
	"find_date2",
	"find_banner_id",
	"find_banner_summa",
	"find_what_show",
	"find_group_sid",
	"find_group_summa",
	"find_type_sid"
	);
	
$sTableID = "adv_banner_list";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

$lAdmin->InitFilter($FilterArr);

if (strlen($set_filter)>0 || $man) 
	InitFilterEx($FilterArr,"AD_STAT_BANNER_GRAPH","set",true); 
else 
	InitFilterEx($FilterArr,"AD_STAT_BANNER_GRAPH","get",true);
if (strlen($del_filter)>0)
	DelFilterEx($FilterArr,"AD_STAT_LIST",true);

//if((!is_set($find_banner_id) && !is_set($find_what_show)) || (!is_set($find_what_show) && is_set($find_banner_id)) || (is_set($find_what_show) && !is_set($find_banner_id)))
//	$strError = GetMessage("ADV_F_NO_FIELDS");

if (!is_array($find_banner_id) || count($find_banner_id)==0)
{
	$find_banner_id = array(0);
}

if (empty($find_banner_summa))
{
	$find_banner_summa = 'Y';
}

if (empty($find_what_show))
{
	$find_what_show = array("visitor", "show", "click", "ctr");
}

$arFilter = Array(
	"DATE_1"			=> $find_date1,
	"DATE_2"			=> $find_date2,
	"BANNER_ID"		=> $find_banner_id,
	"BANNER_SUMMA"		=> $find_banner_summa,
	"WHAT_SHOW"		=> $find_what_show,
	"GROUP_SID"		=> $find_group_sid,
	"GROUP_SUMMA"		=> $find_group_summa,
	);
	
if (count($find_banner_id) < 2)
{
	$find_banner_summa = 'Y';
}

$arrDays = CAdvBanner::GetDynamicList($arFilter, $arrLegend, $is_filtered);

$arShow = $find_what_show;
$filter_selected = 0;
if (is_array($find_banner_id) && count($find_banner_id)>0) $filter_selected++;
if (is_array($find_group_sid) && count($find_group_sid)>0) $filter_selected++;

if ($filter_selected>0) $is_filtered = true;

$arrStat = CAdvBanner::GetStatList($by, $order, $arFilter);

$rsData = new CAdminResult($arrStat, $sTableID); //var_dump($arrStat);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage('ADV_DATE_TABLE_TITLE')));

$arHeaders = array();

$arHeaders[]=
	array(	"id"	=>"DATE",
		"content"	=>GetMessage('ADV_DATE'),
		"sort"		=>"s_date",
		"align"		=>"right",
		"default"	=>true
	);
if ($find_banner_summa=="N"){
	$arHeaders[]=
		array(	"id"	=>"BANNER_ID",
			"content"	=>GetMessage('ADV_BANNER_ID'),
			"sort"		=>"s_id",
			"align"		=>"right",
			"default"	=>false
		);
	$arHeaders[]=
		array(	"id"	=>"BANNER_NAME",
			"content"	=>GetMessage('ADV_BANNER'),
			"sort"		=>false,
			"align"		=>"left",
			"default"	=>true
		);
}
$arHeaders[]=
	array(	"id"	=>"VISITORS",
		"content"	=>GetMessage('AD_VISITOR'),
		"sort"		=>"s_visitors",
		"align"		=>"right",
		"default"	=>true
	);
$arHeaders[]=
	array(	"id"	=>"CLICKS",
		"content"	=>GetMessage('AD_CLICK_GRAPH'),
		"sort"		=>"s_clicks",
		"align"		=>"right",
		"default"	=>true
	);
$arHeaders[]=
	array(	"id"	=>"CTR",
		"content"	=>GetMessage('AD_CTR'),
		"sort"		=>"s_ctr",
		"align"		=>"right",
		"default"	=>true
	);
$arHeaders[]=
	array(	"id"	=>"SHOWS",
		"content"	=>GetMessage('AD_SHOW'),
		"sort"		=>"s_show",
		"align"		=>"right",
		"default"	=>true
	);
	
$lAdmin->AddHeaders($arHeaders);
$noContent = true;//var_dump($rsData);
while($arRes = $rsData->NavNext(true, "f_"))
{
	$noContent = false;
	$row =& $lAdmin->AddRow($f_DATE, $arRes);	
	$row->AddViewField("DATE", $f_DATE_STAT);
	$row->AddViewField("VISITORS", $f_VISITOR_COUNT);
	$row->AddViewField("CTR", $f_CTR==0?'0':$f_CTR);
	$row->AddViewField("SHOWS", $f_SHOW_COUNT);
	$row->AddViewField("CLICKS", $f_CLICK_COUNT);
	if ($find_banner_summa=="N"){
		$row->AddViewField("BANNER_ID", $f_BANNER_ID);
		$row->AddViewField("BANNER_NAME", $f_BANNER_NAME);
	}	
}

$arFooter = array();
$arFooter[] = array(
	"title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"),
	"value"=>$rsData->SelectedRowsCount(),
	);
$lAdmin->AddFooter($arFooter);

/***************************************************************************
								HTML форма
****************************************************************************/

$lAdmin->BeginPrologContent();
echo CAdminMessage::ShowMessage($strError);

$width = COption::GetOptionString("advertising", "BANNER_GRAPH_WEIGHT");
$height = COption::GetOptionString("advertising", "BANNER_GRAPH_HEIGHT");

if (!function_exists("ImageCreate")) :
	echo CAdminMessage::ShowMessage(GetMessage("AD_GD_NOT_INSTALLED")."<br>");
else :
	echo BeginNote();
		echo GetMessage("AD_SERVER_TIME")."&nbsp;&nbsp;<i>".GetTime(time(),"FULL")."</i><br>";
		echo GetMessage("AD_DAYS_TO_KEEP")."&nbsp;&nbsp;<i>".COption::GetOptionString("advertising","BANNER_DAYS")."</i>";
		if ($isAdmin)
			echo "&nbsp;&nbsp;[<a href='/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=advertising' title='".GetMessage("AD_SET_EDIT")."'>".GetMessage("AD_EDIT")."</a>]";
	echo EndNote()
	?>	
	<div class="graph">
	<table border="0" cellspacing="2" cellpadding="10" class="graph">
		<tr>
			<td><img src="/bitrix/admin/adv_graph.php?<?=GetFilterParams($FilterArr)?>&lang=<?=LANGUAGE_ID?>" width="<?=$width?>" height="<?=$height?>"></td>
		</tr>
		<tr>
			<td>
			<table cellpadding="3" cellspacing="1" border="0" class="list-table">
				<?if (count($arShow)>1):?>
				<tr class="head">
					<?if (in_array("visitor", $arShow)):?>
						<td align="center" width="0%"><?=GetMessage("AD_VISITOR")?></td>
					<?endif;?>
					<?if (in_array("ctr", $arShow)):?>
						<td align="center" width="0%">CTR</td>
					<?endif;?>
					<?if (in_array("show", $arShow)):?>
						<td align="center" width="0%"><?=GetMessage("AD_SHOW")?></td>
					<?endif;?>
					<?if (in_array("click", $arShow)):?>
						<td align="center" width="0%"><?=GetMessage("AD_CLICK")?></td>
					<?endif;?>
					<td nowrap>&nbsp;</td>
				</tr>
				<?endif;?>
				<?
				reset($arrLegend);

				while(list($keyL, $arrS) = each($arrLegend)) :
				?>
				<tr valign="center">
					<?if (in_array("visitor", $arShow)):?>
						<td align="center" style="vertical-align:middle;" width="0%"><div style="background-color: <?="#".$arrS["COLOR_VISITOR"]?>"><img src="/bitrix/images/1.gif" width="45" height="2" border=0></div></td>
					<?endif;?>
					<?if (in_array("ctr", $arShow)):?>
						<td align="center" style="vertical-align:middle;" width="0%"><div style="background-color: <?="#".$arrS["COLOR_CTR"]?>"><img src="/bitrix/images/1.gif" width="45" height="2" border=0></div></td>
					<?endif;?>
					<?if (in_array("show", $arShow)):?>
						<td align="center" style="vertical-align:middle;" width="0%"><div style="background-color: <?="#".$arrS["COLOR_SHOW"]?>"><img src="/bitrix/images/1.gif" width="45" height="2" border=0></div></td>
					<?endif;?>
					<?if (in_array("click", $arShow)):?>
						<td align="center" style="vertical-align:middle;" width="0%"><div style="background-color: <?="#".$arrS["COLOR_CLICK"]?>"><img src="/bitrix/images/1.gif" width="45" height="2" border=0></div></td>
					<?endif;?>
					<?
					switch ($arrS["TYPE"]) :
						case "GROUP" :
							?>
							<td nowrap width="100%"><img src="/bitrix/images/1.gif" width="3" height="1"><?
								if ($arrS["COUNTER_TYPE"]=="DETAIL") :
									echo $arrS["ID"];
								else :
									echo GetMessage("AD_GROUP_SUM");
								endif;
								?></td>
							<?
						break;
						case "BANNER" :
							?>
							<td nowrap width="100%"><img src="/bitrix/images/1.gif" width="3" height="1"><?
								if ($arrS["COUNTER_TYPE"]=="DETAIL") :
									echo '[<a href="/bitrix/admin/adv_banner_edit.php?ID='.$arrS["ID"].'&lang='.LANGUAGE_ID.'&action=view" title="'.GetMessage("AD_BANNER_VIEW").'">'.$arrS["ID"].'</a>] '.htmlspecialcharsEx($arrS["NAME"]);
								else :
									echo GetMessage("AD_BANNER_SUM");
								endif;
								?></td>
							<?
						break;
					endswitch;
				?></tr><?
				endwhile;
				?>
			</table>
			</td>
		</tr>
	</table>
	</div>
	<? $lAdmin->EndPrologContent();
/***************************************************************************
								HTML форма
****************************************************************************/
$lAdmin->AddAdminContextMenu(array());
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("AD_BANNER_GRAPH_PAGE_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$FilterFields = Array(
		//GetMessage("AD_F_PERIOD"),
		GetMessage("AD_F_WHAT_TO_SHOW"),
	);
$FilterFields[] = GetMessage("AD_F_BANNERS");
if (count($group_ref_id)>0)
	$FilterFields[] = GetMessage("AD_F_GROUPS");

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	$FilterFields
	);
?>

<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>?">
<input type="hidden" name="lang" value="<?=htmlspecialcharsbx(LANGUAGE_ID)?>">
<?$filter->Begin();
?>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("AD_F_PERIOD")." (".CSite::GetDateFormat("SHORT")."):"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<tr>
	<td nowrap valign="top"><span class="required">*</span><?=GetMessage("AD_F_WHAT_TO_SHOW")?>:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td valign="top"><?
		$arr = array(
			"reference" => array(
				GetMessage("AD_VISITOR_GRAPH"),
				GetMessage("AD_SHOW_GRAPH"),
				GetMessage("AD_CLICK_GRAPH"),
				"CTR"),
			"reference_id" => array(
				"visitor",
				"show",
				"click",
				"ctr"));
		echo SelectBoxMFromArray("find_what_show[]",$arr, $find_what_show, "",false,"4");
		?></td>
</tr>
<tr>
	<td nowrap valign="top"><span class="required">*</span><?=GetMessage("AD_F_BANNERS")?>:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td valign="top"><?
		if (count($banner_ref_id)>1)
		{
			$arr = array("reference"=>array(GetMessage("AD_SEPARATED"), GetMessage("AD_SUMMA")), "reference_id"=>array("N","Y"));
			echo SelectBoxFromArray("find_banner_summa", $arr, htmlspecialcharsbx($find_banner_summa), "", "style='width:100%'")."<br>";
		}
		echo SelectBoxMFromArray("find_banner_id[]",array("REFERENCE"=>$banner_ref, "REFERENCE_ID"=>$banner_ref_id), $find_banner_id,"",false,"10", "style='width:100%'");
		?></td>
</tr>
<?if (count($group_ref_id)>0):?>
<tr>
	<td nowrap valign="top"><?=GetMessage("AD_F_GROUPS")?>:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td valign="top"><?
		if (count($group_ref_id)>1)
		{
			$arr = array("reference"=>array(GetMessage("AD_SEPARATED"), GetMessage("AD_SUMMA")), "reference_id"=>array("N","Y"));
			echo SelectBoxFromArray("find_group_summa", $arr, htmlspecialcharsbx($find_group_summa), "", "style='width:100%'")."<br>";
		}
		echo SelectBoxMFromArray("find_group_sid[]",array("REFERENCE"=>$group_ref, "REFERENCE_ID"=>$group_ref_id), $find_group_sid,"",false,"5", "style='width:100%'");
		?></td>
</tr>
<?endif;?>

<?
$filter->Buttons();
?>
<input type="submit" id="set_filter" name="set_filter" value="<?=GetMessage("ADV_F_FIND")?>" title="<?=GetMessage("ADV_F_FIND_TITLE")?>">
<input type="submit" name="del_filter" value="<?=GetMessage("ADV_F_CLEAR")?>" title="<?=GetMessage("ADV_F_CLEAR_TITLE")?>">
<?
$filter->End();
?>
</form>

	<?
		
		$lAdmin->DisplayList();
	?>
<?
endif;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>