<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Report\VisualConstructor\Fields\Image $field */
$field = $arResult['CONFIGURATION_FIELD'];
$uri = $field->getUri();
$events = $arResult['CONFIGURATION_FIELD_EVENTS'];
$behaviours = $arResult['CONFIGURATION_FIELD_BEHAVIOURS'];
$id = $field->getId();
?>

<img id="<?= $id ?>" src="<?= $uri ?>">
<script>
	new BX.Report.VisualConstructor.Widget.Config.Fields.Image({
		fieldScope: BX("<?=$id?>"),
		events:  <?=CUtil::PhpToJSObject($events)?>,
		behaviours:  <?=CUtil::PhpToJSObject($behaviours)?>
	});
</script>