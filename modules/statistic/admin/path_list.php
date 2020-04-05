<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$context = ($_REQUEST["context"] === "tab"? "tab": "");
if($_REQUEST["table_id"] === "t_path_list_COUNTER")
	$table_id = "t_path_list_COUNTER";
elseif($_REQUEST["table_id"] === "t_path_list_COUNTER_FULL_PATH")
	$table_id = "t_path_list_COUNTER_FULL_PATH";
else
	$table_id = "";

$arSites = array();
$ref = $ref_id = array();
$rs = CSite::GetList(($v1="sort"), ($v2="asc"));
while ($ar = $rs->Fetch())
{
	$ref[] = $ar["ID"];
	$ref_id[] = $ar["ID"];
	$arSites[$ar["ID"]] = "[<a href=\"/bitrix/admin/site_edit.php?LID=".$ar["ID"]."&lang=".LANGUAGE_ID."\">".$ar["ID"]."</a>]&nbsp;";
}
$arSiteDropdown = array("reference" => $ref, "reference_id" => $ref_id);

$rs = CAdv::GetList($v1="", $v2="", Array(), $v3, "", $v4, $v5);
while ($ar = $rs->Fetch())
{
	$arrADV[$ar["ID"]] = $ar["REFERER1"]." / ".$ar["REFERER2"]." [".$ar["ID"]."]";
	$arrADV_DETAIL[$ar["ID"]] = array("REFERER1" => $ar["REFERER1"], "REFERER2" => $ar["REFERER2"]);
}

if(isset($find_referer1) && strlen($find_referer1) > 0)
{
	$find_adv=array();
	foreach($arrADV_DETAIL as $ADV_ID=>$ADV_DETAIL)
		if($ADV_DETAIL["REFERER1"]==$find_referer1 && !in_array($ADV_ID, $find_adv))
			$find_adv[]=$ADV_ID;
}
if(isset($find_referer2) && strlen($find_referer2) > 0)
{
	$find_adv=array();
	foreach($arrADV_DETAIL as $ADV_ID=>$ADV_DETAIL)
		if($ADV_DETAIL["REFERER2"]==$find_referer2 && !in_array($ADV_ID, $find_adv))
			$find_adv[]=$ADV_ID;
}

if(isset($find_diagram_type))
{
	if($find_diagram_type!="COUNTER_FULL_PATH")
		$find_diagram_type="COUNTER";
}
else
{
	$find_diagram_type=false;
}

if($context=="tab")
	$sTableID = "t_path_list_".$find_diagram_type;
else
	$sTableID = "t_path_list";

$oSort = new CAdminSorting($sTableID, "s_counter", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		GetMessage("STAT_F_STEPS"),
		GetMessage("STAT_F_FIRST_PAGE"),
		GetMessage("STAT_F_PAGE"),
		GetMessage("STAT_F_LAST_PAGE"),
		GetMessage("STAT_F_ADV"),
		GetMessage("STAT_F_ADV_DATA_TYPE"),

	)
);

if($lAdmin->IsDefaultFilter())
{
	$find_date1_DAYS_TO_BACK = 90;
	$find_first_page = "~/bitrix/";
	$set_filter = "Y";
}

$FilterArr = Array(
	"find_date1", "find_date2",
	"find_steps1","find_steps2",
	"find_first_page_site_id","find_first_page_404","find_first_page","find_first_page_exact_match",
	"find_page_site_id","find_page_404","find_page",
	"find_last_page_site_id","find_last_page_404","find_last_page","find_last_page_exact_match",
	"find_adv",
	"find_adv_data_type",
);

$lAdmin->InitFilter($FilterArr);

//Restore & Save settings (windows registry like)
$arSettings = array("saved_diagram_type");
InitFilterEx($arSettings, $sTableID."_settings", "get");

if($find_diagram_type===false)//Restore saved setting
{
	if(strlen($saved_diagram_type) > 0)
		$find_diagram_type = $saved_diagram_type;
	else
		$find_diagram_type = "COUNTER";
}
elseif($saved_diagram_type!=$find_diagram_type)//Set if changed
	$saved_diagram_type=$find_diagram_type;

InitFilterEx($arSettings, $sTableID."_settings", "set");

InitBVar($find_first_page_exact_match);
InitBVar($find_last_page_exact_match);

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

