<?php
/**
 * @global \CMain $APPLICATION
 *
 * @var $component \CatalogReportStoreProfitGridComponent
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 * @var array $arParams
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
	<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
		<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
			<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
		</div>
	<?php endforeach;?>
	<?php
	return;
endif;

foreach ($arResult['GRID']['ROWS'] as &$row)
{
	if (isset($row['columns']['TITLE']) && $row['id'] !== 'overallTotal')
	{
		$storeId = (int)($row['columns']['STORE_ID'] ?? 0);
		$row['columns']['TITLE'] =
			'<a class="store-report-link" onclick="BX.Catalog.Report.StoreProfit.StoreGrid.Instance.openStoreProductListGrid('
			. $storeId
			. ')">'
			. $row['columns']['TITLE']
			. '</a>'
		;
	}
}

global $APPLICATION;
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);

$storeGridParams = [
	'productListSliderFilter' => $arResult['GRID_FILTER'],
	'productListSliderUrl' => $arResult['PRODUCT_LIST_SLIDER_URL'],
	'gridId' => $arResult['GRID']['GRID_ID'],
];
?>

<script>
	function onBeforeDialogSearch(event)
	{
		const dialog = event.target;
		if (dialog)
		{
			dialog.removeEntityItems('product_variation');
		}
	}

	BX.ready(() => {
		BX.Catalog.Report.StoreProfit.StoreGrid.Instance = new BX.Catalog.Report.StoreProfit.StoreGrid(<?= CUtil::PhpToJSObject($storeGridParams) ?>);
	});
</script>
