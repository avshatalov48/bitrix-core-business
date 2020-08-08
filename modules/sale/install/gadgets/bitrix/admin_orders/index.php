<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Currency,
	Bitrix\Sale;

if(!CModule::IncludeModule("sale") || !CModule::IncludeModule("currency"))
	return false;

global $USER;

$saleModulePermissions = $GLOBALS["APPLICATION"]->GetGroupRight("sale");

if (intval($arGadgetParams["ITEMS_COUNT"]) <= 0 || intval($arGadgetParams["ITEMS_COUNT"]) > 40)
	$arGadgetParams["ITEMS_COUNT"] = 10;

if ($arGadgetParams["SITE_ID"] <> '')
{
	$arGadgetParams["SITE_CURRENCY"] = Sale\Internals\SiteCurrencyTable::getSiteCurrency($arGadgetParams["SITE_ID"]);
	if ($arGadgetParams["TITLE_STD"] == '')
	{
		$rsSites = CSite::GetByID($arGadgetParams["SITE_ID"]);
		if ($arSite = $rsSites->GetNext())
			$arGadget["TITLE"] .= " / [".$arSite["ID"]."] ".$arSite["NAME"];
	}
}
else
	$arGadgetParams["SITE_CURRENCY"] = Currency\CurrencyManager::getBaseCurrency();

$arGadgetParams["RND_STRING"] = randString(8);

$arOrderStats = Array();
$arOrders = Array();
$arCurUsed = Array();

$userGroups = $USER->GetUserGroupArray();

if ($saleModulePermissions != "W")
{
	$accessibleSites = array();
	$resAccessibleSites = CSaleGroupAccessToSite::GetList(
		array(),
		array("GROUP_ID" => $userGroups),
		false,
		false,
		array("SITE_ID")
	);
	while ($accessibleSiteData = $resAccessibleSites->Fetch())
	{
		if(!in_array($accessibleSiteData["SITE_ID"], $accessibleSites))
		{
			$accessibleSites[] = $accessibleSiteData["SITE_ID"];
		}
	}


	if (strval($arGadgetParams["SITE_ID"]) > 0)
	{
		if(in_array($arGadgetParams["SITE_ID"], $accessibleSites))
		{
			$arFilterLID['=LID'][] = $accessibleSiteData["SITE_ID"];
		}
		else
		{
			$arFilterLID['=LID'] = false;
		}
	}
	else
	{
		$arFilterLID['=LID'] = (count($accessibleSites) > 0 ? $accessibleSites : false);
	}


}
else
{
	if ($arGadgetParams["SITE_ID"] <> '')
	{
		$arFilterLID = array("=LID" => $arGadgetParams["SITE_ID"]);
	}
	else
	{
		$arFilterLID = array();
	}
}

if ($saleModulePermissions != "W")
{
	$allowedStatusesView = \Bitrix\Sale\OrderStatus::getStatusesGroupCanDoOperations($userGroups, array('view'));
	$arFilterPerms["=STATUS_ID"] = $allowedStatusesView;
}
else
{
	$arFilterPerms = array();
}

$BeforeLastMonth_minDate = ConvertTimeStamp(AddToTimeStamp(array("MM" => -2), mktime(0, 0, 0, date("n"), 1, date("Y"))), "FULL");
$BeforeLastMonth_maxDate =  ConvertTimeStamp(AddToTimeStamp(array("MM" => -1, "SS" => -1), mktime(0, 0, 0, date("n"), 1, date("Y"))), "FULL");

$LastMonth_minDate = ConvertTimeStamp(AddToTimeStamp(array("MM" => -1), mktime(0, 0, 0, date("n"), 1, date("Y"))), "FULL");
$LastMonth_maxDate = ConvertTimeStamp(AddToTimeStamp(array("SS" => -1), mktime(0, 0, 0, date("n"), 1, date("Y"))), "FULL");

$ThisMonth_minDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 1, date("Y")), "FULL");
$ThisMonth_maxDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j")+1, date("Y")), "FULL");

$d = date("w");
if($d < 1)
	$d = 6;
elseif($d > 1)
	$d = $d-1;
else
	$d = 0;

$BeforeLastWeek_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".(14+$d)), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
$BeforeLastWeek_maxDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".(7+$d), "SS" => -1),  mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

