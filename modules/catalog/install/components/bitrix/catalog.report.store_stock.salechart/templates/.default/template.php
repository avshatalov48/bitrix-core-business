<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $component
 * @var $this \CBitrixComponentTemplate
 * @var \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

Extension::load([
	'amcharts4',
	'amcharts4_theme_animated',
	'currency',
	'ui.icons',
	'ui.hint',
]);


?>

<div class="store-stock-sale-chart" id="chartdiv"></div>

<div id="chart-popup-template" class="catalog-report-store-stock-modal catalog-report-store-stock-modal-hidden" style="border-color: rgb(57, 168, 239);">
	<div class="catalog-report-store-stock-modal-head">
		<div id="chart-popup-template-title" class="catalog-report-store-stock-modal-title"></div>
	</div>
	<div class="catalog-report-store-stock-modal-main">
		<div class="catalog-report-store-stock-card-info">
			<div class="catalog-report-store-stock-card-info-item" style="display: block">
				<div class="catalog-report-store-stock-card-subtitle"><?=Loc::getMessage('STORE_STOCK_CHART_POPUP_SUM')?></div>
				<div class="catalog-report-store-stock-card-info-value-box">
					<div id="chart-popup-template-sum" class="catalog-report-store-stock-card-info-value"></div>
				</div>
			</div>
			<div class="catalog-report-store-stock-card-info-item" style="display: block">
				<div class="catalog-report-store-stock-card-subtitle">&#8291;</div>
				<div class="catalog-report-store-stock-card-info-value-box">
					<div id="chart-popup-template-sum-proc" class="catalog-report-store-stock-card-info-value"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>

	BX.Currency.setCurrencyFormat(
		'<?=\CUtil::JSEscape($arResult['chartData']['currency'])?>',
		<?= \CUtil::PhpToJSObject(\CCurrencyLang::GetFormatDescription($arResult['chartData']['currency']))?>
	);

	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
	BX.ready(function ()
	{
		var chartParams = {
			chartId: 'chartdiv',
			boardId: '<?=$arResult['boardId']?>',
			widgetId: '<?=$arResult['widgetId']?>',
			chartData: <?=CUtil::PhpToJSObject($arResult['chartData'])?>,
			storeInfoPopupTemplate: document.getElementById('chart-popup-template'),
		};

		BX.Catalog.Report.StoreStock.StoreStockSaleChart.Instance = new BX.Catalog.Report.StoreStock.StoreStockSaleChart(chartParams);
	});
</script>