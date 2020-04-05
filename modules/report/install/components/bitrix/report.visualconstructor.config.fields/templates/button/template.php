<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Button $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$id = $field->getId();
?>

<button <?= $field->getRenderedIdAttribute(); ?>  <?= $field->getRenderedDataAttributes(); ?>  <?= $field->getRenderedClassAttributes(); ?> ><?= $field->getLabel() ?></button>

<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.Button({
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>