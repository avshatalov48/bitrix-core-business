<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$handler = $map['Handler'];
?>
<div class="bizproc-automation-popup-settings">
	<textarea name="<?=htmlspecialcharsbx($handler['FieldName'])?>"
			class="bizproc-automation-popup-textarea"
			placeholder="<?=htmlspecialcharsbx($handler['Name'])?>"
			data-role="inline-selector-target"
	><?=htmlspecialcharsbx($dialog->getCurrentValue($handler['FieldName'], ''))?></textarea>
</div>