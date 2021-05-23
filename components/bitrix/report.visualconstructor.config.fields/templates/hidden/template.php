<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\Valuable\LabelField $field */
$field = $arResult['CONFIGURATION_FIELD'];
$fieldValue = $field->getValue();
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$fieldName = $field->getName();
$id = $field->getId();
?>

<input type="hidden" <?= $field->getRenderedIdAttribute(); ?>   <?= $field->getRenderedDataAttributes(); ?>  <?= $field->getRenderedClassAttributes(); ?> name="<?= $fieldName ?>" value="<?= $fieldValue ?>">
<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.Hidden({
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>