<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$arSites = array();
$ref = $ref_id = array();
$rs = CSite::GetList();
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$rs = CAdv::GetList();
while ($ar = $rs->Fetch())
{
	$arrADV[$ar["ID"]] = $ar["REFERER1"]." / ".$ar["REFERER2"]." [".$ar["ID"]."]";
	$arrADV_DETAIL[$ar["ID"]] = array("REFERER1" => $ar["REFERER1"], "REFERER2" => $ar["REFERER2"]);
}

if($find_referer1 <> '')
{
	$find_adv=array();
	foreach($arrADV_DETAIL as $ADV_ID=>$ADV_DETAIL)
		if($ADV_DETAIL["REFERER1"]==$find_referer1 && !in_array($ADV_ID, $find_adv))
			$find_adv[]=$ADV_ID;
}
if($find_referer2 <> '')
{
	$find_adv=array();
	foreach($arrADV_DETAIL as $ADV_ID=>$ADV_DETAIL)
		if($ADV_DETAIL["REFERER2"]==$find_referer2 && !in_array($ADV_ID, $find_adv))
			$find_adv[]=$ADV_ID;
}

if(isset($find_diagram_type))
{
	if($find_diagram_type!="EXIT_COUNTER" && $find_diagram_type!="ENTER_COUNTER")
		$find_diagram_type="COUNTER";
}
else
{
	$find_diagram_type=false;
}
//Restore & Save settings (windows registry like)
$arSettings = array("saved_group_by");
InitFilterEx($arSettings, $sTableID."_settings", "get");

if($find_diagram_type===false)//Restore saved setting
{
	if ($saved_group_by <> '')
		$find_diagram_type = $saved_group_by;
	else
		$find_diagram_type = "COUNTER";
}
elseif($saved_group_by!=$find_diagram_type)//Set if changed
	$saved_group_by=$find_diagram_type;

InitFilterEx($arSettings, $sTableID."_settings", "set");



$sTableID = "t_visit_section_list_".$find_diagram_type;
$oSort = new CAdminSorting($sTableID, "COUNTER", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arM = array(
		GetMessage("STAT_F_SECTIONS"),
		GetMessage("STAT_F_VIEW")
);

if (is_array($arrADV))
	$arM[] = GetMessage("STAT_F_ADV");

$arM[] = GetMessage("STAT_F_ADV_DATA_TYPE");

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	$arM
);



$arrExactMatch = array(
	"SECTION_EXACT_MATCH"		=> "find_section_exact_match",
	"FIRST_PAGE_EXACT_MATCH"	=> "find_first_page_exact_match",
	"LAST_PAGE_EXACT_MATCH"		=> "find_last_page_exact_match",
	"PAGE_EXACT_MATCH"			=> "find_page_exact_match"
	);

$arFilterFields = Array(
	"find_date1", "find_date2",
	"find_site_id","find_page_404","find_section","find_section_exact_match",
	"find_show",
	"find_adv",
	"find_adv_data_type",

);

if (!is_array($arrADV))
	unset($arFilterFields["find_adv"]);

if($lAdmin->IsDefaultFilter())
{
	$find_show = "D";
	$find_section = "~/bitrix/";
	$find_date1_DAYS_TO_BACK = 90;
}

$lAdmin->InitFilter($arFilterFields);

InitBVar($find_section_exact_match);

if(is_array($find_adv))
{
	$find_adv_names = array();
	foreach($find_adv as $value)
	{
		//$find_adv[$key]=intval($value);
		$find_adv_names[]=$arrADV[$value];
	}
}
else
{
	$find_adv=array();
	$find_adv_names = array();
}

AdminListCheckDate($lAdmin, array("find_date1"=>$find_date1, "find_date2"=>$find_date2));

if(is_array($find_adv) && count($find_adv)>0)
	$str = implode(" | ",$find_adv);
else
	$str = "";

$arFilter = Array(
	"DATE1"				=> $find_date1,
	"DATE2"				=> $find_date2,
	"SHOW"				=> $find_show,
	"SECTION"			=> $find_section,
	"SITE_ID"			=> $find_site_id,
	"PAGE_404"			=> $find_page_404,
	"ADV"				=> $str,
	"ADV_DATA_TYPE"		=> $find_adv_data_type,
	"SECTION_EXACT_MATCH"		=> $find_section_exact_match,
);

$rsPages = CPage::GetList($find_diagram_type, $by, $order, $arFilter);

