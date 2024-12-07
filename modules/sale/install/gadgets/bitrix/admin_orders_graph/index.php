<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Currency;
use Bitrix\Sale;

if (
	!Loader::includeModule('sale')
	|| !Loader::includeModule('currency')
)
{
	return false;
}

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	return false;
if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
	return false;

$arGadgetParams['SITE_ID'] = (string)($arGadgetParams['SITE_ID'] ?? '');

if ($arGadgetParams["SITE_ID"] !== '')
{
	$arGadgetParams["SITE_CURRENCY"] = Sale\Internals\SiteCurrencyTable::getSiteCurrency($arGadgetParams["SITE_ID"]);
	if ($arGadgetParams["TITLE_STD"] == '')
	{
		$rsSites = CSite::GetByID($arGadgetParams["SITE_ID"]);
		$arSite = $rsSites->GetNext();
		if ($arSite)
		{
			$arGadget["TITLE"] .= " / [" . $arSite["ID"] . "] " . $arSite["NAME"];
		}
	}
}
else
{
	$arGadgetParams["SITE_CURRENCY"] = Currency\CurrencyManager::getBaseCurrency();
}

$arGadgetParams["RND_STRING"] = randString(8);

$arColor = Array("08738C", "C6B59C", "0000FF", "FF0000", "FFFF00", "F7C684" ,"8CD694", "9CADCE", "B584BD", "C684BD");
$width = 400;
$height = 300;

$arFields = Array();
if (isset($arGadgetParams["SITE_ID"]) && $arGadgetParams["SITE_ID"] <> '')
	$arFields["find_lid"] = $arGadgetParams["SITE_ID"];
if(empty($arGadgetParams["ORDERS_STATUS"]))
	$arGadgetParams["ORDERS_STATUS"] = Array("CREATED", "PAID");
if(is_array($arGadgetParams["ORDERS_STATUS"]) && !empty($arGadgetParams["ORDERS_STATUS"]))
{
	if(in_array("CREATED", $arGadgetParams["ORDERS_STATUS"]))
	{
		$arFields["find_all"] = "Y";
		$arFields["find_all_".$arGadgetParams["SITE_CURRENCY"]] = "Y";
	}
	if(in_array("PAID", $arGadgetParams["ORDERS_STATUS"]))
	{
		$arFields["find_payed"] = "Y";
		$arFields["find_payed_".$arGadgetParams["SITE_CURRENCY"]] = "Y";
	}
	if(in_array("CANCELED", $arGadgetParams["ORDERS_STATUS"]))
	{
		$arFields["find_canceled"] = "Y";
		$arFields["find_canceled_".$arGadgetParams["SITE_CURRENCY"]] = "Y";
	}
	if(in_array("ALLOW_DELIVERY", $arGadgetParams["ORDERS_STATUS"]))
	{
		$arFields["find_allow_delivery"] = "Y";
		$arFields["find_allow_delivery_".$arGadgetParams["SITE_CURRENCY"]] = "Y";
	}
}
$arFields["width"] = $width;
$arFields["height"] = $height;
$arFields["filter"] = "Y";
$arFields["set_filter"] = "Y";
$arFields["LANG"] = LANGUAGE_ID;
$arFields["rand"] = randString(4);

if(!isset($arGadgetParams["PERIOD"]) || $arGadgetParams["PERIOD"] == '' || $arGadgetParams["PERIOD"] == "MONTH")
{
	$arFields["filter_date_from"] = ConvertTimeStamp(AddToTimeStamp(Array("MM" => -1)));
	$arFields["filter_by"] = "week";
	$arFields["cache_time"] = 60*60*12;
}
elseif($arGadgetParams["PERIOD"] == "WEEK")
{
	$arFields["filter_date_from"] = ConvertTimeStamp(AddToTimeStamp(Array("DD" => -7)));
	$arFields["filter_by"] = "day";
	$arFields["cache_time"] = 60*60*4;
}
elseif($arGadgetParams["PERIOD"] == "QUATER")
{
	$arFields["filter_date_from"] = ConvertTimeStamp(AddToTimeStamp(Array("MM" => -4)));
	$arFields["filter_by"] = "month";
	$arFields["cache_time"] = 60*60*24;
}
elseif($arGadgetParams["PERIOD"] == "YEAR")
{
	$arFields["filter_date_from"] = ConvertTimeStamp(AddToTimeStamp(Array("YYYY" => -1)));
	$arFields["filter_by"] = "month";
	$arFields["cache_time"] = 60*60*24;
}
if(isset($_REQUEST["clear_cache"]) && $_REQUEST["clear_cache"] == "Y")
	$arFields["clear_cache"] = "Y";

