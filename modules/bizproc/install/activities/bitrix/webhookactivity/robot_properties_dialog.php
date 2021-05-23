<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
?>
<div class="bizproc-automation-popup-settings">
	<?= $dialog->renderFieldControl($map['Handler'])?>
</div>