$arFilter = Array(
	"DATE1" => $find_date1,
	"DATE2" => $find_date2,
	"FIRST_PAGE" => $find_first_page,
	"FIRST_PAGE_SITE_ID" => $find_first_page_site_id,
	"FIRST_PAGE_404" => $find_first_page_404,
	"LAST_PAGE" => $find_last_page,
	"LAST_PAGE_SITE_ID" => $find_last_page_site_id,
	"LAST_PAGE_404" => $find_last_page_404,
	"PAGE" => $find_page,
	"PAGE_SITE_ID" => $find_page_site_id,
	"PAGE_404" => $find_page_404,
	"ADV" => (is_array($find_adv) && count($find_adv) > 0? implode(" | ",$find_adv): ""),
	"ADV_DATA_TYPE" => $find_adv_data_type,
	"STEPS1" => $find_steps1,
	"STEPS2" => $find_steps2,
	"FIRST_PAGE_EXACT_MATCH" => $find_first_page_exact_match,
	"LAST_PAGE_EXACT_MATCH" => $find_last_page_exact_match,
);

$rsPath = CPath::GetList($parent_id, $find_diagram_type, $by, $order, $arFilter, $is_filtered);

$str_err_404 = "ERROR_404: ";

$arrPath = array();
$max_counter = 0;
$sum_counter = 0;
while ($arPath = $rsPath->Fetch())
{
	$arrPath[] = $arPath;
	$sum_counter += $arPath["COUNTER"];
	if(intval($arPath["COUNTER"])>$max_counter)
		$max_counter = intval($arPath["COUNTER"]);
}
$rsPath = new CDBResult;
$rsPath->InitFromArray($arrPath);

$rsData = new CAdminResult($rsPath, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("STAT_PATH_PAGES")));

$lAdmin->BeginPrologContent();

if($find_diagram_type=="COUNTER"):
	if(strlen($parent_id)>0) :
	?>
		<table cellspacing=0 cellpadding=0 class="list">
			<tr class="gutter">
				<td><div class="empty"></div></td>
				<td><div class="empty"></div></td>
				<td><div class="empty"></div></td>
				<td><div class="empty"></div></td>
			</tr>
		<tr class="head">
			<td>&nbsp;</td>
			<td><?=GetMessage("STAT_PATH_PART")?></td>
			<td><?=GetMessage("STAT_TRANSFER")?></td>
			<td><?=GetMessage("STAT_PERCENT")?></td>
		</tr>
	<?
		$rsParentPath = CPath::GetByID($parent_id);
		if($arParentPath = $rsParentPath->Fetch()):
			$arrPages = explode("\n",$arParentPath["PAGES"]);
			reset($arrPages);
			$i=0;
			foreach($arrPages as $page):
				if(strlen($page)>0) :
					$i++;
					$arr = array();
					$site_url = "";
					preg_match("#\[(.+?)\]#",$page, $arr);
					if(strlen($arr[1])>0)
					{
						$page = str_replace("[".$arr[1]."] ", "", $page);
						$site_url = $arSites[$arr[1]];
					}
					$err_404 = "N";
					if(substr($page,0,strlen($str_err_404))==$str_err_404)
					{
						$err_404 = "Y";
						$page = substr($page,strlen($str_err_404),strlen($page));
					}
					?>
					<tr>
						<td nowrap><?=$i?></td>
						<td nowrap><a title="<?=GetMessage("STAT_GO")?>" href="<?=htmlspecialcharsbx($page)?>">&raquo;</a>&nbsp;<?
							$new_path_id = GetStatPathID($page, $path_id);
							$arParent[$new_path_id] = $path_id;
							$path_id = $new_path_id;
							if($path_id!=$parent_id) :
								$prev_parent_path = $path_id;
								$action_url = "path_list.php?lang=".LANGUAGE_ID."&find_diagram_type=COUNTER&parent_id=".urlencode($path_id)."&context=".urlencode($context);
								$action_js = ($table_id==""? $sTableID:$table_id).".GetAdminList('".CUtil::JSEscape($action_url)."');";
									?><a href="javascript:void(0)" onclick="<?echo htmlspecialcharsbx($action_js)?>"><?
								if($err_404=="Y"):
									?><span class="stat_attention"><?echo htmlspecialcharsEx(TruncateText($page,65))?></span><?
								else:
									echo htmlspecialcharsEx(TruncateText($page,65));
								endif;
								?></a><?
							else :
								if($err_404=="Y"):
									?><span class="stat_attention"><?echo htmlspecialcharsEx(TruncateText($page,65))?></span><?
								else:
									echo htmlspecialcharsEx(TruncateText($page,65));
								endif;
							endif;
							$arFilter["PATH_ID"] = $path_id;
							$z = CPath::GetList($arParent[$path_id], $find_diagram_type, $v1, $v2, $arFilter, $v3);
							$zr = $z->Fetch();
							$counter = $zr["COUNTER"];
							if($i==1) $max = $counter;

							$percent = ($counter*100)/$max;
							$percent_f = number_format($percent, 2, '.', '');
							$percent_m = number_format(100-$percent, 2, '.', '');

							$alt = "";
							if($i==1)
								$alt = GetMessage("STAT_PATH_START");
							else
								$alt = GetMessage("STAT_PATH_ALT_1")." ".$counter.". ". GetMessage("STAT_PATH_ALT_2")." ".$percent_f."%. ".GetMessage("STAT_PATH_ALT_3")." ".$percent_m."%";
						?></td>
						<td nowrap align="right" width="15%">&nbsp;<?=intval($counter)?></td>
						<td nowrap align="right" width="15%"><span title="<?=$alt?>">&nbsp;<?=($i>1) ? $percent_f."%" : ""?></span></td>
					</tr>
				<?
				endif;
			endforeach;
		endif;
		?></table>
	<?
	endif;
