<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$messageText = $map['MessageText'];
$fromAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode(array(
	'valueInputName' => $map['MessageUserFrom']['FieldName'],
	'selected'       => \Bitrix\Crm\Automation\Helper::prepareUserSelectorEntities(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($map['MessageUserFrom']['FieldName'], $map['MessageUserFrom']['Default'])
	),
	'multiple' => false
)));

$toAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode(array(
	'valueInputName' => $map['MessageUserTo']['FieldName'],
	'selected'       => \Bitrix\Crm\Automation\Helper::prepareUserSelectorEntities(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($map['MessageUserTo']['FieldName'], $map['MessageUserTo']['Default'])
	),
	'multiple' => true,
	'required' => true,
)));
?>
<div class="crm-automation-popup-settings">
	<textarea name="<?=htmlspecialcharsbx($messageText['FieldName'])?>"
			class="crm-automation-popup-textarea"
			placeholder="<?=htmlspecialcharsbx($messageText['Name'])?>"
			data-role="inline-selector-target"
	><?=htmlspecialcharsbx($dialog->getCurrentValue($messageText['FieldName'], $messageText['Default']))?></textarea>
</div>
<div class="crm-automation-popup-settings">
	<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['MessageUserFrom']['Name'])?>:
	</span>
	<div data-role="user-selector" data-config="<?= $fromAttributeValue ?>"></div>
</div>
<div class="crm-automation-popup-settings">
	<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['MessageUserTo']['Name'])?>:
	</span>
	<div data-role="user-selector" data-config="<?= $toAttributeValue ?>"></div>
</div>
<input type="hidden" name="message_format" value="robot">