$imgUrl = "/bitrix/admin/sale_stat_graph.php?";
foreach($arFields as $k => $v)
{
	$imgUrl .= $k."=".$v."&amp;";
}
?>
<script>
	var gdSaleGraphTabControl_<?=$arGadgetParams["RND_STRING"]?> = false;
	BX.ready(function(){
		gdSaleGraphTabControl_<?=$arGadgetParams["RND_STRING"]?> = new gdTabControl('bx_gd_tabset_sale_graph_<?=$arGadgetParams["RND_STRING"]?>');
	});
</script>
<?
$aTabs = array(
	array(
		"DIV" => "bx_gd_sale_graph1_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_ORDERS_TAB_GRAPH"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdSaleGraphTabControl_".$arGadgetParams["RND_STRING"].".SelectTab('bx_gd_sale_graph1_".$arGadgetParams["RND_STRING"]."');"
	),
	array(
		"DIV" => "bx_gd_sale_graph2_".$arGadgetParams["RND_STRING"],
		"TAB" => GetMessage("GD_ORDERS_TAB_GRAPH2"),
		"ICON" => "",
		"TITLE" => "",
		"ONSELECT" => "gdSaleGraphTabControl_".$arGadgetParams["RND_STRING"].".SelectTab('bx_gd_sale_graph2_".$arGadgetParams["RND_STRING"]."');"
	)
);

$tabControl = new CAdminViewTabControl("saleGraphTabControl_".$arGadgetParams["RND_STRING"], $aTabs);

?>
<div class="bx-gadgets-tabs-wrap" id="bx_gd_tabset_sale_graph_<?=$arGadgetParams["RND_STRING"]?>">
	<?
	$tabControl->Begin();
	$tabCount = count($aTabs);
	for ($i = 0; $i < $tabCount; $i++)
	{
		$tabControl->BeginNextTab();
	}
	$tabControl->End();
	?>
	<div class="bx-gadgets-tabs-cont">
		<?
		for ($i = 0; $i < $tabCount; $i++)
		{
			?><div id="<?=$aTabs[$i]["DIV"]?>_content" style="display: <?=($i==0 ? "block" : "none")?>;" class="bx-gadgets-tab-container"><?
				if ($i == 0)
				{
					?>
					<table cellspacing="0" cellpadding="0" border="0">
					<tbody><tr>
						<td valign="top" class="graph">
							<img class="graph" src="<?=$imgUrl?>mode=money" width="<?=$width?>" height="<?=$height?>">
						</td>
					</tr>
					<tr>
						<td valign="top">
							<table cellpadding="2" cellspacing="0" border="0" class="legend" style="font-size:100%;">
							<tbody>
								<?if($arFields["find_all"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=08738C" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L_ALL")?></td>
								</tr>
								<?endif;?>
								<?if($arFields["find_payed"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=C6B59C" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L_PAYED")?></td>
								</tr>
								<?endif;?>
								<?if(isset($arFields["find_allow_delivery"]) && $arFields["find_allow_delivery"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=0000FF" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L_AD")?></td>
								</tr>
								<?endif;?>
								<?if(isset($arFields["find_canceled"]) && $arFields["find_canceled"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=FF0000" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L_CANCELED")?></td>
								</tr>
								<?endif;?>
							</tbody>
							</table>
						</td>
					</tr>
					</tbody></table>
					<?
				}
				elseif ($i == 1)
				{
					?>
					<table cellspacing="0" cellpadding="0" border="0">
					<tbody><tr>
						<td valign="top" class="graph">
							<img class="graph" src="<?=$imgUrl?>mode=count" width="<?=$width?>" height="<?=$height?>">
						</td>
					</tr>
					<tr>
						<td valign="top">
							<table cellpadding="2" cellspacing="0" border="0" class="legend" style="font-size:100%;">
							<tbody>
								<?if($arFields["find_all"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=08738C" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L2_ALL")?></td>
								</tr>
								<?endif;?>
								<?if($arFields["find_payed"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=C6B59C" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L2_PAYED")?></td>
								</tr>
								<?endif;?>
								<?if(isset($arFields["find_allow_delivery"]) && $arFields["find_allow_delivery"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=0000FF" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L2_AD")?></td>
								</tr>
								<?endif;?>
								<?if(isset($arFields["find_canceled"]) && $arFields["find_canceled"] == "Y"):?>
								<tr>
									<td valign="center"><img src="/bitrix/admin/sale_graph_legend.php?color=FF0000" width="45" height="2" style="margin-bottom: 4px;"></td>
									<td nowrap=""><img src="/bitrix/images/1.gif" width="3" height="1"><?=GetMessage("GD_ORDERS_L2_CANCELED")?></td>
								</tr>
								<?endif;?>
							</tbody>
							</table>
						</td>
					</tr>
					</tbody></table>
					<?
				}?>
			</div>
		<?
		}?>
	</div>
</div>