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

$sTableID = "tbl_sale_stat";

$oSort = new CAdminSorting($sTableID, "DATE", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arSite = array();
$dbSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
while($arSites = $dbSite->GetNext())
{
	$arSite[$arSites["LID"]] = $arSites["NAME"];
}


$arFilterFields = array(
	"filter_date_from",
	"filter_date_to",
	"filter_by",
	"filter_site_id",
);

if($lAdmin->IsDefaultFilter())
{
	$filter_date_from_DAYS_TO_BACK = COption::GetOptionString("sale", "order_list_date", 30);
	$filter_date_from = GetTime(time()-86400*COption::GetOptionString("sale", "order_list_date", 30));
	$filter_by = "week";
	$filter_site_id = array_keys($arSite);
	$set_filter = "Y";
}

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if ($filter_date_from <> '')
{
	$arFilter["DATE_FROM"] = Trim($filter_date_from);
}

if ($filter_date_to <> '')
{
	if ($arDate = ParseDateTime($filter_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (mb_strlen($filter_date_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["DATE_TO"] = $filter_date_to;
	}
	else
	{
		$filter_date_to = "";
	}
}
if ($filter_by <> '')
	$filter_by = Trim($filter_by);
else
	$filter_by = "week";

if(empty($filter_site_id) || !is_array($filter_site_id))
	$filter_site_id = array_keys($arSite);

$arFilter["LID"] = $filter_site_id;

$arResult = Array();
$arCurUsed = Array();

$minDate = 0;
$maxDate = 0;

if($filter_date_from <> '')
	$minDate = MakeTimeStamp($filter_date_from);
if($filter_date_to <> '')
	$maxDate = MakeTimeStamp($filter_date_to);
else
	$maxDate = mktime(0, 0, 0, date("n"), date("j")+1, date("Y"));

if($filter_by == "week")
{
	$d = date("N", $minDate);
	$difference = 7 - $d;
	$minDate = AddToTimeStamp(Array("DD" => $difference), $minDate);
}

$arSelectedFields = Array("ID", "PAYED", "DATE_PAYED", "CANCELED", "DATE_CANCELED", "STATUS_ID", "DATE_STATUS", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "TAX_VALUE", "LID");
$dbOrder = CSaleOrder::GetList(Array(), $arFilter, false, false, $arSelectedFields);
while($arOrder = $dbOrder->Fetch())
{
	$tstm = MakeTimeStamp($arOrder["DATE_INSERT"]);
	if($filter_by == "day")
	{
		$key = ConvertDateTime($arOrder["DATE_INSERT"], FORMAT_DATE);
		if($tstm < $minDate || $minDate <= 0)
			$minDate = MakeTimeStamp($arOrder["DATE_INSERT"]);
	}
	elseif($filter_by == "week")
	{
		$d = date("N", $tstm);
		$difference = 7 - $d;
		$key = ConvertTimeStamp(AddToTimeStamp(Array("DD" => $difference), $tstm));
		if($tstm < $minDate || $minDate <= 0)
			$minDate = AddToTimeStamp(Array("DD" => $difference), $tstm);
	}
	elseif($filter_by == "month")
	{
		$key = ConvertTimeStamp(mktime(0, 0, 0, date("n", $tstm), 1, date("Y", $tstm)));

		if($tstm < $minDate || $minDate <= 0)
			$minDate = $tstm;
	}
	else
	{
		$key = ConvertTimeStamp(mktime(0, 0, 0, 1, 1, date("Y", $tstm)));
		if($tstm < $minDate || $minDate <= 0)
			$minDate = $tstm;
	}

	$arResult[$key]["DATE"] = $key;
	if($arResult[$key]["COUNT"] <= 0)
		$arResult[$key]["COUNT"] = 0;
	$arResult[$key]["COUNT"]++;

	if($arResult[$key]["PRICE"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE"][$arOrder["CURRENCY"]] = 0;
	$arResult[$key]["PRICE"][$arOrder["CURRENCY"]] += $arOrder["PRICE"];

	if($arResult[$key]["PRICE_DELIVERY"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_DELIVERY"][$arOrder["CURRENCY"]] = 0;
	$arResult[$key]["PRICE_DELIVERY"][$arOrder["CURRENCY"]] += $arOrder["PRICE_DELIVERY"];

	if($arResult[$key]["TAX_VALUE"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["TAX_VALUE"][$arOrder["CURRENCY"]] = 0;
	$arResult[$key]["TAX_VALUE"][$arOrder["CURRENCY"]] += $arOrder["TAX_VALUE"];

	/*
	if($arResult[$key]["DISCOUNT_VALUE"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["DISCOUNT_VALUE"][$arOrder["CURRENCY"]] = 0;
	$arResult[$key]["DISCOUNT_VALUE"][$arOrder["CURRENCY"]] += $arOrder["DISCOUNT_VALUE"];
	*/

	if($arResult[$key]["PRICE_PAYED"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_PAYED"][$arOrder["CURRENCY"]] = 0;
	if($arResult[$key]["PAYED"] <= 0)
		$arResult[$key]["PAYED"] = 0;
	if($arOrder["PAYED"] == "Y")
	{
		$arResult[$key]["PRICE_PAYED"][$arOrder["CURRENCY"]] += $arOrder["PRICE"];
		$arResult[$key]["PAYED"]++;
	}

	if($arResult[$key]["PRICE_CANCELED"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_CANCELED"][$arOrder["CURRENCY"]] = 0;
	if($arResult[$key]["CANCELED"] <= 0)
		$arResult[$key]["CANCELED"] = 0;
	if($arOrder["CANCELED"] == "Y")
	{
		$arResult[$key]["CANCELED"]++;
		$arResult[$key]["PRICE_CANCELED"][$arOrder["CURRENCY"]] += $arOrder["PRICE"];
	}

	if($arResult[$key]["PRICE_ALLOW_DELIVERY"][$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_ALLOW_DELIVERY"][$arOrder["CURRENCY"]] = 0;
	if($arResult[$key]["ALLOW_DELIVERY"] <= 0)
		$arResult[$key]["ALLOW_DELIVERY"] = 0;
	if($arOrder["ALLOW_DELIVERY"] == "Y")
	{
		$arResult[$key]["ALLOW_DELIVERY"]++;
		$arResult[$key]["PRICE_ALLOW_DELIVERY"][$arOrder["CURRENCY"]] += $arOrder["PRICE"];
	}

	if($arResult[$key]["STATUS_PRICE"][$arOrder["STATUS_ID"]."_".$arOrder["CURRENCY"]] <= 0)
		$arResult[$key]["STATUS_PRICE"][$arOrder["STATUS_ID"]."_".$arOrder["CURRENCY"]] = 0;
	if($arResult[$key]["STATUS"][$arOrder["STATUS_ID"]] <= 0)
		$arResult[$key]["STATUS"][$arOrder["STATUS_ID"]] = 0;
	$arResult[$key]["STATUS"][$arOrder["STATUS_ID"]]++;
	$arResult[$key]["STATUS_PRICE"][$arOrder["STATUS_ID"]."_".$arOrder["CURRENCY"]] += $arOrder["PRICE"];

	if(!in_array($arOrder["CURRENCY"], $arCurUsed))
		$arCurUsed[] = $arOrder["CURRENCY"];
}

	if($filter_by == "day" || $filter_by == "week" || $filter_by == "year")
	{
		if($filter_by == "day")
			$period = 60*60*24;
		elseif($filter_by == "week")
			$period = 60*60*24*7;
		else
			$period = 60*60*24*365;

		for($i=$minDate; $i<$maxDate; $i += $period)
		{
			$tm = ConvertTimeStamp($i);

			if($filter_by == "year")
				$tm = ConvertTimeStamp(mktime(0, 0, 0, 1, 1, date("Y", $i)));

			if(empty($arResult[$tm]))
				$arResult[$tm] = Array("DATE" => $tm);
		}
	}
	else // month
	{
		$minMonth = date("n", $minDate);
		$minYear = date("Y", $minDate);
		$maxMonth = date("n", $maxDate);
		$maxYear = date("Y", $maxDate);
		$m = ($maxYear-$minYear)*12 + ($maxMonth-$minMonth);
		for($i = 0; $i <= $m; $i++)
		{
			$tm = ConvertTimeStamp(mktime(0, 0, 0, $minMonth + $i, 1, $minYear));
			if(empty($arResult[$tm]))
				$arResult[$tm] = Array("DATE" => $tm);
		}

	}

function bxStatSort($a, $b)
{
	global $by, $order;
	$by = toUpper($by);
	$order = toUpper($order);

	if(in_array($by, Array("COUNT", "PAYED", "ALLOW_DELIVERY", "CANCELED")))
	{
		if(DoubleVal($a[$by]) == DoubleVal($b[$by]))
			return 0;
		elseif(DoubleVal($a[$by]) > DoubleVal($b[$by]))
			return ($order == "DESC") ? -1 : 1;
		else
			return ($order == "DESC") ? 1 : -1;
	}
	elseif($by == "PAYED_PROC" || $by == "ALLOW_DELIVERY_PROC" || $by == "CANCELED_PROC")
	{
		if($by == "PAYED_PROC")
			$k = "PAYED";
		else
			$k = "ALLOW_DELIVERY";
		if(intval($a[$k]) == intval($b[$k]) && intval($b[$k]) == 0)
			return 0;
		elseif(intval($a[$k]) > 0 && intval($b[$k]) > 0)
		{
			if(DoubleVal($a[$k]*100/$a["COUNT"]) > DoubleVal($b[$k]*100/$b["COUNT"]))
				return ($order == "DESC") ? -1 : 1;
			elseif(DoubleVal($a[$k]*100/$a["COUNT"]) < DoubleVal($b[$k]*100/$b["COUNT"]))
				return ($order == "DESC") ? 1 : -1;
			else
				return 0;
		}
		elseif(intval($a[$k]) > 0 && intval($b[$k]) <= 0)
			return ($order == "DESC") ? -1 : 1;
		elseif(intval($a[$k]) <= 0 && intval($b[$k]) > 0)
			return ($order == "DESC") ? 1 : -1;
		else
			return 0;
	}
	elseif(
		mb_strpos($by, "STATUS_") !== false || mb_strpos($by, "STATUS_PRICE_") !== false ||
		mb_strpos($by, "PRICE_DELIVERY_") !== false || mb_strpos($by, "TAX_VALUE_") !== false || mb_strpos($by, "DISCOUNT_VALUE_") !== false ||
		mb_strpos($by, "PRICE_") !== false || mb_strpos($by, "PRICE_PAYED_") !== false || mb_strpos($by, "PRICE_ALLOW_DELIVERY_") !== false || mb_strpos($by, "PRICE_CANCELED_") !== false
		)
	{
		if(mb_strpos($by, "STATUS_PRICE_") !== false)
		{
			$k = mb_substr($by, mb_strlen("STATUS_PRICE_"));
			$v = "STATUS_PRICE";
		}
		elseif(mb_strpos($by, "STATUS_") !== false)
		{
			$k = mb_substr($by, mb_strlen("STATUS_"));
			$v = "STATUS";
		}
		elseif(mb_strpos($by, "PRICE_DELIVERY_") !== false)
		{
			$k = mb_substr($by, mb_strlen("PRICE_DELIVERY_"));
			$v = "PRICE_DELIVERY";
		}
		elseif(mb_strpos($by, "TAX_VALUE_") !== false)
		{
			$k = mb_substr($by, mb_strlen("TAX_VALUE_"));
			$v = "TAX_VALUE";
		}
		elseif(mb_strpos($by, "DISCOUNT_VALUE_") !== false)
		{
			$k = mb_substr($by, mb_strlen("DISCOUNT_VALUE_"));
			$v = "DISCOUNT_VALUE";
		}
		elseif(mb_strpos($by, "PRICE_PAYED_") !== false)
		{
			$k = mb_substr($by, mb_strlen("PRICE_PAYED_"));
			$v = "PRICE_PAYED";
		}
		elseif(mb_strpos($by, "PRICE_CANCELED_") !== false)
		{
			$k = mb_substr($by, mb_strlen("PRICE_CANCELED_"));
			$v = "PRICE_CANCELED";
		}
		elseif(mb_strpos($by, "PRICE_ALLOW_DELIVERY_") !== false)
		{
			$k = mb_substr($by, mb_strlen("PRICE_ALLOW_DELIVERY_"));
			$v = "PRICE_ALLOW_DELIVERY";
		}
		else
		{
			$k = mb_substr($by, mb_strlen("PRICE_"));
			$v = "PRICE";
		}
		if(intval($a[$v][$k]) == intval($b[$v][$k]))
			return 0;
		elseif(intval($a[$v][$k]) > intval($b[$v][$k]))
			return ($order == "DESC") ? -1 : 1;
		else
			return ($order == "DESC") ? 1 : -1;
	}
	else
	{
		if(MakeTimeStamp($a["DATE"]) == MakeTimeStamp($b["DATE"]))
			return 0;
		elseif(MakeTimeStamp($a["DATE"]) > MakeTimeStamp($b["DATE"]))
			return ($order == "DESC") ? -1 : 1;
		else
			return ($order == "DESC") ? 1 : -1;
	}
}
uasort($arResult, "bxStatSort");

$arHeaders = array(
	array("id"=>"DATE", "content"=>GetMessage("SALE_DATE"), "sort"=>"DATE", "default"=>true),
	array("id"=>"COUNT","content"=>GetMessage("SALE_COUNT"), "sort"=>"COUNT", "default"=>true, "align" => "right"),
	array("id"=>"PAYED", "content"=>GetMessage("SALE_PAYED"),  "sort"=>"PAYED", "default"=>true, "align" => "right"),
	array("id"=>"PAYED_PROC", "content"=>GetMessage("SALE_PAYED_PROC"),  "sort"=>"PAYED_PROC", "default"=>true, "align" => "right"),
	array("id"=>"ALLOW_DELIVERY", "content"=>GetMessage("SALE_ALLOW_DELIVERY"),  "sort"=>"ALLOW_DELIVERY", "default"=>true, "align" => "right"),
	array("id"=>"ALLOW_DELIVERY_PROC", "content"=>GetMessage("SALE_ALLOW_DELIVERY_PROC"),  "sort"=>"ALLOW_DELIVERY_PROC", "default"=>true, "align" => "right"),
	array("id"=>"CANCELED", "content"=>GetMessage("SALE_CANCELED"),  "sort"=>"CANCELED", "default"=>true, "align" => "right"),
	array("id"=>"CANCELED_PROC", "content"=>GetMessage("SALE_CANCELED_PROC"),  "sort"=>"CANCELED_PROC", "default"=>true, "align" => "right"),
);

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
	$arHeaders[] = array("id"=>"STATUS_".$arStatusList["ID"], "content"=>$arStatusList["NAME"]." ".GetMessage("SALE_QUANTITY"), "sort"=>"STATUS_".$arStatusList["ID"], "default"=>true, "align" => "right");
}

$arCurrency = Array();
$dbCur = CCurrency::GetList("name", "asc", LANGUAGE_ID);
while($arCur = $dbCur->GetNext())
{
	$arCurrency[$arCur["CURRENCY"]] = $arCur["FULL_NAME"];
	if(in_array($arCur["CURRENCY"], $arCurUsed))
	{
		$arHeaders[] = array("id"=>"PRICE_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_PRICE", Array("#CURRENCY#" => $arCur["FULL_NAME"])), "sort"=>"PRICE_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
		$arHeaders[] = array("id"=>"PRICE_DELIVERY_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_PRICE_DELIVERY", Array("#CURRENCY#" => $arCur["FULL_NAME"])), "sort"=>"PRICE_DELIVERY_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
		$arHeaders[] = array("id"=>"TAX_VALUE_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_TAX_VALUE", Array("#CURRENCY#" => $arCur["FULL_NAME"])), "sort"=>"TAX_VALUE_".$arCur["CURRENCY"], "default"=>true, "align" => "right");

		$arHeaders[] = array("id"=>"PRICE_PAYED_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_PRICE_PAYED", Array("#CURRENCY#" => $arCur["FULL_NAME"])), "sort"=>"PRICE_PAYED_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
		$arHeaders[] = array("id"=>"PRICE_ALLOW_DELIVERY_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_PRICE_ALLOW_DELIVERY", Array("#CURRENCY#" => $arCur["FULL_NAME"])), "sort"=>"PRICE_ALLOW_DELIVERY_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
		$arHeaders[] = array("id"=>"PRICE_CANCELED_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_PRICE_CANCELED", Array("#CURRENCY#" => $arCur["FULL_NAME"])), "sort"=>"PRICE_CANCELED_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
		foreach($arStatus as $k => $v)
		{
			$arHeaders[] = array("id"=>"STATUS_PRICE_".$k."_".$arCur["CURRENCY"], "content"=>$v." (".$arCur["FULL_NAME"].")", "sort"=>"STATUS_PRICE_".$k."_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
		}

	}
}

$lAdmin->AddHeaders($arHeaders);
$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$dbResult = new CDBResult;
$dbResult->InitFromArray($arResult);

$dbResult = new CAdminResult($dbResult, $sTableID);

$dbResult->NavStart();
$lAdmin->NavText($dbResult->GetNavPrint(""));

while ($arResult = $dbResult->GetNext())
{
	$row =& $lAdmin->AddRow($arResult["DATE"], $arResult);

	$row->AddViewField("DATE", $arResult["DATE"]);
	if($arResult["COUNT"] > 0)
	{
		$row->AddViewField("COUNT", $arResult["COUNT"]);
		$row->AddViewField("PAYED", $arResult["PAYED"]);
		$row->AddViewField("ALLOW_DELIVERY", $arResult["ALLOW_DELIVERY"]);
		$row->AddViewField("CANCELED", $arResult["CANCELED"]);

		$row->AddViewField("PAYED_PROC", roundEx($arResult["PAYED"]*100/$arResult["COUNT"], 0));
		$row->AddViewField("ALLOW_DELIVERY_PROC", roundEx($arResult["ALLOW_DELIVERY"]*100/$arResult["COUNT"], 0));
		$row->AddViewField("CANCELED_PROC", roundEx($arResult["CANCELED"]*100/$arResult["COUNT"], 0));
		foreach($arStatus as $k => $v)
			$row->AddViewField("STATUS_".$k, intval($arResult["STATUS"][$k]));
		foreach($arCurUsed as $k)
		{
			$row->AddViewField("PRICE_".$k, number_format(roundEx($arResult["PRICE"][$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));
			$row->AddViewField("PRICE_DELIVERY_".$k, number_format(roundEx($arResult["PRICE_DELIVERY"][$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));
			$row->AddViewField("TAX_VALUE_".$k, number_format(roundEx($arResult["TAX_VALUE"][$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));
			//$row->AddViewField("DISCOUNT_VALUE_".$k, roundEx($arResult["DISCOUNT_VALUE"][$k], SALE_VALUE_PRECISION));
			$row->AddViewField("PRICE_PAYED_".$k, number_format(roundEx($arResult["PRICE_PAYED"][$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));
			$row->AddViewField("PRICE_CANCELED_".$k, number_format(roundEx($arResult["PRICE_CANCELED"][$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));
			$row->AddViewField("PRICE_ALLOW_DELIVERY_".$k, number_format(roundEx($arResult["PRICE_ALLOW_DELIVERY"][$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));

			foreach($arStatus as $k1 => $v1)
				$row->AddViewField("STATUS_PRICE_".$k1."_".$k, number_format(roundEx($arResult["STATUS_PRICE"][$k1."_".$k], SALE_VALUE_PRECISION), SALE_VALUE_PRECISION, '.', ''));

		}
	}
	else
	{
		$row->AddViewField("COUNT", 0);
		$row->AddViewField("PAYED", 0);
		$row->AddViewField("ALLOW_DELIVERY", 0);
		$row->AddViewField("CANCELED", 0);
		$row->AddViewField("PAYED_PROC", 0);
		$row->AddViewField("ALLOW_DELIVERY_PROC", 0);
		$row->AddViewField("CANCELED_PROC", 0);
		foreach($arStatus as $k => $v)
			$row->AddViewField("STATUS_".$k, 0);
		foreach($arCurUsed as $k)
		{
			$row->AddViewField("PRICE_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
			$row->AddViewField("PRICE_DELIVERY_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
			$row->AddViewField("TAX_VALUE_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
			//$row->AddViewField("DISCOUNT_VALUE_".$k, 0);
			$row->AddViewField("PRICE_PAYED_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
			$row->AddViewField("PRICE_ALLOW_DELIVERY_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
			$row->AddViewField("PRICE_CANCELED_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
			foreach($arStatus as $k1 => $v1)
				$row->AddViewField("STATUS_PRICE_".$k1."_".$k, number_format(0, SALE_VALUE_PRECISION, ".", ""));
		}
	}
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResult->SelectedRowsCount()
		),
	)
);

$lAdmin->AddAdminContextMenu();

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
	GetMessage("SALE_S_DATE"),
	GetMessage("SALE_S_SITE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><b><?echo GetMessage("SALE_S_BY")?>:</b></td>
		<td>
			<select name="filter_by">
				<option value="day"<?if ($filter_by=="day") echo " selected"?>><?echo GetMessage("SALE_S_DAY")?></option>
				<option value="week"<?if ($filter_by=="week") echo " selected"?>><?echo GetMessage("SALE_S_WEEK")?></option>
				<option value="month"<?if ($filter_by=="month") echo " selected"?>><?echo GetMessage("SALE_S_MONTH")?></option>
				<option value="year"<?if ($filter_by=="year") echo " selected"?>><?echo GetMessage("SALE_S_YEAR")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_S_DATE");?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_from", $filter_date_from, "filter_date_to", $filter_date_to, "find_form", "Y")?>
		</td>
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
<?
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