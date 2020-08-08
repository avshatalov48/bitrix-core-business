<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Catalog;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!Loader::includeModule("sale"))
	return false;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	return false;

if ($arGadgetParams["SITE_ID"] <> '')
{
	if ($arGadgetParams["TITLE_STD"] == '')
	{
		$rsSites = CSite::GetByID($arGadgetParams["SITE_ID"]);
		if ($arSite = $rsSites->GetNext())
			$arGadget["TITLE"] .= " / [".$arSite["ID"]."] ".$arSite["NAME"];
	}
}

$arGadgetParams["RND_STRING"] = randString(8);

$arFilter = array();
if ($arGadgetParams["SITE_ID"] <> '')
{
	$arFilter["LID"] = $arGadgetParams["SITE_ID"];
	$arGadgetParams["RND_STRING"] = $arGadgetParams["SITE_ID"].'_'.$arGadgetParams["RND_STRING"];
}
$cache_time = 0;
if($arGadgetParams["PERIOD"] == "WEEK")
{
	$arFilter[">=DATE_INSERT"] = ConvertTimeStamp(AddToTimeStamp(Array("DD" => -7)));
	$cache_time = 60*60*4;
}
elseif($arGadgetParams["PERIOD"] == '' || $arGadgetParams["PERIOD"] == "MONTH")
{
	$arFilter[">=DATE_INSERT"] = ConvertTimeStamp(AddToTimeStamp(Array("MM" => -1)));
	$cache_time = 60*60*12;
}
elseif($arGadgetParams["PERIOD"] == "QUATER")
{
	$arFilter[">=DATE_INSERT"] = ConvertTimeStamp(AddToTimeStamp(Array("MM" => -4)));
	$cache_time = 60*60*24;
}
elseif($arGadgetParams["PERIOD"] == "YEAR")
{
	$arFilter[">=DATE_INSERT"] = ConvertTimeStamp(AddToTimeStamp(Array("YYYY" => -1)));
	$cache_time = 60*60*24;
}
if(!isset($arGadgetParams["LIMIT"]) || (int)$arGadgetParams["LIMIT"] <= 0)
	$arGadgetParams["LIMIT"] = 5;

$obCache = new CPHPCache;
$cache_id = "admin_products_".md5(serialize($arFilter))."_".$arGadgetParams["LIMIT"];
if ($obCache->InitCache($cache_time, $cache_id, "/"))
{
	$arResult = $obCache->GetVars();
}
else
{
	$cacheStart = false;
	if ($cache_time > 0)
	{
		$cacheStart = $obCache->StartDataCache();
	}
	$arResult = array();
	$arResult["SEL"] = array();
	$arFilter["PAYED"] = "Y";
	$dbR = CSaleProduct::GetBestSellerList("AMOUNT", array(), $arFilter, $arGadgetParams["LIMIT"]);
	while($arR = $dbR->Fetch())
	{
		$arResult["SEL"][] = $arR;
	}

	// VIEWED
	$arResult["VIEWED"] = array();

	if (!Loader::includeModule('catalog'))
	{
		$obCache->AbortDataCache();
		return;
	}

	$basePrice = CCatalogGroup::GetBaseGroup();
	if (empty($basePrice))
	{
		$obCache->AbortDataCache();
		return;
	}

	$productFilter = array(
		'>ELEMENT_ID' => 0,
		'>PRODUCT_ID' => 0,
		'>=DATE_VISIT' => $arFilter['>=DATE_INSERT']
	);
	if (isset($arFilter['LID']))
		$productFilter['=SITE_ID'] = $arFilter['LID'];

	$elementIds = array();
	$elements = array();
	$productMap = array();
	$productIds = array();
	$iterator = Catalog\CatalogViewedProductTable::getList(array(
		'select' => array('ELEMENT_ID', 'PRODUCT_ID' ,'VIEW_COUNT'),
		'filter' => $productFilter,
		'group' => 'ELEMENT_ID',
		'order' => array('VIEW_COUNT' => 'DESC'),
		'limit' => $arGadgetParams['LIMIT']
	));
	while ($row = $iterator->fetch())
	{
		$elementIds[$row['ELEMENT_ID']] = $row['ELEMENT_ID'];
		$productIds[$row['PRODUCT_ID']] = $row['PRODUCT_ID'];
		$productMap[$row['PRODUCT_ID']] = $row['ELEMENT_ID'];
		$row['NAME'] = '';

		if (isset($elements[$row['ELEMENT_ID']]))
		{
			$elements[$row['ELEMENT_ID']]['VIEW_COUNT'] += $row['VIEW_COUNT'];
		}
		else
		{
			$elements[$row['ELEMENT_ID']] = $row;
		}
	}
	unset($item, $iterator);

	if (!empty($elementIds))
	{
		sort($elementIds);
		sort($productIds);
		$iterator = Catalog\CatalogViewedProductTable::getList(array(
			'select' => array('ELEMENT_ID', 'NAME' => 'PARENT_ELEMENT.NAME'),
			'filter' => array('@ELEMENT_ID' => $elementIds)
		));
		while ($row = $iterator->fetch())
			$elements[$row['ELEMENT_ID']]['NAME'] = (string)$row['NAME'];
		unset($row, $iterator);


		$iterator = Catalog\PriceTable::getList(array(
			'select' => array('PRODUCT_ID', 'PRICE', 'CURRENCY'),
			'filter' => array(
				'@PRODUCT_ID' => $productIds,
				'=CATALOG_GROUP_ID' => $basePrice['ID'],
				array(
					'LOGIC' => 'OR',
					'<=QUANTITY_FROM' => 1,
					'=QUANTITY_FROM' => null
				),
				array(
					'LOGIC' => 'OR',
					'>=QUANTITY_TO' => 1,
					'=QUANTITY_TO' => null
				)
			)
		));
		while ($row = $iterator->fetch())
		{
			if (!isset($productMap[$row['PRODUCT_ID']]))
				continue;
			$index = $productMap[$row['PRODUCT_ID']];
			$elements[$index]['PRICE'] = $row['PRICE'];
			$elements[$index]['CURRENCY'] = $row['CURRENCY'];
			unset($index);
		}
		unset($row, $iterator);

		$clearElements = array();
		foreach ($elements as $row)
		{
			if ($row['NAME'] == '' || !isset($row['PRICE']))
				continue;
			$clearElements[] = $row;
		}
		unset($row);
		$elements = $clearElements;
		unset($clearElements);
	}

	$arResult['VIEWED'] = $elements;

	if ($cacheStart)
	{
		$obCache->EndDataCache($arResult);
	}
}

