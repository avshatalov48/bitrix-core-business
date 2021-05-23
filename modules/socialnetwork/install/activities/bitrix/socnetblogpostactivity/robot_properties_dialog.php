<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
?>
<div class="bizproc-automation-popup-settings">
	<?= $dialog->renderFieldControl($map['PostTitle'])?>
</div>
<div class="bizproc-automation-popup-settings">
	<?= $dialog->renderFieldControl($map['PostMessage'])?>
</div>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['OwnerId']['Name'])?>:
	</span>
	<?= $dialog->renderFieldControl($map['OwnerId'])?>
</div>
<div class="bizproc-automation-popup-settings">
<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
	<?=htmlspecialcharsbx($map['UsersTo']['Name'])?>:</span>
	<?= $dialog->renderFieldControl($map['UsersTo'])?>
</div>