<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
$availableViewList = $arParams['AVAILABLE_VIEWS'];
/** @var array $preparedWidget */
$preparedWidget = $arParams['PREPARED_WIDGET'];
/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\PreviewBlock $field */
$field = $arResult['CONFIGURATION_FIELD'];
$fieldValue = $field->getValue();
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$fieldName = $field->getName();
$id = $field->getId();

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

?>
<div class="report-configuration-item report-configuration-previewblock-item">
	<div class="report-configuration-col-content report-widget-configuration-preview-block">
		<div class="report-widget-selected-view-container">
			<div class="report-widget-selected-view-wrapper"></div>
		</div>
		<div class="report-widget-view-select-variants">
			<? foreach ($availableViewList as $view): ?>
				<div data-type="view-miniature-box" data-view-key="<?= $view['key'] ?>" class="report-widget-view-miniature-container <?= $fieldValue == $view['key'] ? 'report-widget-view-miniature-container-active' : '' ?>">
					<img src="<?= $view['logoUrl'] ?>" title="<?= $view['label'] ?>"/>
				</div>
			<? endforeach; ?>
		</div>
		<input type="hidden" id="<?= $id ?>" name="<?= $fieldName ?>" value="<?= $fieldValue ?>" data-role="preview-view-type-key">
	</div>
</div>


<script>
	BX.message({'REPORT_CHANGE_VIEW_ATTENTION_TEXT': "<?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_CHANGE_VIEW_ATTENTION_TEXT')?>"});
	BX.message({'REPORT_CHANGE_VIEW_ATTENTION_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_CHANGE_VIEW_ATTENTION_TITLE')?>"});
	BX.message({'REPORT_CHANGE_VIEW_CHANGE_CONFIRM_BUTTON_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_CHANGE_VIEW_CHANGE_CONFIRM_BUTTON_TITLE')?>"});
	BX.message({'REPORT_CHANGE_VIEW_CHANGE_CANCEL_BUTTON_TITLE': "<?=\Bitrix\Main\Localization\Loc::getMessage('REPORT_CHANGE_VIEW_CHANGE_CANCEL_BUTTON_TITLE')?>"});
	new BX.Report.VisualConstructor.Widget.Config.Fields.PreviewBlock({
		previewBlock: document.querySelector('.report-widget-configuration-preview-block'),
		previewBlockWidgetContainer: document.querySelector('.report-widget-selected-view-wrapper'),
		widgetOptions: <?=CUtil::PhpToJSObject($preparedWidget, false, false, true)?>,
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>,
		value: <?=CUtil::PhpToJSObject($fieldValue)?>
	});
</script>