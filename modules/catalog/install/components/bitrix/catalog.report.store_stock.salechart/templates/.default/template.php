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
	'ui.design-tokens',
	'ui.fonts.opensans',
	'amcharts4',
	'amcharts4_theme_animated',
	'currency',
	'ui.icons',
	'ui.hint',
	'catalog.store-chart',
]);

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
	<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
		<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
			<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
		</div>
	<?php endforeach;?>
	<?php
	return;
endif;


$jsMessagesCodes = [
	'STORE_CHART_ZOOMOUT_TITLE',
	'STORE_CHART_HINT_TITLE',
	'STORE_STOCK_CHART_SUM_STORED_SERIES_TITLE',
	'STORE_STOCK_CHART_SUM_STORED_SERIES_POPUP_TITLE',
	'STORE_STOCK_CHART_SUM_STORED_SERIES_POPUP_SUM',
	'STORE_STOCK_CHART_HINT_TITLE',
];

$jsMessages = [];

foreach ($jsMessagesCodes as $code)
{
	$jsMessages[$code] = Loc::getMessage($code);
}

$currency = $arResult['chartData']['chartProps']['currency']['id'];
?>

<div class="store-stock-sale-chart" id="<?=$arResult['chartData']['chartProps']['id']?>"></div>

<script>

	BX.Currency.setCurrencyFormat(
		'<?=\CUtil::JSEscape($currency)?>',
		<?= \CUtil::PhpToJSObject(\CCurrencyLang::GetFormatDescription($currency))?>
	);

	BX.message(<?=Json::encode($jsMessages)?>);
	BX.ready(function ()
	{
		BX.Catalog.Report.StoreStockChartManager.Instance = new BX.Catalog.Report.StoreStockChartManager(<?=CUtil::PhpToJSObject($arResult['chartData'])?>);
	});
</script>