switch ($find_diagram_type)
{
	case "COUNTER":
		$diagram_title = GetMessage("STAT_T_HITS_DIAGRAM");
		$column_title = GetMessage("STAT_HITS");
		$group_title = GetMessage("STAT_GROUP_BY_HITS");
		break;
	case "EXIT_COUNTER":
		$diagram_title = GetMessage("STAT_T_EXIT_DIAGRAM");
		$column_title = GetMessage("STAT_SESSIONS");
		$group_title = GetMessage("STAT_GROUP_BY_EXITS");
		break;
	case "ENTER_COUNTER":
		$diagram_title = GetMessage("STAT_T_ENTER_DIAGRAM");
		$column_title = GetMessage("STAT_SESSIONS");
		$group_title = GetMessage("STAT_GROUP_BY_ENTERS");
		break;
}

$arrPages = array();
$max_counter = 0;
$sum_counter = 0;
while ($arPage = $rsPages->Fetch())
{
	$arrPages[] = $arPage;
	$sum_counter += $arPage["COUNTER"];
	if (intval($arPage["COUNTER"])>$max_counter) $max_counter = intval($arPage["COUNTER"]);
}
$rsPages = new CDBResult;
$rsPages->InitFromArray($arrPages);


$rsData = new CAdminResult($rsPages, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_SECTION_PAGES")));

$arHeaders = Array();

$arHeaders[] = array("id"=>"NUMBER", "content"=>"ID","default"=>true,);
$arHeaders[] = array("id"=>"URL", "content"=>GetMessage("STAT_PAGE"), "sort"=>"s_url", "default"=>true,);
$arHeaders[] = array("id"=>"COUNTER", "content"=>$column_title, "sort"=>"s_counter", "default"=>true, "align" => "right");
$arHeaders[] = array("id"=>"PERCENT", "content"=>GetMessage("STAT_PERCENT"), "default"=>true, "align"=>"right",);

$lAdmin->AddHeaders($arHeaders);



$number = (intval($rsData->NavPageNomer)-1)*intval($rsData->NavPageSize);
// maximum diagram width in percent of work area
$max_width = 100;
// normalization
$max_relation = ($max_counter*100)/$max_width;

$s = "";
foreach($find_adv as $f)
	$s .= "&find_adv[]=".urlencode($f);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$w = round(($f_COUNTER*100)/$max_relation);
	$q = number_format(($f_COUNTER*100)/$sum_counter, 2, '.', '');
	$number++;
	$site_url = $arSites[$f_SITE_ID];

	$row =& $lAdmin->AddRow($number, $arRes);

	$str = '<a target="_blank" title="'.GetMessage("STAT_GO").'" href="'.$f_URL.'">&raquo;</a>&nbsp;';
		if ($f_DIR=="Y") :
				$str .= '<a title="'.GetMessage("STAT_FILTER_PAGE_DIAGRAM_ALT").'" href="'.$APPLICATION->GetCurPage().'?lang='.LANG.GetFilterParams($arFilterFields).'&find_diagram_type='.$find_diagram_type.'&find_section='.urlencode("$f_URL% ~$f_URL").'&find_show=F&find_section_exact_match=Y&set_filter=Y">';
			if ($f_URL_404=="Y") :
				$str .= "<span class=\"stat_attention\">".TruncateText($f_URL,65)."</span>";
			else :
				$str .= TruncateText($f_URL,65);
			endif;
			$str .= "</a>";
		else :
			if ($f_URL_404=="Y") :
				$str .= "<span class=\"stat_attention\">".TruncateText($f_URL,65)."</span>";
			else :
				$str .= TruncateText($f_URL,65);
			endif;
		endif;

	$row->AddViewField("URL", $str);

	$row->AddViewField("PERCENT", $q."%");

	$row->AddViewField("NUMBER", $number);


	$str = "<a href=\"hit_list.php?lang=".LANG."&find_url=".urlencode($f_URL."%")."&find_url_exact_match=Y&set_filter=Y\">".$f_COUNTER."</a>";
	$row->AddViewField("COUNTER", $str);

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"graphic",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("STAT_SECTION_GRAPH"),
		//"ACTION"=>"javascript:CloseWaitWindow();ShowGraph('".urlencode($f_URL)."', '".$f_SITE_ID."', '".$f_DIR."')",
		"ACTION"=>"javascript:CloseWaitWindow();jsUtils.OpenWindow('section_graph_list.php?lang=".LANG.$s."&find_adv_data_type=".$find_adv_data_type."&date1=".urlencode($find_date1)."&date2=".urlencode($find_date2)."&section=".urlencode($f_URL)."&site_id=".$f_SITE_ID."&is_dir=".$f_DIR."&set_default=Y', 620, 600);",
	);

	$arActions[] = array(
		"ICON"=>"",
		"TEXT"=>GetMessage("STAT_SECTION_LINK_STAT"),
		"ACTION"=>$lAdmin->ActionRedirect($f_URL."?show_link_stat=Y"),
	);

	$row->AddActions($arActions);

	//$row->AddViewField("GRAPH", '<img src="/bitrix/images/statistic/votebar.gif" width="'.($w==0 ? "0" : $w."%").'" height="10" border=0 alt="">');
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("title"=>GetMessage("STAT_TOTAL"), "value"=>$sum_counter),
	)
);

