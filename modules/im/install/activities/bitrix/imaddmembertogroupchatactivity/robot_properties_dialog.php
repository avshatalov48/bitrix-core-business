<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
foreach ($dialog->getMap() as $property): ?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
			<?= htmlspecialcharsbx($property['Name']) ?>:
		</span>
		<?= $dialog->renderFieldControl($property) ?>
	</div>
<?php
endforeach;
?>
