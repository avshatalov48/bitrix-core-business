<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/prolog.php");
Loader::includeModule('advertising');

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
$rsContracts = CAdvContract::GetList("s_sort", "desc");
$contract_ref_id = array();
$contract_ref = array();
$j=0;
while ($arContract = $rsContracts->Fetch())
{
	$contract_ref_id[] = $arContract["ID"];
	$contract_ref[] = "[".$arContract["ID"]."] ".$arContract["NAME"];
}
if(empty($contract_ref))
	$strError = GetMessage("ADV_NO_CONTRACTS_FOR_GRAPHIC");

$FilterArr = Array(
	"find_date1",
	"find_date2",
	"find_contract_id",
	"find_contract_summa",
	"find_what_show"
	);

$sTableID = "adv_contract_list";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

$lAdmin->InitFilter($FilterArr);

$man = false;
if (!isset($_SESSION["SESS_ADMIN"]["AD_STAT_CONTRACT_GRAPH"]) || empty($_SESSION["SESS_ADMIN"]["AD_STAT_CONTRACT_GRAPH"]))
//if(strlen($find_date1)<=0 && strlen($find_date2)<=0 && !is_array($find_contract_id) && strlen($find_contract_summa)<=0 && !is_array($find_what_show))
{
	$find_contract_id = $contract_ref_id;
	$find_contract_summa = "Y";
	$find_what_show = Array("ctr");
	$man = true;
}

if ($set_filter <> '' || $man)
	InitFilterEx($FilterArr,"AD_STAT_CONTRACT_GRAPH","set",true);
else
	InitFilterEx($FilterArr,"AD_STAT_CONTRACT_GRAPH","get",true);
if ($del_filter <> '') DelFilterEx($FilterArr,"AD_STAT_LIST",true);

//if((!is_set($find_contract_id) && !is_set($find_what_show)) || (!is_set($find_what_show) && is_set($find_contract_id)) || (is_set($find_what_show) && !is_set($find_contract_id)))
//	$strError = GetMessage("ADV_F_NO_FIELDS");

if (!is_array($find_contract_id) || count($find_contract_id)==0)
{
	$find_contract_id = array(0);
}

if (empty($find_contract_summa))
{
	$find_contract_summa = 'Y';
}

if (empty($find_what_show))
{
	$find_what_show = array("visitor", "show", "click", "ctr");
}

$arFilter = Array(
	"DATE_1"			=> $find_date1,
	"DATE_2"			=> $find_date2,
	"CONTRACT_ID"		=>(is_array($find_contract_id) && count($find_contract_id)>0)?$find_contract_id:array(),
	"CONTRACT_SUMMA"	=> $find_contract_summa,
	"WHAT_SHOW"			=> $find_what_show
	);

if (count($find_contract_id) < 2)
{
	$find_contract_summa = 'Y';
}

$arrDays = CAdvBanner::GetDynamicList($arFilter, $arrLegend, $is_filtered);
$arShow = $find_what_show;
$filter_selected = 0;
if (is_array($find_contract_id) && count($find_contract_id)>0) $filter_selected++;

if ($filter_selected>0) $is_filtered = true;

$arrStat = CAdvContract::GetStatList($by, $order, $arFilter);

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
if ($find_contract_summa=="N"){
	$arHeaders[]=
		array(	"id"	=>"CONTRACT_ID",
			"content"	=>GetMessage('ADV_CONTRACT_ID'),
			"sort"		=>"s_id",
			"align"		=>"right",
			"default"	=>false
		);
	$arHeaders[]=
		array(	"id"	=>"CONTRACT_NAME",
			"content"	=>GetMessage('ADV_CONTRACT'),
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
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_DATE, $arRes);
	$row->AddViewField("DATE", $f_DATE_STAT);
	$row->AddViewField("VISITORS", $f_VISITOR_COUNT);
	$row->AddViewField("CTR", $f_CTR==0?'0':$f_CTR);
	$row->AddViewField("SHOWS", $f_SHOW_COUNT);
	if ($find_contract_summa=="N"){
		$row->AddViewField("CONTRACT_ID", $f_CONTRACT_ID);
		$row->AddViewField("CONTRACT_NAME", $f_CONTRACT_NAME);
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
	echo EndNote();?>

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
					<td align="center"><?=GetMessage("AD_VISITOR")?></td>
				<?endif;?>
				<?if (in_array("ctr", $arShow)):?>
					<td align="center">CTR</td>
				<?endif;?>
				<?if (in_array("show", $arShow)):?>
					<td align="center"><?=GetMessage("AD_SHOW")?></td>
				<?endif;?>
				<?if (in_array("click", $arShow)):?>
					<td align="center"><?=GetMessage("AD_CLICK")?></td>
				<?endif;?>
				<td nowrap>&nbsp;</td>
			</tr>
			<?endif;?>
			<?
			foreach ($arrLegend as $keyL => $arrS):
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
				if($arrS["TYPE"] == "CONTRACT")
				{
				?>
					<td nowrap width="100%"><img src="/bitrix/images/1.gif" width="3" height="1"><?
					if ($arrS["COUNTER_TYPE"]=="DETAIL") :
						echo "[<a href='/bitrix/admin/adv_contract_edit.php?ID=".$arrS["ID"]."&lang=".LANGUAGE_ID."&action=view' title='".GetMessage("AD_CONTRACT_VIEW")."'>".$arrS["ID"]."</a>] ".$arrS["NAME"];
					else :
						echo GetMessage("AD_CONTRACT_SUM");
					endif;
					?></td>
				<?
				}
			?></tr><?
			endforeach;
			?>
		</table>
		</td>
	</tr>
</table>
</div>

<?
endif;
$lAdmin->EndPrologContent();
$lAdmin->AddAdminContextMenu(array());
$lAdmin->CheckListMode();
/***************************************************************************
								HTML форма
****************************************************************************/
$APPLICATION->SetTitle(GetMessage("AD_CONTRACT_GRAPH_PAGE_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$FilterFields = Array(
		GetMessage("AD_F_WHAT_TO_SHOW"),
	);
$FilterFields[] = GetMessage("AD_F_CONTRACTS");

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	$FilterFields
	);
?>

<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<input type="hidden" name="lang" value="<?=htmlspecialcharsbx(LANGUAGE_ID)?>">
<?$filter->Begin();
?>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("AD_F_PERIOD")." (".CSite::GetDateFormat("SHORT")."):"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y")?></td>
</tr>
<tr>
	<td nowrap valign="top"><span class="required">*</span><?=GetMessage("AD_F_WHAT_TO_SHOW")?>:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td><?
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
	<td nowrap valign="top"><span class="required">*</span><?=GetMessage("AD_F_CONTRACTS")?>:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td nowrap><?
		if (count($contract_ref_id)>1)
		{
			$arr = array("reference"=>array(GetMessage("AD_SEPARATED"), GetMessage("AD_SUMMA")), "reference_id"=>array("N","Y"));
			echo SelectBoxFromArray("find_contract_summa", $arr, htmlspecialcharsbx($find_contract_summa), "", "style='width:100%'")."<br>";
		}
		echo SelectBoxMFromArray("find_contract_id[]",array("REFERENCE"=>$contract_ref, "REFERENCE_ID"=>$contract_ref_id), $find_contract_id,"",false,"5", "style='width:100%'");
		?></td>
</tr>
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
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>