endif;

$lAdmin->EndPrologContent();

$arHeaders = Array();

$arHeaders[] = array("id"=>"NUMBER", "content"=>GetMessage("STAT_NUM"), "default"=>true, "align"=>"right");
$arHeaders[] = array("id"=>"URL", "content"=>GetMessage("STAT_PAGE"), "sort"=>"s_url", "default"=>true,);
$arHeaders[] = array("id"=>"COUNTER", "content"=>GetMessage("STAT_TRANSFER"), "sort"=>"s_counter", "default"=>true, "align"=>"right");
$arHeaders[] = array("id"=>"PERCENT", "content"=>GetMessage("STAT_PERCENT"), "default"=>true, "align"=>"right",);

$lAdmin->AddHeaders($arHeaders);

$number = (intval($rsData->NavPageNomer)-1)*intval($rsData->NavPageSize);
$max_width = 90;
$max_relation = ($max_counter*100)/$max_width;

while($arRes = $rsData->NavNext(true, "f_"))
{
	$w = round(($f_COUNTER*100)/$max_relation);
	$q = number_format(($f_COUNTER*100)/$sum_counter, 2, '.', '');
	$number++;

	$row =& $lAdmin->AddRow($number, $arRes);

	$str = "";

	if($find_diagram_type=="COUNTER")
	{
		$action_url = "path_list.php?lang=".LANGUAGE_ID."&find_diagram_type=COUNTER&parent_id=".urlencode($f_PATH_ID)."&context=".urlencode($context);
		$action_js = ($table_id==""? $sTableID:$table_id).".GetAdminList('".CUtil::JSEscape($action_url)."');";
		$str .= "";
		$str .= '<a title="'.GetMessage("STAT_GO").'" href="'.$f_LAST_PAGE.'">&raquo;</a>&nbsp;<a title="'.GetMessage("STAT_NEXT_STEP").'" href="javascript:void(0)" onclick="'.htmlspecialcharsbx($action_js).'">';

		if($f_LAST_PAGE_404=="Y")
			$str .= "<span class=\"stat_attention\">".TruncateText($f_LAST_PAGE,65)."</span>";
		else
			$str .= TruncateText($f_LAST_PAGE,65);
	}
	else
	{
		$arrPAGES = explode("\n",$f_PAGES);
		$path_id = "";

		foreach($arrPAGES as $page)
		{
			if(strlen($page)>0)
			{
				$arr = array();
				$site_url = "";
				preg_match("#\[(.+?)\]#",$page, $arr);
				if(strlen($arr[1])>0)
				{
					$page = str_replace("[".$arr[1]."] ", "", $page);
					$site_url = $arSites[$arr[1]];
				}

				$err_404 = "N";
				if(substr($page,0,strlen($str_err_404))==$str_err_404)
				{
					$err_404 = "Y";
					$page = substr($page,strlen($str_err_404),strlen($page));
				}
				$path_id = GetStatPathID($page, $path_id);
				$action_url = "path_list.php?lang=".LANGUAGE_ID."&find_diagram_type=COUNTER&parent_id=".urlencode($path_id)."&context=".urlencode($context);
				$action_js = ($table_id==""? $sTableID: $table_id).".GetAdminList('".CUtil::JSEscape($action_url)."');";
				$str .= '<a title="'.GetMessage("STAT_GO").'" href="'.$page.'">&raquo;</a>&nbsp;<a title="'.GetMessage("STAT_NEXT_PAGES").'" href="javascript:void(0)" onclick="'.htmlspecialcharsbx($action_js).'">';
				if($err_404=="Y")
					$str .= '<span class="stat_attention">'.TruncateText($page,65).'</span>';
				else
					$str .= TruncateText($page,80);

				$str .= '<br>';

			}

		}

	}

	$row->AddViewField("URL", $str);

	$row->AddViewField("NUMBER", $number);
	$row->AddViewField("PERCENT", $q."%");

}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("title"=>GetMessage("STAT_TOTAL"), "value"=>$sum_counter),
	)
);

