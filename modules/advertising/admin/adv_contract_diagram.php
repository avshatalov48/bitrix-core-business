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
	if ($set_default=="Y" && !isset($_SESSION["SESS_ADMIN"]["AD_STAT_LIST"]) && $j<5)
	{
		$j++;
		$find_what_show = array("ctr");
		$find_contract_id[] = $arContract["ID"];
		$set_filter = "Y";
	}
}
if(empty($contract_ref))
	$strError = GetMessage("ADV_NO_CONTRACTS_FOR_DIAGRAM");

$man = false;
if ((!isset($_SESSION["SESS_ADMIN"]["AD_STAT_CONTRACT_DIAGRAM"]) || empty($_SESSION["SESS_ADMIN"]["AD_STAT_CONTRACT_DIAGRAM"])) && $find_date1 == '' && $find_date2 == '' && !is_array($find_contract_id) && !is_array($find_what_show))
{
	$find_contract_id = $contract_ref_id;
	$find_what_show = Array("ctr");
	$man = true;
}

$FilterArr = Array(
	"find_date1",
	"find_date2",
	"find_contract_id",
	"find_what_show"
	);
if ($set_filter <> '' || $man)
	InitFilterEx($FilterArr,"AD_STAT_CONTRACT_DIAGRAM","set",true);
else
	InitFilterEx($FilterArr,"AD_STAT_CONTRACT_DIAGRAM","get",true);
if ($del_filter <> '') DelFilterEx($FilterArr,"AD_STAT_LIST",true);

if((!is_set($find_contract_id) && !is_set($find_what_show)) || (!is_set($find_what_show) && is_set($find_contract_id)) || (is_set($find_what_show) && !is_set($find_contract_id)))
	$strError = GetMessage("ADV_F_NO_FIELDS");

$arFilter = Array(
	"DATE_1"			=> $find_date1,
	"DATE_2"			=> $find_date2,
	"CONTRACT_ID"		=> $find_contract_id,
	"WHAT_SHOW"			=> $find_what_show
	);

$arrDays = CAdvBanner::GetDynamicList($arFilter, $arrLegend, $is_filtered);

$arShow = $find_what_show;
$filter_selected = 0;
if (is_array($find_contract_id) && count($find_contract_id)>0) $filter_selected++;

if ($filter_selected>0) $is_filtered = true;

/***************************************************************************
								HTML форма
****************************************************************************/
$APPLICATION->SetTitle(GetMessage("AD_CONTRACT_DIAGRAM_PAGE_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$FilterFields = Array(
		//GetMessage("AD_F_PERIOD"),
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
echo CAdminMessage::ShowMessage($strError);

$diameter = intval(COption::GetOptionString("advertising", "BANNER_DIAGRAM_DIAMETER"));

if (!function_exists("ImageCreate")) :
	echo CAdminMessage::ShowMessage(GetMessage("AD_GD_NOT_INSTALLED")."<br>");
elseif (count($arrLegend)>0) :
	echo BeginNote();
		echo GetMessage("AD_SERVER_TIME")."&nbsp;&nbsp;<i>".GetTime(time(),"FULL")."</i><br>";
		echo GetMessage("AD_DAYS_TO_KEEP")."&nbsp;&nbsp;<i>".COption::GetOptionString("advertising","BANNER_DAYS")."</i>";
		if ($isAdmin)
			echo "&nbsp;&nbsp;[<a href='/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=advertising' title='".GetMessage("AD_SET_EDIT")."'>".GetMessage("AD_EDIT")."</a>]";
	echo EndNote();

	// Диаграммы по контрактам
	if ($find_contract_summa!="Y" && count($find_contract_id)>1) :

		$diagram_type = "CONTRACT";

		$sum_ctr = 0;
		$sum_show = 0;
		$sum_click = 0;
		$sum_visitor = 0;
		foreach ($arrLegend as $keyL => $arrS)
		{
			if ($arrS["COUNTER_TYPE"]=="DETAIL" && $arrS["TYPE"]==$diagram_type)
			{
				$sum_ctr += $arrS["CTR"];
				$sum_show += $arrS["SHOW"];
				$sum_click += $arrS["CLICK"];
				$sum_visitor += $arrS["VISITOR"];
			}
		}
		if ($sum_show>0 || $sum_click>0 || $sum_ctr>0 || $sum_visitor>0) :

			if (!function_exists("ImageCreate")) :
				echo CAdminMessage::ShowMessage(GetMessage("AD_GD_NOT_INSTALLED"));
			else :
				reset($arShow);
				$aTabs = Array();
				$i=0;
				foreach($arShow as $ctype)
				{
					$counter_type = mb_strtoupper($ctype);
					if (${"sum_".mb_strtolower($counter_type)}>0)
					{
						$i++;
						$aTabs[] = array("DIV"=>"ttab".$i, "TAB"=>GetMessage("AD_".$counter_type."_DIAGRAM"), "TITLE"=>GetMessage("AD_CONTRACT_DIAGRAM_TITLE"));
					}
				}

				reset($arShow);
				$viewTabContract = new CAdminViewTabControl("viewTabContract", $aTabs);
				if(count($aTabs)>0)
					$viewTabContract->Begin();
				foreach($arShow as $ctype) :
					$counter_type = mb_strtoupper($ctype);
					if ( ${"sum_".mb_strtolower($counter_type)}>0):
						?>
						<?$viewTabContract->BeginNextTab();?>
						<div class="graph">
						<table cellspacing=0 cellpadding=2 class="graph">
							<tr>
								<td valign="top"><img class="graph" src="/bitrix/admin/adv_diagram.php?<?=GetFilterParams($FilterArr)?>&diagram_type=<?echo $diagram_type?>&counter_type=<?echo $counter_type?>" width="<?echo $diameter?>" height="<?echo $diameter?>">
								</td>
								<td valign="top">
									<table cellpadding=0 cellspacing=0 border=0 class="legend">
										<?
										$i=0;
										foreach($arrLegend as $keyL => $arrS):
											if ($arrS["COUNTER_TYPE"]=="DETAIL" && $arrS["TYPE"]==$diagram_type):
											$i++;
											$counter = $arrS[$counter_type];
											if ($ctype!="ctr") $counter = intval($counter);
											$procent = round(($counter*100)/${"sum_".$ctype},2);
											$color = $arrS["COLOR"];
										?>
										<tr>
											<td align="right" nowrap><?=$i."."?></td>
											<td valign="center">
												<div style="background-color: <?="#".$color?>"><img src="/bitrix/images/1.gif" width="12" height="12" border=0></div>
											</td>
											<td align="right" nowrap><?echo sprintf("%01.2f", $procent)."%"?></td>
											<td nowrap>(<?=$counter?>)</td>
											<td  nowrap><?echo "[<a href='/bitrix/admin/adv_contract_edit.php?find_id=".$arrS["ID"]."&lang=".LANGUAGE_ID."&action=view' title='".GetMessage("AD_CONTRACT_VIEW")."'>".$arrS["ID"]."</a>] ".$arrS["NAME"];?></td>
										</tr>
										<?
											endif;
										endforeach;
										?>
									</table>
								</td>
							</tr>
						</table></div>
						<?
					endif;
				endforeach;
				$viewTabContract->End();
			endif;
		endif;
	else:
		echo CAdminMessage::ShowMessage(GetMessage("ADV_NO_DATA_DIAGRAM"));
	endif;
endif;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
