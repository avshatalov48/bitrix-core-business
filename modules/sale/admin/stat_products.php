<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

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

$sTableID = "tbl_sale_stat_products";

$oSort = new CAdminSorting($sTableID, "ORDER_QUANTITY", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_date_from",
	"filter_date_to",
	"filter_site_id",
);
$arSite = array();
$dbSite = CSite::GetList($by1="sort", $order1="desc", Array("ACTIVE" => "Y"));
while($arSites = $dbSite->GetNext())
{
	$arSite[$arSites["LID"]] = $arSites["NAME"];
}

if($lAdmin->IsDefaultFilter())
{
	$filter_date_from_DAYS_TO_BACK = COption::GetOptionString("sale", "order_list_date", 30);
	$filter_date_from = GetTime(time()-86400*COption::GetOptionString("sale", "order_list_date", 30));
	$filter_site_id = array_keys($arSite);
	$set_filter = "Y";
}

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (strlen($filter_date_from)>0) $arFilter[">=DATE_INSERT"] = Trim($filter_date_from);
if (strlen($filter_date_to)>0)
{
	if ($arDate = ParseDateTime($filter_date_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (StrLen($filter_date_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["<=DATE_INSERT"] = $filter_date_to;
	}
	else
	{
		$filter_date_to = "";
	}
}
if(empty($filter_site_id) || !is_array($filter_site_id))
	$filter_site_id = array_keys($arSite);

$arFilter["LID"] = $filter_site_id;

$arResult = Array();
$arCurUsed = Array();

$minDate = 0;
$maxDate = 0;

if(strlen($filter_date_from) > 0)
	$minDate = MakeTimeStamp($filter_date_from);
if(strlen($filter_date_to) > 0)
	$maxDate = MakeTimeStamp($filter_date_to);
else
	$maxDate = mktime(0, 0, 0, date("n"), date("j")+1, date("Y"));

$arSelectedFields = Array("ID", "ORDER_ID", "PRODUCT_ID", "PRICE", "CURRENCY", "DATE_INSERT", "QUANTITY", "DELAY", "NAME", "CAN_BUY", "MODULE", "CATALOG_XML_ID", "PRODUCT_XML_ID", "ORDER_PAYED", "ORDER_ALLOW_DELIVERY", "FUSER_ID", "LID", "ORDER_PRICE");
$dbBasket = CSaleBasket::GetList(Array(), $arFilter, false, false, $arSelectedFields);
while($arBasket = $dbBasket->Fetch())
{
	$key = $arBasket["PRODUCT_ID"];

	$arResult[$key]["PRODUCT_ID"] = $key;
	$arResult[$key]["NAME"] = $arBasket["NAME"];

	if($arResult[$key]["COUNT"] <= 0)
		$arResult[$key]["COUNT"] = 0;
	if($arResult[$key]["ORDER_COUNT"] <= 0)
		$arResult[$key]["ORDER_COUNT"] = 0;
	if($arResult[$key]["QUANTITY"] <= 0)
		$arResult[$key]["QUANTITY"] = 0;
	if($arResult[$key]["ORDER_QUANTITY"] <= 0)
		$arResult[$key]["ORDER_QUANTITY"] = 0;
	if($arResult[$key]["BASKET_QUANTITY"] <= 0)
		$arResult[$key]["BASKET_QUANTITY"] = 0;
	if($arResult[$key]["PAYED"] <= 0)
		$arResult[$key]["PAYED"] = 0;
	if($arResult[$key]["ALLOW_DELIVERY"] <= 0)
		$arResult[$key]["ALLOW_DELIVERY"] = 0;
	if($arResult[$key]["DELAY"] <= 0)
		$arResult[$key]["DELAY"] = 0;
	if($arResult[$key]["PRICE"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE"][$arBasket["CURRENCY"]] = 0;
	if($arResult[$key]["PRICE_PRODUCT"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_PRODUCT"][$arBasket["CURRENCY"]] = 0;
	if($arResult[$key]["PRICE_PRODUCT_PAYED"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_PRODUCT_PAYED"][$arBasket["CURRENCY"]] = 0;
	if($arResult[$key]["PRICE_PRODUCT_ALLOW_DELIVERY"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_PRODUCT_ALLOW_DELIVERY"][$arBasket["CURRENCY"]] = 0;
	if($arResult[$key]["PRICE_MIN"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_MIN"][$arBasket["CURRENCY"]] = $arBasket["PRICE"];
	if($arResult[$key]["PRICE_MAX"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_MAX"][$arBasket["CURRENCY"]] = $arBasket["PRICE"];
	if($arResult[$key]["PRICE_ORDER"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_ORDER"][$arBasket["CURRENCY"]] = 0;
	if($arResult[$key]["PRICE_ORDER_PAYED"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_ORDER_PAYED"][$arBasket["CURRENCY"]] = 0;
	if($arResult[$key]["PRICE_ORDER_ALLOW_DELIVERY"][$arBasket["CURRENCY"]] <= 0)
		$arResult[$key]["PRICE_ORDER_ALLOW_DELIVERY"][$arBasket["CURRENCY"]] = 0;


	if(IntVal($arBasket["ORDER_ID"]) <= 0)
		$arResult[$key]["COUNT"]++;
	else
		$arResult[$key]["ORDER_COUNT"]++;

	if(IntVal($arBasket["ORDER_ID"]) > 0)
	{
		$arResult[$key]["ORDER_QUANTITY"] += $arBasket["QUANTITY"];
		$arResult[$key]["PRICE_PRODUCT"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);
		$arResult[$key]["PRICE_ORDER"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);

	}
	else
		$arResult[$key]["BASKET_QUANTITY"] += $arBasket["QUANTITY"];
	$arResult[$key]["QUANTITY"] += $arBasket["QUANTITY"];

	$arResult[$key]["PRICE"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);

	if($arBasket["ORDER_PAYED"] == "Y")
	{
		$arResult[$key]["PAYED"]++;
		$arResult[$key]["PRICE_PRODUCT_PAYED"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);
		$arResult[$key]["PRICE_ORDER_PAYED"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);
	}

	if($arBasket["ORDER_ALLOW_DELIVERY"] == "Y")
	{
		$arResult[$key]["ALLOW_DELIVERY"]++;
		$arResult[$key]["PRICE_PRODUCT_ALLOW_DELIVERY"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);
		$arResult[$key]["PRICE_ORDER_ALLOW_DELIVERY"][$arBasket["CURRENCY"]] += roundEx($arBasket["PRICE"]*$arBasket["QUANTITY"], SALE_VALUE_PRECISION);
	}

	if($arBasket["DELAY"] == "Y")
		$arResult[$key]["DELAY"] += $arBasket["QUANTITY"];

	if($arResult[$key]["PRICE_MIN"][$arBasket["CURRENCY"]] > $arBasket["PRICE"])
		$arResult[$key]["PRICE_MIN"][$arBasket["CURRENCY"]] = $arBasket["PRICE"];
	if($arResult[$key]["PRICE_MAX"][$arBasket["CURRENCY"]] < $arBasket["PRICE"])
		$arResult[$key]["PRICE_MAX"][$arBasket["CURRENCY"]] = $arBasket["PRICE"];

	if(!in_array($arBasket["CURRENCY"], $arCurUsed))
		$arCurUsed[] = $arBasket["CURRENCY"];
}

$arPrices = Array("PRICE", "PRICE_ORDER", "PRICE_ORDER_PAYED", "PRICE_ORDER_ALLOW_DELIVERY", "PRICE_PRODUCT", "PRICE_PRODUCT_PAYED", "PRICE_PRODUCT_ALLOW_DELIVERY", "PRICE_MIN", "PRICE_MAX");

function bxStatSort($a, $b)
{
	global $by, $order, $arPrices, $arCurUsed;
	$by = toUpper($by);
	$order = toUpper($order);

	if(in_array($by, Array("PRODUCT_ID", "PAYED", "ALLOW_DELIVERY", "ORDER_COUNT", "COUNT", "QUANTITY", "DELAY", "ORDER_QUANTITY", "BASKET_QUANTITY")))
	{
		if(DoubleVal($a[$by]) == DoubleVal($b[$by]))
			return 0;
		elseif(DoubleVal($a[$by]) > DoubleVal($b[$by]))
			return ($order == "DESC") ? -1 : 1;
		else
			return ($order == "DESC") ? 1 : -1;
	}
	elseif($by == "PAYED_PROC" || $by == "ALLOW_DELIVERY_PROC")
	{
		if($by == "PAYED_PROC")
			$k = "PAYED";
		else
			$k = "ALLOW_DELIVERY";
		if(IntVal($a[$k]) == IntVal($b[$k]) && IntVal($b[$k]) == 0)
			return 0;
		elseif(IntVal($a[$k]) > 0 && IntVal($b[$k]) > 0)
		{
			if(DoubleVal($a[$k]*100/$a["ORDER_COUNT"]) > DoubleVal($b[$k]*100/$b["ORDER_COUNT"]))
				return ($order == "DESC") ? -1 : 1;
			elseif(DoubleVal($a[$k]*100/$a["ORDER_COUNT"]) < DoubleVal($b[$k]*100/$b["ORDER_COUNT"]))
				return ($order == "DESC") ? 1 : -1;
			else
				return 0;
		}
		elseif(IntVal($a[$k]) > 0 && IntVal($b[$k]) <= 0)
			return ($order == "DESC") ? -1 : 1;
		elseif(IntVal($a[$k]) <= 0 && IntVal($b[$k]) > 0)
			return ($order == "DESC") ? 1 : -1;
		else
			return 0;
	}
	else//if(strpos($by, "PRICE_") !== false)
	{
		$bFound = false;
		foreach($arCurUsed as $cur)
		{
			foreach($arPrices as $price)
			{
				if($by == $price."_".$cur)
				{
					$bFound = true;
					$k = $cur;
					$v = $price;
					break;
				}
			}
			if($bFound)
				break;
		}

		if($bFound)
		{
			if(IntVal($a[$v][$k]) == IntVal($b[$v][$k]))
				return 0;
			elseif(IntVal($a[$v][$k]) > IntVal($b[$v][$k]))
				return ($order == "DESC") ? -1 : 1;
			else
				return ($order == "DESC") ? 1 : -1;
		}
		else
		{
			if($a[$by] == $b[$by])
				return 0;
			elseif($a[$by] > $b[$by])
				return ($order == "DESC") ? -1 : 1;
			else
				return ($order == "DESC") ? 1 : -1;
		}
	}
}
uasort($arResult, "bxStatSort");

$arHeaders = array(
	array("id"=>"PRODUCT_ID", "content"=>GetMessage("SALE_PRODUCT_ID"), "sort"=>"PRODUCT_ID", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("SALE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"QUANTITY", "content"=>GetMessage("SALE_QUANTITY"), "sort"=>"QUANTITY", "default"=>true, "align" => "right"),
	array("id"=>"COUNT", "content"=>GetMessage("SALE_COUNT"), "sort"=>"COUNT", "default"=>true, "align" => "right"),
	array("id"=>"ORDER_QUANTITY", "content"=>GetMessage("SALE_ORDER_QUANTITY"), "sort"=>"ORDER_QUANTITY", "default"=>true, "align" => "right"),
	array("id"=>"ORDER_COUNT", "content"=>GetMessage("SALE_ORDER_COUNT"), "sort"=>"ORDER_COUNT", "default"=>true, "align" => "right"),
	array("id"=>"PAYED", "content"=>GetMessage("SALE_PAYED"), "sort"=>"PAYED", "default"=>true, "align" => "right"),
	array("id"=>"PAYED_PROC", "content"=>GetMessage("SALE_PAYED_PROC"), "sort"=>"PAYED_PROC", "default"=>true, "align" => "right"),
	array("id"=>"ALLOW_DELIVERY", "content"=>GetMessage("SALE_ALLOW_DELIVERY"), "sort"=>"ALLOW_DELIVERY", "default"=>true, "align" => "right"),
	array("id"=>"ALLOW_DELIVERY_PROC", "content"=>GetMessage("SALE_ALLOW_DELIVERY_PROC"), "sort"=>"ALLOW_DELIVERY_PROC", "default"=>true, "align" => "right"),
	array("id"=>"DELAY", "content"=>GetMessage("SALE_DELAY"), "sort"=>"DELAY", "default"=>true, "align" => "right"),
	array("id"=>"BASKET_QUANTITY", "content"=>GetMessage("SALE_BASKET_QUANTITY"), "sort"=>"BASKET_QUANTITY", "default"=>true, "align" => "right"),
);

$arCurrency = Array();

$dbCur = CCurrency::GetList(($b1="name"), ($order1="asc"), LANGUAGE_ID);
while($arCur = $dbCur->Fetch())
{
	$arCurrency[$arCur["CURRENCY"]] = htmlspecialcharsEx($arCur["FULL_NAME"]);
	if(in_array($arCur["CURRENCY"], $arCurUsed))
	{
		foreach($arPrices as $v)
			$arHeaders[] = array("id"=>$v."_".$arCur["CURRENCY"], "content"=>GetMessage("SALE_".$v, Array("#CURRENCY#" => htmlspecialcharsEx($arCur["FULL_NAME"]))), "sort"=>$v."_".$arCur["CURRENCY"], "default"=>true, "align" => "right");
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
	$row =& $lAdmin->AddRow($arResult["PRODUCT_ID"], $arResult);

	$row->AddViewField("PRODUCT_ID", $arResult["PRODUCT_ID"]);
	$row->AddViewField("NAME", $arResult["NAME"]);
	$row->AddViewField("COUNT", $arResult["COUNT"]);
	$row->AddViewField("ORDER_COUNT", $arResult["ORDER_COUNT"]);
	$row->AddViewField("QUANTITY", $arResult["QUANTITY"]);
	$row->AddViewField("ORDER_QUANTITY", $arResult["ORDER_QUANTITY"]);
	$row->AddViewField("BASKET_QUANTITY", $arResult["BASKET_QUANTITY"]);
	$row->AddViewField("PAYED", $arResult["PAYED"]);
	if(IntVal($arResult["ORDER_COUNT"]) > 0)
	{
		$row->AddViewField("PAYED_PROC", roundEx($arResult["PAYED"]*100/$arResult["ORDER_COUNT"], 0));
		$row->AddViewField("ALLOW_DELIVERY_PROC", roundEx($arResult["ALLOW_DELIVERY"]*100/$arResult["ORDER_COUNT"], 0));
	}
	else
	{
		$row->AddViewField("PAYED_PROC", 0);
		$row->AddViewField("ALLOW_DELIVERY_PROC", 0);
	}
	$row->AddViewField("ALLOW_DELIVERY", $arResult["ALLOW_DELIVERY"]);
	$row->AddViewField("DELAY", $arResult["DELAY"]);
	foreach($arCurUsed as $currency)
	{
		foreach($arPrices as $price)
		{
			$row->AddViewField($price."_".$currency, \Bitrix\Sale\PriceMaths::roundByFormatCurrency($arResult[$price][$currency], $currency));
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
	GetMessage("SALE_S_SITE")
	)
);

$oFilter->Begin();
?>
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