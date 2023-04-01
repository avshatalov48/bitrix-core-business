<?php

use Bitrix\Main\UI\Extension;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Extension::load([
	'ui.layout-form',
	'ui.forms',
	'ui.buttons',
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.common',
	'ui.sidepanel-content',
	'ui.notification',
	'ui.messagecard',
]);
$cardSettings = $arResult['CARD_SETTINGS'];
?>

<div id="<?=$cardSettings['containerId']?>">
	<form id="<?=$cardSettings['formId']?>"></form>
	<?php
	if (!$cardSettings['readOnly'])
	{
		$buttons = [
			[
				'TYPE' => 'save',
				'ONCLICK' => 'BX.Catalog.SeoDetail.onClickSave(); return false;',
			],
			'cancel'
		];
		$APPLICATION->IncludeComponent(
			'bitrix:ui.button.panel',
			"",
			[
				'BUTTONS' => $buttons,
				'ALIGN' => "center"
			],
			false
		);
	}
	?>
</div>

<script>
	BX.message({
		CSD_LOWERCASE_CHECKBOX_INPUT_TITLE: '<?=GetMessageJS('CSD_LOWERCASE_CHECKBOX_INPUT_TITLE')?>',
		CSD_TRANSLITERATE_CHECKBOX_INPUT_TITLE: '<?=GetMessageJS('CSD_TRANSLITERATE_CHECKBOX_INPUT_TITLE')?>',
		CSD_WHITESPACE_CHARACTER_INPUT_TITLE: '<?=GetMessageJS('CSD_WHITESPACE_CHARACTER_INPUT_TITLE')?>',
		CSD_SAVE_MESSAGE_NOTIFICATION: '<?=GetMessageJS('CSD_SAVE_MESSAGE_NOTIFICATION')?>',
		CSD_INHERIT_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE: '<?=GetMessageJS('CSD_INHERIT_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE')?>',
		CSD_INHERIT_SECTION_OVERWRITE_CHECKBOX_INPUT_TITLE: '<?=GetMessageJS('CSD_INHERIT_SECTION_OVERWRITE_CHECKBOX_INPUT_TITLE')?>',
		CSD_INHERIT_SECTION_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE: '<?=GetMessageJS('CSD_INHERIT_SECTION_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE')?>',
		CSD_ELEMENT_INFO_MESSAGE_HELP_LINK_TITLE: '<?=GetMessageJS('CSD_ELEMENT_INFO_MESSAGE_HELP_LINK_TITLE')?>',
	});
	BX(function() {
		var editor = BX.Catalog.SeoDetail.create(<?=CUtil::PhpToJSObject($cardSettings)?>);
		editor.layout();
	});
</script>