if($context!="tab")
	$lAdmin->AddAdminContextMenu(array());

$lAdmin->BeginPrologContent();?>


<?if (is_array($arrPages) && count($arrPages)>0):?>
<div class="graph">
<table cellpadding="0" cellspacing="0" border="0" class="graph" align="center">
	<tr>
		<td>
		<?$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");?>

		<img class="graph" src="<?echo htmlspecialcharsbx("visit_section_diagram.php?lang=".LANG."
&find_section=".urlencode($find_section)."&find_date1=".urlencode($find_date1)."&find_date2=".urlencode($find_date2)."&find_show=".urlencode($find_show)."&find_site_id=".urlencode($find_site_id)."&find_page_404=".urlencode($find_page_404)."&find_adv_data_type=".urlencode($find_adv_data_type)."&find_section_exact_match=".urlencode($find_section_exact_match).$s.GetFilterParams("find_")."&by=".urlencode($by)."&order=".urlencode($order))?>" width="<?=$diameter?>" height="<?=$diameter?>">
		</td>
		<td>
		<table border="0" cellspacing="2" cellpadding="0" class="legend">
			<?
			$i = 1;
			$max_width = 100;
			$max_relation = ($max_counter*100)/$max_width;
			$total = count($arrPages);
			if ($total>10)
				$total = 11;
			$top_sum = 0;

			foreach($arrPages as $key => $arVal):
			if ($i==11) break;
			$color = GetNextRGB($color, $total);
			$q = number_format(($arVal["COUNTER"]*100)/$sum_counter, 2, '.', '');
			//$w = round(($arVal["COUNTER"]*100)/$max_relation);
			$top_sum += $arVal["COUNTER"];

			$str = '<a target="_blank" title="'.GetMessage("STAT_GO").'" href="'.htmlspecialcharsbx($arVal["URL"]).'">&raquo;</a>&nbsp;';
			if ($arVal["DIR"]=="Y") :
				$str .= '<a title="'.GetMessage("STAT_FILTER_PAGE_DIAGRAM_ALT").'" href="'.htmlspecialcharsbx($APPLICATION->GetCurPage().'?lang='.LANG.GetFilterParams($arFilterFields).'&find_diagram_type='.$find_diagram_type.'&find_section='.urlencode($arVal["URL"]."% ~".$arVal["URL"]).'&find_show=F&find_section_exact_match=Y&set_filter=Y').'">';
				if ($arVal["URL_404"]=="Y") :
					$str .= "<span class=\"stat_attention\">".htmlspecialcharsEx(TruncateText($arVal["URL"],45))."</span>";
				else:
					$str .= htmlspecialcharsEx(TruncateText($arVal["URL"],45));
				endif;
			$str .= "</a>";
			else :
				if(mb_substr($arVal["URL"], -1) == "/")
					$arVal["URL"] .= "index.php";
				if ($arVal["URL_404"]=="Y") :
					$str .= "<span class=\"stat_attention\">".htmlspecialcharsEx(TruncateText($arVal["URL"],45))."</span>";
				else:
					$str .= htmlspecialcharsEx(TruncateText($arVal["URL"],45));
				endif;
			endif;
			?>
			<tr>
					<td valign="center" class="color">
						<div style="background-color: <?="#".$color?>"></div>
					</td>
					<td class="number"><?=$q?>%</td>
					<td><?=$str?></td>
					<td class="number"><a href="<?echo htmlspecialcharsbx("hit_list.php?lang=".LANG."&find_url=".urlencode($arVal["URL"]."%")."&find_url_exact_match=Y&set_filter=Y")?>"><?echo $arVal["COUNTER"]?></a></td>
			</tr>
			<?$i++;endforeach;?>
			<?if ($total==11):?>
			<tr>
					<td valign="center" class="color">
						<div style="background-color: <?="#".GetNextRGB($color, $total)?>"></div>
					</td>
					<td class="number"><?=(number_format((($sum_counter-$top_sum)*100)/$sum_counter, 2, '.', ''))?>%</td>
					<td><?=GetMessage("STAT_OTHER")?></td>
					<td class="number"><?=($sum_counter-$top_sum)?></td>
			</tr>
			<?endif?>
		</table>
		</td>
	</tr>
