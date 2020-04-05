<?
\Bitrix\Main\UI\Extension::load("ui.icons");
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . ' no-background no-all-paddings pagetitle-toolbar-field-view ');

?>
<div class="report-visualconstructor-dashboard-mask">
	<div class="report-visualconstructor-dashboard-mask-img"></div>
	<div class="report-visualconstructor-dashboard-mask-content">
		<div class="report-visualconstructor-dashboard-mask-blur-box"></div>
		<div class="report-visualconstructor-dashboard-mask-text"><?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_VISUALCONSTRUCTOR_DASHBOARD_MASK_TEXT')?></div>
	</div>
</div>