$LastWeek_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".(7+$d)), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
$LastWeek_maxDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".$d, "SS" => -1),  mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

$ThisWeek_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-".$d),  mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
$ThisWeek_maxDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j")+1, date("Y")), "FULL");

$BeforeYesterday_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-2"), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
$BeforeYesterday_maxDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-1", "SS" => -1), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

$Yesterday_minDate = ConvertTimeStamp(AddToTimeStamp(array("DD" => "-1"), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");
$Yesterday_maxDate = ConvertTimeStamp(AddToTimeStamp(array("SS" => -1), mktime(0, 0, 0, date("n"), date("j"), date("Y"))), "FULL");

$Today_minDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j"), date("Y")), "FULL");
$Today_maxDate = ConvertTimeStamp(mktime(0, 0, 0, date("n"), date("j")+1, date("Y")), "FULL");


$arDatePeriods = array(
	"BEFORE_LAST_MONTH" => array(
		"MIN_DATE" => $BeforeLastMonth_minDate,
		"MAX_DATE" => $BeforeLastMonth_maxDate,
		"CACHE_TIME" => 86400
	),
	"LAST_MONTH" => array(
		"MIN_DATE" => $LastMonth_minDate,
		"MAX_DATE" => $LastMonth_maxDate,
		"CACHE_TIME" => 86400
	),
	"THIS_MONTH" => array(
		"MIN_DATE" => $ThisMonth_minDate,
		"MAX_DATE" => $ThisMonth_maxDate,
		"CACHE_TIME" => 1
	),
	"BEFORE_LAST_WEEK" => array(
		"MIN_DATE" => $BeforeLastWeek_minDate,
		"MAX_DATE" => $BeforeLastWeek_maxDate,
		"CACHE_TIME" => 86400
	),
	"LAST_WEEK" => array(
		"MIN_DATE" => $LastWeek_minDate,
		"MAX_DATE" => $LastWeek_maxDate,
		"CACHE_TIME" => 86400
	),
	"THIS_WEEK" => array(
		"MIN_DATE" => $ThisWeek_minDate,
		"MAX_DATE" => $ThisWeek_maxDate,
		"CACHE_TIME" => 1
	),
	"BEFORE_YESTERDAY" => array(
		"MIN_DATE" => $BeforeYesterday_minDate,
		"MAX_DATE" => $BeforeYesterday_maxDate,
		"CACHE_TIME" => 86400
	),
	"YESTERDAY" => array(
		"MIN_DATE" => $Yesterday_minDate,
		"MAX_DATE" => $Yesterday_maxDate,
		"CACHE_TIME" => 3600
	),
	"TODAY" => array(
		"MIN_DATE" => $Today_minDate,
		"MAX_DATE" => $Today_maxDate,
		"CACHE_TIME" => 1
	)
);

$arStatus1 = array(
	"CREATED" => array(
		"NAME" => GetMessage("GD_ORDERS_STATUS_1_CREATED"),
		"DB_FIELD" => "DATE_INSERT"
	),
	"PAID" => array(
		"NAME" => GetMessage("GD_ORDERS_STATUS_1_PAID"),
		"DB_FIELD" => "DATE_PAYED",
		"Y_FIELD" => "PAYED"
	),
	"CANCELED" => array(
		"NAME" => GetMessage("GD_ORDERS_STATUS_1_CANCELED"),
		"DB_FIELD" => "DATE_CANCELED",
		"Y_FIELD" => "CANCELED"
	),
	"ALLOW_DELIVERY" =>array(
		"NAME" => GetMessage("GD_ORDERS_STATUS_1_ALLOW_DELIVERY"),
		"DB_FIELD" => "DATE_ALLOW_DELIVERY",
		"Y_FIELD" => "ALLOW_DELIVERY"
	)
);

foreach($arDatePeriods as $key => $arPeriod)
{
	foreach($arStatus1 as $status_code => $arStatus)
	{
		$arOrderStats[$key][$status_code] = 0;
		$arOrderStats[$key]["PRICE_".$status_code] = 0;
	}
	$obCache = new CPHPCache;
	$cache_id = $key."_".md5(serialize($arPeriod))."_".md5(serialize($arFilterLID))."_".md5(serialize($arFilterPerms))."_".md5(serialize($arGadgetParams["ORDERS_STATUS_1"]));
	if($obCache->InitCache($arPeriod["CACHE_TIME"], $cache_id, "/"))
	{
		$vars = $obCache->GetVars();
		foreach($arStatus1 as $status_code => $arStatus)
		{
			$arOrderStats[$key][$status_code] = $vars[$status_code]["COUNT"];
			$arOrderStats[$key]["PRICE_".$status_code] = $vars[$status_code]["SUM"];
		}
	}
	else
	{
		foreach($arStatus1 as $status_code => $arStatus)
		{
			if (
				!is_array($arGadgetParams["ORDERS_STATUS_1"])
				|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
			)
			{
				if (array_key_exists("Y_FIELD", $arStatus) && $arStatus["Y_FIELD"] <> '')
					$arFilterYField = array("=".$arStatus["Y_FIELD"] => "Y");
				else
					$arFilterYField = array();

				$arFilter = array_merge(
					array(
						">=".$arStatus["DB_FIELD"]=> $arPeriod["MIN_DATE"],
						"<=".$arStatus["DB_FIELD"] => $arPeriod["MAX_DATE"]
					),
					$arFilterLID,
					$arFilterPerms,
					$arFilterYField
				);

				$resOrder = \Bitrix\Sale\Internals\OrderTable::getList(array(
					'select' => array(
						new \Bitrix\Main\Entity\ExpressionField('SUM', 'SUM(%s)', 'PRICE'),
						new \Bitrix\Main\Entity\ExpressionField('COUNT', 'COUNT(%s)', 'ID')
					),
					'filter' => $arFilter
				));

				while($orderData = $resOrder->fetch())
				{
					$arOrderStats[$key][$status_code] = $orderData["COUNT"];
					$arOrderStats[$key]["PRICE_".$status_code] = $orderData["SUM"];
				}
			}
		}
	}

	if($obCache->StartDataCache())
	{
		$arCacheData = array();
		foreach($arStatus1 as $status_code => $arStatus)
		{
			$arCacheData[$status_code] = array(
				"COUNT" => $arOrderStats[$key][$status_code],
				"SUM" => $arOrderStats[$key]["PRICE_".$status_code]
			);
		}

		$obCache->EndDataCache($arCacheData);
	}
}

$arFilter = array_merge(
	$arFilterLID,
	$arFilterPerms
);
$arSelectedFields = Array("ID", "PAYED", "DATE_PAYED", "CANCELED", "DATE_CANCELED", "STATUS_ID", "DATE_STATUS", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "LID", "USER_ID", "USER_NAME", "USER_LAST_NAME");

$resOrder = \Bitrix\Sale\Internals\OrderTable::getList(array(
	'select' => array(
		"ID",
		"PAYED",
		"DATE_PAYED",
		"CANCELED",
		"DATE_CANCELED",
		"STATUS_ID",
		"DATE_STATUS",
		"PRICE_DELIVERY",
		"ALLOW_DELIVERY",
		"DATE_ALLOW_DELIVERY",
		"PRICE",
		"CURRENCY",
		"DISCOUNT_VALUE",
		"PAY_SYSTEM_ID",
		"DELIVERY_ID",
		"DATE_INSERT",
		"LID",
		"USER_ID",
		'USER_NAME' => "USER.NAME",
		'USER_LAST_NAME' => "USER.LAST_NAME"
	),
	'filter' => $arFilter,
	'order' => array("DATE_UPDATE" => "DESC"),
	'limit' => $arGadgetParams["ITEMS_COUNT"]
));
while($orderData = $resOrder->fetch())
{
	$arOrders[] = $orderData;
}

$today = ConvertTimeStamp(time());
$yesterday = ConvertTimeStamp(AddToTimeStamp(array("DD" => -2, "MM" => 0, "YYYY"	=> 0, "HH" => 0, "MI" => 0, "SS" => 0), time()));
$before_yesterday = ConvertTimeStamp(AddToTimeStamp(array("DD" => -1, "MM" => 0, "YYYY"	=> 0, "HH" => 0, "MI" => 0, "SS" => 0), time()));

$d = date("w");
if($d < 1)
	$d = 6;
elseif($d > 1)
	$d = $d-1;
else
	$d = 0;

$this_week = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".$d), time()));
$last_week = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".(7+$d)), time()));
$before_last_week = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".(14+$d)), time()));

$this_month = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 1, date("Y")));
$last_month = ConvertTimeStamp(AddToTimeStamp(array("DD" => 0, "MM" => -1, "YYYY"	=> 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), 1, date("Y"))));
$before_last_month = ConvertTimeStamp(AddToTimeStamp(array("DD" => 0, "MM" => -2, "YYYY"	=> 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), 1, date("Y"))));

