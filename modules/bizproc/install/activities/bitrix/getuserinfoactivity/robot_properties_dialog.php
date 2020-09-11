<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$user = $map['GetUser'];
$selectedUser = $dialog->getCurrentValue($user['FieldName']);

?>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($user['Name'])?></span>
	<?=$dialog->renderFieldControl($user, $selectedUser)?>
</div>
