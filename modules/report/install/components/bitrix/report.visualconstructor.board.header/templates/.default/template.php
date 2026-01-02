<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Report\VisualConstructor\Helper\Filter;

/** @var \Bitrix\Report\VisualConstructor\Helper\Filter $filter */
$filter = $arResult['FILTER'];

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . ' no-background no-all-paddings pagetitle-toolbar-field-view ');

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

if ($arResult['FILTER'] instanceof Filter): ?>
	<? foreach ($arResult['FILTER']->getStringList() as $str): ?>
		<?= $str ?>
	<? endforeach; ?>
	<? if ($arResult['IS_FRAME_MODE']): ?>
		<div class="report-vc-filter">
			<div class="report-vc-filter-flex">
			<?
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				$filter->getFilterParameters(),
				$component,
				array()
			);
			?>
			</div>
		</div>
	<? endif ?>
<?endif; ?>
