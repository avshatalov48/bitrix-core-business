<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
?>
<div class="bizproc-automation-popup-settings">
	<?= $dialog->renderFieldControl($map['MessageText'])?>
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
