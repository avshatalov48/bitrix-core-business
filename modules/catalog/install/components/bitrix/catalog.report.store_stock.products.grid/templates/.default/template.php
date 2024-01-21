<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.alerts',
]);

global $APPLICATION;

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES'])): ?>
<?php foreach($arResult['ERROR_MESSAGES'] as $error):?>
	<div class="ui-alert ui-alert-danger" style="margin-bottom: 0px;">
		<span class="ui-alert-message"><?= htmlspecialcharsbx($error) ?></span>
	</div>
<?php endforeach;?>
<?php
	return;
endif;

$title =
	$arParams['STORE_ID'] > 0
		? Loc::getMessage('STORE_STOCK_REPORT_PRODUCT_GRID_TITLE',['#STORE_TITLE#' => $arResult['STORE_TITLE']])
		: Loc::getMessage('STORE_STOCK_REPORT_PRODUCT_GRID_ALL_STORES_TITLE')
;

$APPLICATION->SetTitle($title);

$this->setViewTarget('below_pagetitle');
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.filter',
	'',
	$arResult['FILTER_OPTIONS']
);
$this->endViewTarget();

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);
?>

<script>
	function onBeforeDialogSearch(event)
	{
		const dialog = event.getTarget();
		dialog.removeEntityItems('product_variation');
	}

	BX.addCustomEvent('DocumentCard:onBeforeEntityRedirect', function () {
		const grid = BX.Main.gridManager.getInstanceById('<?= CUtil::JSEscape($arResult['GRID']['GRID_ID']) ?>');
		if (grid)
		{
			grid.reload();
		}
	});
</script>
