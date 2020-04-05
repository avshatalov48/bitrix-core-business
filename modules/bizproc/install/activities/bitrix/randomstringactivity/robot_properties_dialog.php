<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();

foreach ($map as $id => $field):?>
	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
			<?=htmlspecialcharsbx($field['Name'])?>:
		</span>
		<?
		echo $dialog->renderFieldControl($field);
		?>
	</div>
<?
endforeach;