$date_today = ConvertTimeStamp(time(), "SHORT");
$date_yesterday = ConvertTimeStamp(AddToTimeStamp(array("DD" => -1, "MM" => 0, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), time()), "SHORT");
$date_beforeyesterday = ConvertTimeStamp(AddToTimeStamp(array("DD" => -2, "MM" => 0, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), time()), "SHORT");

$date_this_week1 = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".$d), time()));
$date_last_week1 = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".(7+$d)), time()));
$date_before_last_week1 = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".(14+$d)), time()));

$date_this_week2 = $date_today;
$date_last_week2 = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".(1+$d)), time()));
$date_before_last_week2 = ConvertTimeStamp(AddToTimeStamp(Array("DD" => "-".(8+$d)), time()));

$date_this_month1 = ConvertTimeStamp(mktime(0, 0, 0, date("n"), 1, date("Y")));
$date_last_month1 = ConvertTimeStamp(AddToTimeStamp(array("DD" => 0, "MM" => -1, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), 1, date("Y"))));
$date_before_last_month1 = ConvertTimeStamp(AddToTimeStamp(array("DD" => 0, "MM" => -2, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), 1, date("Y"))));

$date_this_month2 = $date_today;
$date_last_month2 = ConvertTimeStamp(AddToTimeStamp(array("DD" => -1, "MM" => 0, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), 1, date("Y"))));
$date_before_last_month2 = ConvertTimeStamp(AddToTimeStamp(array("MM" => -1, "DD" => -1, "YYYY" => 0, "HH" => 0, "MI" => 0, "SS" => 0), mktime(0, 0, 0, date("n"), 1, date("Y"))));

