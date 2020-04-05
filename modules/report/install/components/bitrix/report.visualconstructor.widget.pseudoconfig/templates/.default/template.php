<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
$widgetId = $arResult['WIDGET_ID'];
/** @var \Bitrix\Report\VisualConstructor\Entity\Widget $widget */
$widget = $arResult['WIDGET'];
/** @var \Bitrix\Report\VisualConstructor\Handler\BaseReport $pseudoReportHandler */
$pseudoReportHandler = $arResult['REPORT_HANDLER'];
$pseudoReportId = $pseudoReportHandler->getReport()->getGId();
?>
<div class="report-configuration-container" data-report-id="<?= $pseudoReportId ?>" data-is-pseudo="1" data-role="report-configuration-container">
	<?php foreach ($pseudoReportHandler->getFormElements() as $configurationField): ?>
		<?php $configurationField->render(); ?>
	<?php endforeach; ?>
</div>
<script>
	new BX.VisualConstructor.Widget.PseudoReportConfigs({
		pseudoConfigurationScope: document.querySelector(".report-configuration-container[data-report-id='<?=$pseudoReportId?>']")
	});
</script>