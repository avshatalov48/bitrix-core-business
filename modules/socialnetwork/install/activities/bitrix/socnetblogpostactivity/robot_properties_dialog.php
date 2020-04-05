<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
$postMessage = $map['PostMessage'];
$postTitle = $map['PostTitle'];

$authorConfigAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode(array(
	'valueInputName' => $map['OwnerId']['FieldName'],
	'selected'       => \Bitrix\Crm\Automation\Helper::prepareUserSelectorEntities(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($map['OwnerId']['FieldName'], $map['OwnerId']['Default'])
	),
	'multiple' => false,
	'required' => true,
)));

$toConfigAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode(array(
	'valueInputName' => $map['UsersTo']['FieldName'],
	'selected'       => \Bitrix\Crm\Automation\Helper::prepareUserSelectorEntities(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($map['UsersTo']['FieldName'], $map['UsersTo']['Default'])
	),
	'multiple' => true,
	'required' => true,
)));

?>
<div class="crm-automation-popup-settings">
	<input name="<?=htmlspecialcharsbx($postTitle['FieldName'])?>" type="text" class="crm-automation-popup-input"
		   value="<?=htmlspecialcharsbx($dialog->getCurrentValue($postTitle['FieldName']))?>"
		   placeholder="<?=htmlspecialcharsbx($postTitle['Name'])?>"
		   data-role="inline-selector-target"
	>
</div>
<div class="crm-automation-popup-settings">
	<textarea name="<?=htmlspecialcharsbx($postMessage['FieldName'])?>"
			  class="crm-automation-popup-textarea"
			  placeholder="<?=htmlspecialcharsbx($postMessage['Name'])?>"
			  data-role="inline-selector-target"
	><?=htmlspecialcharsbx($dialog->getCurrentValue($postMessage['FieldName'], $postMessage['Default']))?></textarea>
</div>
<div class="crm-automation-popup-settings">
	<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['OwnerId']['Name'])?>:
	</span>
	<div data-role="user-selector" data-config="<?= $authorConfigAttributeValue ?>"></div>
</div>
<div class="crm-automation-popup-settings">
<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete">
	<?=htmlspecialcharsbx($map['UsersTo']['Name'])?>:</span>
	<div data-role="user-selector" data-config="<?= $toConfigAttributeValue ?>"></div>
</div>