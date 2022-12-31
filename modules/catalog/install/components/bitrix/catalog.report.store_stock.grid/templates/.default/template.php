<?php

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
	if (isset($row['columns']['TITLE'], $row['columns']['STORE_ID']))
	{
		$row['columns']['TITLE'] =
			'<a class="store-report-link" onclick="BX.Catalog.Report.StoreStock.StoreGrid.Instance.openStoreProductListGrid('
			. $row['columns']['STORE_ID']
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
		BX.Catalog.Report.StoreStock.StoreGrid.Instance = new BX.Catalog.Report.StoreStock.StoreGrid({
			productListSliderFilter: <?=Cutil::PhpToJSObject($arResult['GRID_FILTER'])?>,
			productListSliderUrl: '<?=CUtil::JSEscape($arResult['PRODUCT_LIST_SLIDER_URL'])?>',
			gridId: '<?=CUtil::JSEscape($arResult['GRID']['GRID_ID'])?>'
		});
	});
</script>
