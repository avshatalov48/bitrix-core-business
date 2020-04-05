<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
/** @var \Bitrix\Report\VisualConstructor\Fields\ComplexHtml $field */
$field = $arResult['CONFIGURATION_FIELD'];
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$id = $field->getId();
\CJSCore::init("color_picker");
?>

<div id="<?= $id ?>" <?= $field->getRenderedClassAttributes() ?>>
	<?= $field->getContent() ?>
</div>

<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.ComplexHtml({
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>