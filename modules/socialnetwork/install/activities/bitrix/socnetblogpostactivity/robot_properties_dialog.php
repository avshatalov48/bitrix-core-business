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
<?php if (isset($map['Attachment'], $map['AttachmentType'])): ?>
<div class="bizproc-automation-popup-settings">
	<?php
		$attachment = $map['Attachment'];
		$attachmentType = $map['AttachmentType'];

		$config = [
			'type' => $dialog->getCurrentValue($attachmentType['FieldName']),
			'typeInputName' => $attachmentType['FieldName'],
			'valueInputName' => $attachment['FieldName'],
			'multiple' => $attachment['Multiple'],
			'required' => !empty($attachment['Required']),
			'useDisk' => CModule::IncludeModule('disk'),
			'label' => $attachment['Name'],
			'labelFile' => $attachmentType['Options']['file'],
			'labelDisk' => $attachmentType['Options']['disk']
		];

		if ($dialog->getCurrentValue($attachmentType['FieldName']) === 'disk')
		{
			$config['selected'] = \Bitrix\Crm\Automation\Helper::prepareDiskAttachments(
				$dialog->getCurrentValue($attachment['FieldName'])
			);
		}
		else
		{
			$config['selected'] = \Bitrix\Crm\Automation\Helper::prepareFileAttachments(
				$dialog->getDocumentType(),
				$dialog->getCurrentValue($attachment['FieldName'])
			);
		}
		$configAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($config));
	?>
	<div class="bizproc-automation-popup-settings" data-role="file-selector" data-config="<?=$configAttributeValue?>"></div>
</div>
<?php endif; ?>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['Tags']['Name'])?>:
	</span>
	<?= $dialog->renderFieldControl($map['Tags'])?>
</div>
