<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.buttons",
]);

/** @var array $arResult */
$widgetGId = $arResult['WIDGET_GID'];
$pageTitle = $arResult['PAGE_TITLE'];
$mode = $arResult['MODE'];
$form = $arResult['FORM'];
?>
<div class="report-widget-configuration" data-role="report-configuration-page-wrapper">
	<div class="pagetitle">
		<span class="pagetitle-item">
			<?= $pageTitle ?>
		</span>
	</div>
	<div class="report-widget-configuration-form-wrapper">
		<?php $form->render(); ?>
	</div>

	<script>
		new BX.Report.VisualConstructor.Widget.Form(
			BX(<?=CUtil::PhpToJSObject('report_widget_configuration_form_' . $widgetGId)?>),
			{
				widgetId: <?=CUtil::PhpToJSObject($widgetGId)?>,
				mode: <?=CUtil::PhpToJSObject($mode)?>
			}
		);
	</script>
</div>