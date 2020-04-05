<?
\Bitrix\Main\UI\Extension::load("ui.icons");
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . ' no-background no-all-paddings pagetitle-toolbar-field-view ');
$isBitrix24Template = SITE_TEMPLATE_ID === "bitrix24";
if ($isBitrix24Template)
{
	$this->SetViewTarget('inside_pagetitle');
}
?>

<? if (!$isBitrix24Template): ?>
<div class="tasks-interface-filter-container">
	<? endif ?>

	<div class="pagetitle-container<? if (!$isBitrix24Template): ?> pagetitle-container-light<? endif ?> pagetitle-flexible-space">
		<div class="pagetitle-container pagetitle-align-right-container">

		</div>
	</div>
	<? if (!$isBitrix24Template): ?>
</div>
<? endif ?>
<?php
if ($isBitrix24Template)
{
	$this->EndViewTarget();
}
?>
<div class="report-visualconstructor-dashboard-mask">
	<div class="report-visualconstructor-dashboard-mask-img"></div>
	<div class="report-visualconstructor-dashboard-mask-content">
		<div class="report-visualconstructor-dashboard-mask-blur-box"></div>
		<div class="report-visualconstructor-dashboard-mask-text"><?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_VISUALCONSTRUCTOR_DASHBOARD_MASK_TEXT')?></div>
	</div>
</div>
