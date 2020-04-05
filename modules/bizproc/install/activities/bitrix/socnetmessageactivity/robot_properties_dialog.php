<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$messageText = $map['MessageText'];
?>
<div class="bizproc-automation-popup-settings">
	<textarea name="<?=htmlspecialcharsbx($messageText['FieldName'])?>"
			class="bizproc-automation-popup-textarea"
			placeholder="<?=htmlspecialcharsbx($messageText['Name'])?>"
			data-role="inline-selector-target"
	><?=htmlspecialcharsbx($dialog->getCurrentValue($messageText['FieldName'], $messageText['Default']))?></textarea>
</div>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['MessageUserFrom']['Name'])?>:
	</span>
	<?=$dialog->renderFieldControl($map['MessageUserFrom'])?>
</div>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['MessageUserTo']['Name'])?>:
	</span>
	<?=$dialog->renderFieldControl($map['MessageUserTo'])?>
</div>
<input type="hidden" name="message_format" value="robot">
