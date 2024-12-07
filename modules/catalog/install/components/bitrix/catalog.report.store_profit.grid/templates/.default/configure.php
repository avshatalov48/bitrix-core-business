<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'catalog.config.settings']);
?>

<div class="catalog-report-config">
	<div class="catalog-report-config-title"><?= Loc::getMessage('CATALOG_REPORT_CONFIG_TITLE_MSGVER_1') ?></div>
</div>

<script>
	BX.ready(() => {
		const button = new BX.UI.Button({
			text: <?= CUtil::PhpToJSObject(Loc::getMessage('CATALOG_REPORT_CONFIG_BUTTON')) ?>,
			color: BX.UI.Button.Color.PRIMARY,
			onclick: () => {
				BX.Catalog.Config.Slider.open('report', {
					events: {
						onClose: () => {
							// if we simply reload the page, the first report that was opened in the slider will open again
							new BX.Report.Analytics.Page({
								scope: document.getElementById('report-analytics-page'),
								menuScope: document.getElementById('report-analytic-left-menu'),
								defaultBoardKey: 'catalog_warehouse_profit',
							});
						}
					}
				});
			},
		});
		const container = document.querySelector('.catalog-report-config');
		button.renderTo(container);
	});
</script>
