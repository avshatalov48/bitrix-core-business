<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
{
	require($DOCUMENT_ROOT."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$arColor = Array("08738C", "C6B59C", "0000FF", "FF0000", "FFFF00", "F7C684" ,"8CD694", "9CADCE", "B584BD", "C684BD", "FF94C6", "BDE794", "F7949C", "7BCE6B", "FF6342", "E2F86B", "A5DE63", "42BD6B", "52BDA5", "F79473", "5AC6DE", "94D6C6", "9C52AD", "BD52AD", "9C94C6", "FF63AD", "FF6384", "FE881D", "FF9C21", "FFAD7B", "EFFF29", "7BCE6B", "42BD6B", "52C6AD", "6B8CBD", "3963AD", "F7298C", "A51800", "9CA510", "528C21", "689EB9", "217B29", "6B8CC6", "D6496C", "C6A56B", "00B0A4", "AD844A", "9710B4", "946331", "AD3908", "734210", "008400", "3EC19A", "28D7D7", "6B63AD", "A4C13E", "7BCE31", "A5DE94", "94D6E7", "9C8C73", "FF8C4A", "A7588B", "03CF45", "F7B54A", "808040", "947BBD", "840084", "737373", "C48322", "809254", "1E8259", "63C6DE", "46128D", "8080C0");

IncludeModuleLangFile(__FILE__);
$arStatus = Array();
$dbStatusList = CSaleStatus::GetList(
		array("SORT" => "ASC"),
		array("LID" => LANGUAGE_ID),
		false,
		false,
		array("ID", "NAME", "SORT")
	);
while ($arStatusList = $dbStatusList->GetNext())
{
	$arStatus[$arStatusList["ID"]] = $arStatusList["NAME"];
}
$arSite = array();
$dbSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
while($arSites = $dbSite->GetNext())
{
	$arSite[$arSites["LID"]] = $arSites["NAME"];
}

$sTableID = "tbl_sale_graph_index";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);


if($lAdmin->IsDefaultFilter())
{
	$filter_date_from_DAYS_TO_BACK = COption::GetOptionInt("sale", "order_list_date", 30);
	$filter_date_from = GetTime(time()-86400*COption::GetOptionInt("sale", "order_list_date", 30));
	$filter_by = "week";

	$find_all = "Y";
	$find_payed = "Y";
	$find_allow_delivery = "Y";
	$find_canceled = "Y";
	foreach($arStatus as $k => $v)
		${"find_status_".$k} = "Y";

	$set_filter = "Y";
	$filter_site_id = array_keys($arSite);

}


$arFilterFields = array(
	"filter_date_from",
	"filter_date_to",
	"filter_by",
	"find_all",
	"find_payed",
	"find_allow_delivery",
	"find_canceled",
	"filter_site_id",
);
foreach($arStatus as $k => $v)
	$arFilterFields[] = "find_status_".$k;

$lAdmin->InitFilter($arFilterFields);

$lAdmin->BeginPrologContent();
?>
<?
/***************************************************************************
			HTML form
****************************************************************************/

if (!function_exists("ImageCreate")) :
	CAdminMessage::ShowMessage(GetMessage("STAT_GD_NOT_INSTALLED"));
elseif (count($lAdmin->arFilterErrors)==0) :
	$width = COption::GetOptionInt("sale", "GRAPH_WEIGHT", 800);
	$height = COption::GetOptionInt("sale", "GRAPH_HEIGHT", 600);
	?>
<div class="graph">
<table cellspacing="0" cellpadding="0" class="graph" border="0">
<tr>
	<td valign="top" class="graph">
		<img class="graph" src="/bitrix/admin/sale_stat_graph.php?<?=GetFilterParams($arFilterFields)?>&amp;width=<?=$width?>&amp;height=<?=$height?>&amp;lang=<?=LANG?>&amp;rand=<?=rand()?>&amp;mode=count" width="<?=$width?>" height="<?=$height?>">
	</td>
</tr>
<tr>
	<td valign="top">
	<table cellpadding="2" cellspacing="0" border="0" class="legend">
		<?if ($find_all=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[0]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_COUNT")?></td>
		</tr>
		<?endif;?>
		<?if ($find_payed=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[1]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_PAYED")?></td>
		</tr>
		<?endif;?>
		<?if ($find_allow_delivery=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[2]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_ALLOW_DELIVERY")?></td>
		</tr>
		<?endif;?>
		<?if ($find_canceled=="Y"):?>
		<tr>
			<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[3]?>" width="45" height="2"></td>
			<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_CANCELED")?></td>
		</tr>
		<?endif;?>
		<?
		$i = 4;
		foreach($arStatus as $k => $v)
		{
			if (${"find_status_".$k} == "Y"):?>
			<tr>
				<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[$i]?>" width="45" height="2"></td>
				<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=$v?></td>
			</tr>
			<?endif;
			$i++;
		}
		?>
	</table>
	</td>
</tr>
</table>
</div>
<?endif;?>

<?
$lAdmin->EndPrologContent();


$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

/***************************************************************************
			HTML form
****************************************************************************/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($sTableID."_filter",array(
	GetMessage("SALE_S_SITE"),
	GetMessage("SALE_SHOW"),
	GetMessage("SALE_S_BY"),
));
?>

<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter->Begin();
?>
<tr>
	<td><?echo GetMessage("SALE_S_DATE").":"?></td>
	<td><?echo CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y")?></td>
</tr>
<tr>
	<td valign="top"><?echo GetMessage("SALE_S_SITE");?>:</td>
	<td>
		<?
		foreach($arSite as $k => $v)
		{
			?><input type="checkbox" name="filter_site_id[]" value="<?=$k?>" id="site_<?=$k?>"<?if(in_array($k, $filter_site_id)) echo " checked"?>> <label for="site_<?=$k?>"><?=$v?></label><br /><?
		}
		?>
	</td>
</tr>

<tr valign="top">
	<td><?=GetMessage("SALE_SHOW")?>:</td>
	<td>
		<?echo InputType("checkbox","find_all","Y",$find_all,false,false,'id="find_all"');?>
		<label for="find_all"><?=GetMessage("SALE_COUNT")?></label><br>
		<?echo InputType("checkbox","find_payed","Y",$find_payed,false,false,'id="find_payed"'); ?>
		<label for="find_payed"><?=GetMessage("SALE_PAYED")?></label><br>
		<?echo InputType("checkbox","find_allow_delivery","Y",$find_allow_delivery,false,false,'id="find_allow_delivery"'); ?>
		<label for="find_allow_delivery"><?=GetMessage("SALE_ALLOW_DELIVERY")?></label><br>
		<?echo InputType("checkbox","find_canceled","Y",$find_canceled,false,false,'id="find_canceled"'); ?>
		<label for="find_canceled"><?=GetMessage("SALE_CANCELED")?></label><br>
		<?
		foreach($arStatus as $k => $v)
		{
			echo InputType("checkbox","find_status_".$k,"Y",${"find_status_".$k},false,false,'id="find_status_'.$k.'"');
			?>
			<label for="find_status_<?=$k?>"><?=$v?></label><br>
			<?
		}
		?>
	</td>
</tr>
	<tr>
		<td><?echo GetMessage("SALE_S_BY")?>:</td>
		<td>
			<select name="filter_by">
				<option value="day"<?if ($filter_by=="day") echo " selected"?>><?echo GetMessage("SALE_S_DAY")?></option>
				<option value="weekday"<?if ($filter_by=="weekday") echo " selected"?>><?echo GetMessage("SALE_S_WEEKDAY")?></option>
				<option value="week"<?if ($filter_by=="week") echo " selected"?>><?echo GetMessage("SALE_S_WEEK")?></option>
				<option value="month"<?if ($filter_by=="month") echo " selected"?>><?echo GetMessage("SALE_S_MONTH")?></option>
				<option value="year"<?if ($filter_by=="year") echo " selected"?>><?echo GetMessage("SALE_S_YEAR")?></option>
			</select>
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