$aContext = array();
if(strlen($parent_id)>0)
{
	$aContext[] = array(
		"TEXT" => GetMessage("STAT_ENTER_POINTS_S"),
		"ICON" => "btn_list",
		"LINK" =>"/bitrix/admin/path_list.php?lang=".LANG."&set_default=Y&find_diagram_type=COUNTER_FULL_PATH",
	);
}

$aContext[] =
	array(
		"TEXT"=>($find_diagram_type!="COUNTER_FULL_PATH" ? GetMessage("STAT_F_SEGMENT_PATH") : GetMessage("STAT_F_FULL_PATH")),
		"MENU"=>array(
			array(
				"TEXT"=>GetMessage("STAT_F_SEGMENT_PATH"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_diagram_type=COUNTER"),
				"ICON"=>($find_diagram_type=="COUNTER"?"checked":""),
			),
			array(
				"TEXT"=>GetMessage("STAT_F_FULL_PATH"),
				"ACTION"=>$lAdmin->ActionDoGroup(0, "", "find_diagram_type=COUNTER_FULL_PATH"),
				"ICON"=>($find_diagram_type=="COUNTER_FULL_PATH"?"checked":""),
			),
		),
	);

if($context!="tab")
	$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("STAT_RECORDS_LIST"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>


<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>">
<?$filter->Begin();?>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PERIOD").":"?></td>
	<td width="0%" nowrap><?echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1","Y")?></td>
</tr>
<tr>
	<td><?echo GetMessage("STAT_F_STEPS")?>:</td>
	<td><input type="text" name="find_steps1" size="10" value="<?echo htmlspecialcharsbx($find_steps1)?>"><?echo "&nbsp;".GetMessage("STAT_TILL")."&nbsp;"?><input type="text" name="find_steps2" size="10" value="<?echo htmlspecialcharsbx($find_steps2)?>"></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_FIRST_PAGE")?>:</td>
	<td width="0%" nowrap><?
		echo SelectBoxFromArray("find_first_page_site_id", $arSiteDropdown, $find_first_page_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_first_page_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_first_page_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_first_page" size="37" value="<?echo htmlspecialcharsbx($find_first_page)?>"><?=ShowExactMatchCheckbox("find_first_page")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_PAGE")?>:</td>
	<td width="0%" nowrap><?
		echo SelectBoxFromArray("find_page_site_id", $arSiteDropdown, $find_page_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_page_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_page_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_page" size="37" value="<?echo htmlspecialcharsbx($find_page)?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="center">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_LAST_PAGE")?>:</td>
	<td width="0%" nowrap><?
		echo SelectBoxFromArray("find_last_page_site_id", $arSiteDropdown, $find_last_page_site_id, GetMessage("STAT_D_SITE"));
	?>&nbsp;<?
		echo SelectBoxFromArray("find_last_page_404", array("reference"=>array(GetMessage("STAT_YES"), GetMessage("STAT_NO")), "reference_id"=>array("Y","N")), htmlspecialcharsbx($find_last_page_404), GetMessage("STAT_404"));
	?>&nbsp;<input type="text" name="find_last_page" size="37" value="<?echo htmlspecialcharsbx($find_last_page)?>"><?=ShowExactMatchCheckbox("find_last_page")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr valign="top">
	<td width="0%" nowrap valign="top"><?
		echo GetMessage("STAT_F_ADV")?>:<br><img src="/bitrix/images/statistic/mouse.gif" width="44" height="21" border=0 alt=""></td>
	<td width="100%" nowrap>
	<?
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
<tr valign="top">
	<td width="0%" nowrap><?echo GetMessage("STAT_F_ADV_DATA_TYPE")?>:</td>
	<td width="0%" nowrap><?
		$arr = array(
		"reference"=>array(
			GetMessage("STAT_ADV_SUMMA"),
			GetMessage("STAT_ADV_NO_BACK"),
			GetMessage("STAT_ADV_BACK")
			),
		"reference_id"=>array("S", "P","B"));
		echo SelectBoxFromArray("find_adv_data_type", $arr, htmlspecialcharsbx($find_adv_data_type));
		?></td>
</tr>
<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>

<?
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