</table>
</div>
<?else:?>
	<?//CAdminMessage::ShowMessage(GetMessage("STAT_NO_DATA"))?>
<?endif?>

<h2><?=$diagram_title?></h2>

<?
$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();


if($find_diagram_type == "ENTER_COUNTER")
	$APPLICATION->SetTitle(GetMessage("STAT_GROUP_BY_ENTERS"));
elseif($find_diagram_type == "EXIT_COUNTER")
	$APPLICATION->SetTitle(GetMessage("STAT_GROUP_BY_EXITS"));
else
	$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>




<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>">
<?$filter->Begin();?>

<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_SECTIONS")?>:</td>
	<td width="0%" nowrap><?
		echo SelectBoxFromArray("find_site_id", $arSiteDropdown, $find_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_page_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_page_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_section" size="37" value="<?echo htmlspecialcharsbx($find_section)?>"><?=ShowExactMatchCheckbox("find_section")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?
		echo GetMessage("STAT_F_VIEW")?>:</td>
	<td width="100%" nowrap><?
		$arr = array("reference"=>array(GetMessage("STAT_F_VIEW_SECTIONS"), GetMessage("STAT_F_VIEW_FILES")), "reference_id"=>array("D","F"));
		echo SelectBoxFromArray("find_show", $arr, htmlspecialcharsbx($find_show), GetMessage("MAIN_ALL"));
		?>
		</td>
</tr>
<?

if (is_array($arrADV)):
?>
<tr valign="top">
	<td width="0%" nowrap valign="top"><?
		echo GetMessage("STAT_F_ADV")?>:<br><img src="/bitrix/images/statistic/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td width="100%" nowrap><?
		echo SelectBoxMFromArray("find_adv[]",array("REFERENCE"=>$find_adv_names, "REFERENCE_ID"=>$find_adv), $find_adv,"",false,"5", "style=\"width:300px;\"");
		?>
	<script language="Javascript">
	function selectEventType(form, field)
	{
		jsUtils.OpenWindow('adv_multiselect.php?lang=<?=LANG?>&form='+form+'&field='+field, 600, 600);
	}
	jsSelectUtils.sortSelect('find_adv[]');
	jsSelectUtils.selectAllOptions('find_adv[]');
	</script>
	<br>
	<input type="button" OnClick="selectEventType('find_form','find_adv[]')" value="<?=GetMessage("MAIN_ADMIN_MENU_ADD")?>...">&nbsp;
	<input type="button" OnClick="jsSelectUtils.deleteSelectedOptions('find_adv[]');" value="<?=GetMessage("MAIN_ADMIN_MENU_DELETE")?>">

		</td>
</tr>
<?endif;?>
<tr valign="top">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_ADV_DATA_TYPE")?>:</td>
	<td width="0%" nowrap><?
		$arr = array(
		"reference"=>array(
			GetMessage("STAT_ADV_SUMMA"),
			GetMessage("STAT_ADV_NO_BACK"),
			GetMessage("STAT_ADV_BACK")
			),
		"reference_id"=>array("S","P","B"));
		echo SelectBoxFromArray("find_adv_data_type", $arr, htmlspecialcharsbx($find_adv_data_type));
		?></td>
</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
if ($message)
	echo $message->Show();
?>

<?$lAdmin->DisplayList();?>

<?echo BeginNote();?>
<table border="0" width="100%" cellspacing="1" cellpadding="3">
	<tr>
		<td nowrap><?echo GetMessage("STAT_ATTENTION")?>&nbsp;!<br><?echo GetMessage("STAT_ATTENTION_GOTO")?></td>
	</tr>
</table>
<?echo EndNote();?>
<?require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
