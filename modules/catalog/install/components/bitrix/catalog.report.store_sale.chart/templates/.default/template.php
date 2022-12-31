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
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'amcharts4',
	'amcharts4_theme_animated',
	'currency',
	'ui.icons',
	'ui.hint',
	'ui.alerts',
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
	'STORE_SALE_CHART_SUM_SHIPPED_SERIES_TITLE',
	'STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_TITLE',
	'STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_SUM',
	'STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_SOLD_PERCENT',
	'STORE_SALE_CHART_SUM_ARRIVED_SERIES_TITLE',
	'STORE_SALE_CHART_SUM_ARRIVED_SERIES_POPUP_TITLE',
	'STORE_SALE_CHART_SUM_ARRIVED_SERIES_POPUP_SUM',
];

$jsMessages = [];

foreach ($jsMessagesCodes as $code)
{
	$jsMessages[$code] = Loc::getMessage($code);
}

?>

<div class="store-sale-chart" id="<?=$arResult['chartData']['chartProps']['id']?>"></div>

<script>

	BX.Currency.setCurrencyFormat(
		'<?=\CUtil::JSEscape($arResult['chartData']['currency'])?>',
		<?= \CUtil::PhpToJSObject(\CCurrencyLang::GetFormatDescription($arResult['chartData']['currency']))?>
	);

	BX.message(<?=Json::encode($jsMessages)?>);
	BX.ready(function ()
	{
		BX.Catalog.Report.StoreSaleChartManager.Instance = new BX.Catalog.Report.StoreSaleChartManager(<?=CUtil::PhpToJSObject($arResult['chartData'])?>);
	});
</script>