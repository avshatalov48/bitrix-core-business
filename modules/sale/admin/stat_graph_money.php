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

$arSite = array();
$dbSite = CSite::GetList($by1="sort", $order1="desc", Array("ACTIVE" => "Y"));
while($arSites = $dbSite->GetNext())
{
	$arSite[$arSites["LID"]] = $arSites["NAME"];
	$arRes = CSaleLang::GetByID($arSites["LID"]);
	if($arRes)
		$arAvCur[$arSites["LID"]] = $arRes["CURRENCY"];
	else
		$arAvCur[$arSites["LID"]] = COption::GetOptionString("sale", "default_currency", $CURRENCY_DEFAULT);
}
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

$sTableID = "tbl_sale_graph_money";
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
	"filter_site_id",
	"find_all",
	"find_payed",
	"find_allow_delivery",
	"find_canceled",
);
foreach($arStatus as $k1 => $v)
	$arFilterFields[] = "find_status_".$k1;

if(empty($filter_site_id) || !is_array($filter_site_id))
	$filter_site_id = array_keys($arSite);

//foreach($arCurrency as $k => $v)
	//$arFilterFields[] = "find_".$k;

$arCurrency = Array();
$arCurrencyInfo = Array();
$dbCur = CCurrency::GetList(($b="sort"), ($order1="asc"), LANGUAGE_ID);
while($arCur = $dbCur->GetNext())
{
	$arCurrencyInfo[$arCur["CURRENCY"]] = $arCur["FULL_NAME"];
	$arFilterFields[] = "find_".$arCur["CURRENCY"];

	$arFilterFields[] = "find_all_".$arCur["CURRENCY"];
	$arFilterFields[] = "find_payed_".$arCur["CURRENCY"];
	$arFilterFields[] = "find_allow_delivery_".$arCur["CURRENCY"];
	$arFilterFields[] = "find_canceled_".$arCur["CURRENCY"];
	foreach($arStatus as $k1 => $v)
		$arFilterFields[] = "find_status_".$k1."_".$arCur["CURRENCY"];

}

if (!empty($filter_find) && is_array($filter_find))
{
	foreach ($filter_find as $filterLine)
	{
		${$filterLine} = 'Y';
	}
}


foreach($filter_site_id as $v)
{
	if($arAvCur[$v] <> '' && $arCurrencyInfo[$arAvCur[$v]] <> '')
	{
		$arCurrency[$arAvCur[$v]] = $arCurrencyInfo[$arAvCur[$v]];

		if($find_all == "Y")
			${"find_all_".$arAvCur[$v]} = "Y";
		if($find_payed == "Y")
			${"find_payed_".$arAvCur[$v]} = "Y";
		if($find_allow_delivery == "Y")
			${"find_allow_delivery_".$arAvCur[$v]} = "Y";
		if($find_canceled == "Y")
			${"find_canceled_".$arAvCur[$v]} = "Y";

		foreach($arStatus as $k1 => $v1)
		{
			if(${"find_status_".$k1} == "Y")
				${"find_status_".$k1."_".$arAvCur[$v]} = "Y";
		}

	}
}


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
		<img class="graph" src="/bitrix/admin/sale_stat_graph.php?<?=GetFilterParams($arFilterFields)?>&amp;width=<?=$width?>&amp;height=<?=$height?>&amp;lang=<?=LANG?>&amp;rand=<?=rand()?>&amp;mode=money" width="<?=$width?>" height="<?=$height?>">
	</td>
</tr>
<tr>
	<td valign="top">
	<table cellpadding="2" cellspacing="0" border="0" class="legend">
		<?
		$i = 0;
		foreach($arCurrencyInfo as $k => $v)
		{
			if (${"find_all_".$k}=="Y"):?>
			<tr>
				<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[$i]?>" width="45" height="2"></td>
				<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_COUNT")?> (<?=$v?>)</td>
			</tr>
			<?endif;
			$i++;
			if (${"find_payed_".$k}=="Y"):?>
			<tr>
				<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[$i]?>" width="45" height="2"></td>
				<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_PAYED")?> (<?=$v?>)</td>
			</tr>
			<?endif;
			$i++;
			if (${"find_allow_delivery_".$k}=="Y"):?>
			<tr>
				<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[$i]?>" width="45" height="2"></td>
				<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_ALLOW_DELIVERY")?> (<?=$v?>)</td>
			</tr>
			<?endif;
			$i++;
			if (${"find_canceled_".$k}=="Y"):?>
			<tr>
				<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[$i]?>" width="45" height="2"></td>
				<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("SALE_CANCELED")?> (<?=$v?>)</td>
			</tr>
			<?endif;?>
			<?
			$i++;
			foreach($arStatus as $k1 => $v1)
			{
				if (${"find_status_".$k1."_".$k} == "Y"):?>
				<tr>
					<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=<?=$arColor[$i]?>" width="45" height="2"></td>
					<td nowrap><img src="/bitrix/images/1.gif" width="3" height="1"><?=$v1?> (<?=$v?>)</td>
				</tr>
				<?endif;
				$i++;
			}
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
		<select name="filter_find[]" multiple>
			<option value="find_all"<?if ($find_all=="Y") echo " selected"?>><?echo GetMessage("SALE_COUNT")?></option>
			<option value="find_payed"<?if ($find_payed=="Y") echo " selected"?>><?echo GetMessage("SALE_PAYED")?></option>
			<option value="find_allow_delivery"<?if ($find_allow_delivery=="Y") echo " selected"?>><?echo GetMessage("SALE_ALLOW_DELIVERY")?></option>
			<option value="find_canceled"<?if ($find_canceled=="Y") echo " selected"?>><?echo GetMessage("SALE_CANCELED")?></option>
			<?
				foreach($arStatus as $id => $name)
				{
					?>
					<option value="find_status_<?=$id?>" <?if (${"find_status_$id"}=="Y") echo " selected"?>><?=htmlspecialcharsbx($name)?></option>
					<?
				}
			?>
		</select>
	</td>
</tr>

	<tr>
		<td><?echo GetMessage("SALE_S_BY")?>:</td>
		<td>
			<select name="filter_by">
				<option value="day"<?if ($filter_by=="day") echo " selected"?>><?echo GetMessage("SALE_S_DAY")?></option>
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
