<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";

if($bAdmin!="Y" && $bDemo!="Y")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");

$sTableID = "tbl_support";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_name",
	"filter_open_time",
	"filter_sla_id"
);
$USER_FIELD_MANAGER->AdminListAddFilterFields("SUPPORT", $arFilterFields);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if(strlen($filter_name) > 0)
	$arFilter["~NAME"] = "%".$filter_name."%";
if(strlen($filter_open_time) > 0)
	$arFilter["OPEN_TIME"] = $filter_open_time;
if(is_array($filter_sla_id))
	$arFilter["SLA_ID"] = $filter_sla_id;
else
	$filter_sla_id = array();

$USER_FIELD_MANAGER->AdminListAddFilter("SUPPORT", $arFilter);

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target'] == 'selected')
	{
		$arID = Array();
		$dbResultList = CSupportHolidays::GetList(array($by => $order), $arFilter);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID) <= 0)
		{
			continue;
		}
		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				CSupportHolidays::Delete($ID);
				break;
		}
	}
}

$arHeaders = array(
	array("id"=>"ID",			"content"=>"ID",						"sort"=>"ID",			"default"=>true),
	array("id"=>"NAME",			"content"=>GetMessage("SUP_NAME"),		"sort"=>"NAME",			"default"=>true),
	array("id"=>"OPEN_TIME",	"content"=>GetMessage("SUP_OPEN_TIME"),	"sort"=>"OPEN_TIME",	"default"=>true),
	array("id"=>"DATE_FROM",	"content"=>GetMessage("SUP_DATE_FROM"),	"sort"=>"DATE_FROM",	"default"=>true),
	array("id"=>"DATE_TILL",	"content"=>GetMessage("SUP_DATE_TILL"),	"sort"=>"DATE_TILL",	"default"=>true),
	array("id"=>"SLA",			"content"=>GetMessage("SUP_SLA"),			"default"=>true),
	
);

$USER_FIELD_MANAGER->AdminListAddHeaders("SUPPORT", $arHeaders);
$lAdmin->AddHeaders($arHeaders);

/*
$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSelectedFields = array("ID", "NAME", "OPEN_TIME", "DATE_FROM", "DATE_TILL", "SLA");

foreach($arVisibleColumns as $val)
	if(!in_array($val, $arSelectedFields))
		$arSelectedFields[] = $val;
*/
$dbResultList = CSupportHolidays::GetList(array($by => $order), $arFilter);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SUP_GROUP_NAV")));

while ($arBlog = $dbResultList->NavNext(true, "f_"))
{    
	$row =& $lAdmin->AddRow($f_ID, $arBlog, "/bitrix/admin/ticket_holidays_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID, GetMessage("SUP_UPDATE_ALT"));
	$row->AddField("NAME", '<a href="/bitrix/admin/ticket_holidays_edit.php?ID=' . $f_ID . '&lang=' . LANGUAGE_ID . '" title="' . GetMessage("SUP_UPDATE_ALT").'">'.$f_NAME.'</a>');
	$row->AddField("OPEN_TIME", GetMessage(CSupportHolidays::GetOpenTimeT($f_OPEN_TIME)));
	if($f_OPEN_TIME != "HOLIDAY_H" && $f_OPEN_TIME != "WORKDAY_H")
	{
		$f_DATE_FROM = GetTime(MakeTimeStamp($f_DATE_FROM),"SHORT");
		$f_DATE_TILL = GetTime(MakeTimeStamp($f_DATE_TILL),"SHORT");
	}
	$row->AddField("DATE_FROM", $f_DATE_FROM);
	$row->AddField("DATE_TILL", $f_DATE_TILL);
	
	$SLA = "";
	$rs = CSupportHolidays::GetSLAByID($f_ID);
	while($arR = $rs->Fetch())
	{
		$SLA .= '<a href="/bitrix/admin/ticket_sla_edit.php?ID=' . intval($arR["SLA_ID"]) . '&lang=' . LANGUAGE_ID . '">' . htmlspecialcharsbx($arR["NAME"]) . '</a><br/>';
	}
	$row->AddField("SLA", $SLA);

	
	//$row->AddField("USE_SOCNET", (($f_USE_SOCNET == "Y") ? GetMessage("BLB_YES") : GetMessage("BLB_NO")));
	
	$USER_FIELD_MANAGER->AddUserFields("SUPPORT", $arBlog, $row);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SUP_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("ticket_holidays_edit.php?ID=" . $f_ID . "&lang=" . LANG . "&" . GetFilterParams("filter_") . ""), "DEFAULT"=>true);
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SUP_DELETE_ALT"), "ACTION"=>"if(confirm('" . GetMessage('SUP_DELETE_CONF') . "')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	$row->AddActions($arActions);
}





$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);


$aContext = array(
	array(
		"TEXT" => GetMessage("SUP_ADD_NEW"),
		"ICON" => "btn_new",
		"LINK" => "ticket_holidays_edit.php?lang=".LANG,
		"TITLE" => GetMessage("SUP_ADD_NEW_ALT")
	),
);
$lAdmin->AddAdminContextMenu($aContext);


$lAdmin->CheckListMode();

/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SUP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SUP_OPEN_TIME"),
		GetMessage("SUP_SLA")
	)
);

$oFilter->Begin();

/*
"filter_name",
"filter_open_time",
"filter_sla_id"
*/

?>
	<tr>
		<td><?echo GetMessage("SUP_FILTER_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("SUP_FILTER_OPEN_TIME")?>:</td>
		<td>
		<select id="filter_open_time" name="filter_open_time">
		<option value=""></option>';
		<?
		$arr = CSupportHolidays::GetOpenTimeArray();
		foreach($arr as $v => $n)
		{
			$ss = substr($v, 0, 3);
			if($ss == "GB_")
			{
				echo '<optgroup label="' .  GetMessage($n) . '">';
			}
			elseif($ss == "GE_")
			{
				echo '</optgroup>';
			}
			else
			{
				echo '<option value="' . $v . '">' .  GetMessage($n) . '</option>';
			}
		}		
		?>
		</select>
		</td>
	</tr>
	
	<tr>
		<td><?echo GetMessage("SUP_FILTER_SLA")?>:</td>
		<td>
		<?
			$arSort = array();
			$ar = CTicketSLA::GetList($arSort, array(), $is_filtered);
			$slaI = 0;
			while($arR = $ar->Fetch())
			{
				$slaI++;
				$slaC = in_array($arR["ID"], $filter_sla_id) ? 'checked=""' : '';
				echo '<input id="filter_sla_id' . $slaI . '" name="filter_sla_id[]" type="checkbox" value="' . $arR["ID"] . '" ' . $slaC . '>';
				echo '<label class="adm-designed-checkbox-label" for="filter_sla_id' . $slaI . '" title="">' . htmlspecialcharsbx($arR["NAME"]) . '</label><br>';
			}
		?>
		</td>
	</tr>
	
<?
$USER_FIELD_MANAGER->AdminListShowFilter("SUPPORT");

$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>