?><script type="text/javascript">
	var gdSaleProductsTabControl_<?=$arGadgetParams["RND_STRING"]?> = false;
	BX.ready(function(){
		gdSaleProductsTabControl_<?=$arGadgetParams["RND_STRING"]?> = new gdTabControl('bx_gd_tabset_sale_products_<?=$arGadgetParams["RND_STRING"]?>');
	});
</script><?

$aTabs = array(
	array(
		"DIV" => "bx_gd_sale_products1_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_PRD_TAB_1"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdSaleProductsTabControl_".$arGadgetParams["RND_STRING"].".SelectTab('bx_gd_sale_products1_".$arGadgetParams["RND_STRING"]."');"
	),
	array(
		"DIV" => "bx_gd_sale_products2_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_PRD_TAB_2"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdSaleProductsTabControl_".$arGadgetParams["RND_STRING"].".SelectTab('bx_gd_sale_products2_".$arGadgetParams["RND_STRING"]."');"
	)
);

$tabControl = new CAdminViewTabControl("salePrdTabControl_".$arGadgetParams["RND_STRING"], $aTabs);

?><div class="bx-gadgets-tabs-wrap" id="bx_gd_tabset_sale_products_<?=$arGadgetParams["RND_STRING"]?>"><?

	$tabControl->Begin();
	$tabsCount = count($aTabs);
	for($i = 0; $i < $tabsCount; $i++)
		$tabControl->BeginNextTab();
	$tabControl->End();

	?><div class="bx-gadgets-tabs-cont"><?
		for($i = 0; $i < $tabsCount; $i++)
		{
			?><div id="<?=$aTabs[$i]["DIV"]?>_content" style="display: <?=($i==0 ? "block" : "none")?>;" class="bx-gadgets-tab-container"><?
				if ($i == 0)
				{
					if (!empty($arResult["SEL"]))
					{
						?><table class="bx-gadgets-table">
							<tbody>
								<tr>
									<th><?=GetMessage("GD_PRD_NAME")?></th>
									<th><?=GetMessage("GD_PRD_QUANTITY")?></th>
									<th><?=GetMessage("GD_PRD_AV_PRICE")?></th>
									<th><?=GetMessage("GD_PRD_SUM")?></th>
								</tr><?
								foreach($arResult["SEL"] as $val)
								{
									?><tr>
										<td><?=htmlspecialcharsEx($val["NAME"])?></td>
										<td align="right"><?=intval($val["QUANTITY"])?></td>
										<td align="right" nowrap><?=CCurrencyLang::CurrencyFormat(DoubleVal($val["AVG_PRICE"]), $val["CURRENCY"], true)?></td>
										<td align="right" nowrap><?=CCurrencyLang::CurrencyFormat(DoubleVal($val["PRICE"]), $val["CURRENCY"], true)?></td>
									</tr><?
								}
							?></tbody>
						</table><?
					}
					else
					{
						?><div align="center" class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t"><?=GetMessage("GD_PRD_NO_DATA")?></div><?
					}
				}
				elseif ($i == 1)
				{
					if (!empty($arResult["VIEWED"]))
					{
						?><table class="bx-gadgets-table">
							<tbody>
								<tr>
									<th><?=GetMessage("GD_PRD_NAME")?></th>
									<th><?=GetMessage("GD_PRD_VIEWED")?></th>
									<th><?=GetMessage("GD_PRD_PRICE")?></th>
								</tr><?
								foreach($arResult["VIEWED"] as $val)
								{
									?><tr>
										<td><?=htmlspecialcharsEx($val["NAME"])?></td>
										<td align="right"><?=intval($val["VIEW_COUNT"])?></td>
										<td align="right" nowrap><?=(DoubleVal($val["PRICE"]) > 0 ? CCurrencyLang::CurrencyFormat(DoubleVal($val["PRICE"]), $val["CURRENCY"], true) : "")?></td>
									</tr><?
								}
							?></tbody>
						</table><?
					}
					else
					{
						?><div align="center" class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t"><?=GetMessage("GD_PRD_NO_DATA")?></div><?
					}
				}
			?></div><?
		}
	?></div>
</div>