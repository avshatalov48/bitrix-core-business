<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
$map = $dialog->getMap();
$postMessage = $map['PostMessage'];
$postTitle = $map['PostTitle'];
?>
<div class="bizproc-automation-popup-settings">
	<input name="<?=htmlspecialcharsbx($postTitle['FieldName'])?>" type="text" class="bizproc-automation-popup-input"
		   value="<?=htmlspecialcharsbx($dialog->getCurrentValue($postTitle['FieldName']))?>"
		   placeholder="<?=htmlspecialcharsbx($postTitle['Name'])?>"
		   data-role="inline-selector-target"
	>
</div>
<div class="bizproc-automation-popup-settings">
	<textarea name="<?=htmlspecialcharsbx($postMessage['FieldName'])?>"
			  class="bizproc-automation-popup-textarea"
			  placeholder="<?=htmlspecialcharsbx($postMessage['Name'])?>"
			  data-role="inline-selector-target"
	><?=htmlspecialcharsbx($dialog->getCurrentValue($postMessage['FieldName'], $postMessage['Default']))?></textarea>
</div>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['OwnerId']['Name'])?>:
	</span>
	<?
	if (method_exists($dialog, 'renderFieldControl')):
		echo $dialog->renderFieldControl($map['OwnerId']);
	else:
		$authorConfigAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode(array(
			'valueInputName' => $map['OwnerId']['FieldName'],
			'selected'       => \Bitrix\Bizproc\Automation\Helper::prepareUserSelectorEntities(
				$dialog->getDocumentType(),
				$dialog->getCurrentValue($map['OwnerId']['FieldName'], $map['OwnerId']['Default'])
			),
			'multiple' => false,
			'required' => true,
		)));
	?>
	<div data-role="user-selector" data-config="<?= $authorConfigAttributeValue ?>"></div>
	<?endif?>
</div>
<div class="bizproc-automation-popup-settings">
<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
	<?=htmlspecialcharsbx($map['UsersTo']['Name'])?>:</span>
	<?
	if (method_exists($dialog, 'renderFieldControl')):
		echo $dialog->renderFieldControl($map['UsersTo']);
	else:
		$toConfigAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode(array(
			'valueInputName' => $map['UsersTo']['FieldName'],
			'selected'       => \Bitrix\Bizproc\Automation\Helper::prepareUserSelectorEntities(
				$dialog->getDocumentType(),
				$dialog->getCurrentValue($map['UsersTo']['FieldName'], $map['UsersTo']['Default'])
			),
			'multiple' => true,
			'required' => true,
		)));
		?>
		<div data-role="user-selector" data-config="<?= $toConfigAttributeValue ?>"></div>
	<?endif?>
</div>