?><script type="text/javascript">
	var gdSaleTabControl_<?=$arGadgetParams["RND_STRING"]?> = false;
</script><?

$aTabs = array(
	array(
		"DIV" => "bx_gd_sale_stats_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_ORDERS_TAB_STATS"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdSaleTabControl_".$arGadgetParams["RND_STRING"].".SelectTab('bx_gd_sale_stats_".$arGadgetParams["RND_STRING"]."');"
	),
	array(
		"DIV" => "bx_gd_sale_orders_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_ORDERS_TAB_ORDERS"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdSaleTabControl_".$arGadgetParams["RND_STRING"].".SelectTab('bx_gd_sale_orders_".$arGadgetParams["RND_STRING"]."');"
	)
);

$tabControl = new CAdminViewTabControl("saleTabControl_".$arGadgetParams["RND_STRING"], $aTabs);

?><div class="bx-gadgets-tabs-wrap" id="bx_gd_tabset_sale_<?=$arGadgetParams["RND_STRING"]?>"><?
	$tabControl->Begin();
	$countTabs = count($aTabs);
	for($i = 0; $i < $countTabs; $i++)
		$tabControl->BeginNextTab();
	$tabControl->End();
	?><div class="bx-gadgets-tabs-cont"><?

		$countTab = count($aTabs);
		for($i = 0; $i < $countTab; $i++)
		{
			?><div id="<?=$aTabs[$i]["DIV"]?>_content" style="display: <?=($i==0 ? "block" : "none")?>;" class="bx-gadgets-tab-container"><?
				if ($i == 0)
				{
					?><table class="bx-gadgets-table">
						<tbody>
							<tr>
								<th>&nbsp;</th><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										?><th><?=$arStatus["NAME"]?></th><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_TODAY")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_today."&filter_date_to=".$date_today;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_today."&filter_date_paid_to=".$date_today;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_today."&filter_date_update_to=".$date_today;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_today."&filter_allow_delivery_to=".$date_today;

										?><td align="center"><a <?if (intval($arOrderStats["TODAY"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["TODAY"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										);?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["TODAY"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_YESTERDAY")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_yesterday."&filter_date_to=".$date_yesterday;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_yesterday."&filter_date_paid_to=".$date_yesterday;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_yesterday."&filter_date_update_to=".$date_yesterday;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_yesterday."&filter_date_allow_delivery_to=".$date_yesterday;

										?><td align="center"><a <?if (intval($arOrderStats["YESTERDAY"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["YESTERDAY"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["YESTERDAY"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_BEFOREYESTERDAY")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_beforeyesterday."&filter_date_to=".$date_beforeyesterday;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_beforeyesterday."&filter_date_paid_to=".$date_beforeyesterday;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_beforeyesterday."&filter_date_update_to=".$date_beforeyesterday;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_beforeyesterday."&filter_date_allow_delivery_to=".$date_beforeyesterday;

										?><td align="center"><a <?if (intval($arOrderStats["BEFORE_YESTERDAY"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["BEFORE_YESTERDAY"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										);?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["BEFORE_YESTERDAY"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_THISWEEK")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_this_week1."&filter_date_to=".$date_this_week2;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_this_week1."&filter_date_paid_to=".$date_this_week2;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_this_week1."&filter_date_update_to=".$date_this_week2;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_this_week1."&filter_date_allow_delivery_to=".$date_this_week2;

										?><td align="center"><a <?if (intval($arOrderStats["THIS_WEEK"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["THIS_WEEK"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["THIS_WEEK"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_LASTWEEK")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_last_week1."&filter_date_to=".$date_last_week2;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_last_week1."&filter_date_paid_to=".$date_last_week2;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_last_week1."&filter_date_update_to=".$date_last_week2;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_last_week1."&filter_date_allow_delivery_to=".$date_last_week2;

										?><td align="center"><a <?if (intval($arOrderStats["LAST_WEEK"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["LAST_WEEK"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["LAST_WEEK"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_BEFORELASTWEEK")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_before_last_week1."&filter_date_to=".$date_before_last_week2;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_before_last_week1."&filter_date_paid_to=".$date_before_last_week2;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_before_last_week1."&filter_date_update_to=".$date_before_last_week2;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_before_last_week1."&filter_date_allow_delivery_to=".$date_before_last_week2;

										?><td align="center"><a <?if (intval($arOrderStats["BEFORE_LAST_WEEK"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["BEFORE_LAST_WEEK"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["BEFORE_LAST_WEEK"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_THISMONTH")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_this_month1."&filter_date_to=".$date_this_month2;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_this_month1."&filter_date_paid_to=".$date_this_month2;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_this_month1."&filter_date_update_to=".$date_this_month2;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_this_month1."&filter_date_allow_delivery_to=".$date_this_month2;

										?><td align="center"><a <?if (intval($arOrderStats["THIS_MONTH"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["THIS_MONTH"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["THIS_MONTH"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_LASTMONTH")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_last_month1."&filter_date_to=".$date_last_month2;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_last_month1."&filter_date_paid_to=".$date_last_month2;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_last_month1."&filter_date_update_to=".$date_last_month2;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_last_month1."&filter_date_allow_delivery_to=".$date_last_month2;

										?><td align="center"><a <?if (intval($arOrderStats["LAST_MONTH"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["LAST_MONTH"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["LAST_MONTH"][$status_code])?></a></td><?
									}
							?></tr>
							<tr>
								<td><?=GetMessage("GD_ORDERS_BEFORELASTMONTH")?></td><?
								foreach($arStatus1 as $status_code => $arStatus)
									if (
										!is_array($arGadgetParams["ORDERS_STATUS_1"])
										|| in_array($status_code, $arGadgetParams["ORDERS_STATUS_1"])
									)
									{
										if ($status_code == "CREATED")
											$strDateFilter = "filter_date_from=".$date_before_last_month1."&filter_date_to=".$date_before_last_month2;
										elseif ($status_code == "PAID")
											$strDateFilter = "filter_payed=Y&filter_date_paid_from=".$date_before_last_month1."&filter_date_paid_to=".$date_before_last_month2;
										elseif ($status_code == "CANCELED")
											$strDateFilter = "filter_canceled=Y&filter_date_update_from=".$date_before_last_month1."&filter_date_update_to=".$date_before_last_month2;
										elseif ($status_code == "ALLOW_DELIVERY")
											$strDateFilter = "filter_allow_delivery=Y&filter_date_allow_delivery_from=".$date_before_last_month1."&filter_date_allow_delivery_to=".$date_before_last_month2;

										?><td align="center"><a <?if (intval($arOrderStats["BEFORE_LAST_MONTH"][$status_code]) > 0):?>title="<?=GetMessage("GD_ORDERS_ORDER_SUM")?>: <?=CCurrencyLang::CurrencyFormat(
											$arOrderStats["BEFORE_LAST_MONTH"]["PRICE_".$status_code],
											$arGadgetParams["SITE_CURRENCY"],
											true
										); ?>"<?endif;?> href="/bitrix/admin/sale_order.php?set_filter=Y&<?=$strDateFilter?>&lang=<?=LANGUAGE_ID?>"><?=intval($arOrderStats["BEFORE_LAST_MONTH"][$status_code])?></a></td><?
									}
							?></tr>
						</tbody>
					</table><?
				}
				elseif ($i == 1)
				{
					if (count($arOrders) > 0)
					{
						?><table class="bx-gadgets-table">
							<tbody>
								<tr>
									<th align="left">&nbsp;</th>
									<th align="left"><?=GetMessage("GD_ORDERS_ORDER_SUM")?></th>
									<th align="left"><?=GetMessage("GD_ORDERS_ORDER_USER")?></th>
									<th align="center"><?=GetMessage("GD_ORDERS_ORDER_PAID")?></th>
									<th align="center"><?=GetMessage("GD_ORDERS_ORDER_CANCELED")?></th>
									<th align="center"><?=GetMessage("GD_ORDERS_ORDER_ALLOW_DELIVERY")?></th>
								</tr><?
								foreach($arOrders as $arOrder)
								{
									?><tr>
										<td align="left" style="vertical-align: top;"><a href="/bitrix/admin/sale_order_view.php?ID=<?=$arOrder["ID"]?>&lang=<?=LANGUAGE_ID?>"><?=GetMessage("GD_ORDERS_ORDER_1")?><?=$arOrder["ID"]?></a><br><?=$arOrder["DATE_INSERT"]?></td>
										<td align="left" style="vertical-align: top;"><?=CCurrencyLang::CurrencyFormat($arOrder["PRICE"], $arOrder["CURRENCY"], true); ?></td>
										<td align="left" style="vertical-align: top;"><a href="/bitrix/admin/user_edit.php?ID=<?=$arOrder["USER_ID"]?>&lang=<?=LANGUAGE_ID?>"><?=htmlspecialcharsbx($arOrder["USER_NAME"])." ".htmlspecialcharsbx($arOrder["USER_LAST_NAME"])?></a></td>
										<td align="center" style="vertical-align: top;"><?=($arOrder["PAYED"]=="Y"?GetMessage("GD_ORDERS_YES")."<br>".$arOrder["DATE_PAYED"]:GetMessage("GD_ORDERS_NO"))?></td>
										<td align="center" style="vertical-align: top;"><?=($arOrder["CANCELED"]=="Y"?GetMessage("GD_ORDERS_YES")."<br>".$arOrder["DATE_CANCELED"]:GetMessage("GD_ORDERS_NO"))?></td>
										<td align="center" style="vertical-align: top;"><?=($arOrder["ALLOW_DELIVERY"]=="Y"?GetMessage("GD_ORDERS_YES")."<br>".$arOrder["DATE_ALLOW_DELIVERY"]:GetMessage("GD_ORDERS_NO"))?></td>
									</tr><?
								}
							?></tbody>
						</table><?
					}
					else
					{
						?><div align="center" class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t"><?=GetMessage("GD_ORDERS_NO_DATA")?></div><?
					}
					?><div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t"><a href="/bitrix/admin/sale_order.php?lang=<?=LANGUAGE_ID?>"><?=GetMessage("GD_ORDERS_ALL_ORDERS")?></a></div><?
				}
			?></div><?
		}
	?></div>
</div>
<script type="text/javascript">
	BX.ready(function(){
		gdSaleTabControl_<?=$arGadgetParams["RND_STRING"]?> = new gdTabControl('bx_gd_tabset_sale_<?=$arGadgetParams["RND_STRING"]?>');
	});
</script>