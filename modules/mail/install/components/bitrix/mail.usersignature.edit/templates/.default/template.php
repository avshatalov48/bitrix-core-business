<?php

use Bitrix\Mail\Internals\UserSignatureTable;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'sidepanel',
	'ui.forms',
	'ui.buttons',
	'ui.alerts',
	'ui.notification',
]);

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view')));
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view no-background')));
?>
<div class="mail-user-signature-editor-wrapper">
	<div id="signature-alert-container">
	</div>
	<div id="signature-editor-container">
		<?
		$editor = new CHTMLEditor;
		$editor->show([
			'name'                => 'signature-editor-name',
			'id'                  => 'signature-editor-id',
			'siteId'              => SITE_ID,
			'width'               => "100%",
			'minBodyWidth'        => "100%",
			'normalBodyWidth'     => 680,
			'height'              => 300,
			'minBodyHeight'       => 300,
			'showTaskbars'        => false,
			'showNodeNavi'        => false,
			'autoResize'          => true,
			'autoResizeOffset'    => 40,
			'bbCode'              => false,
			'saveOnBlur'          => false,
			'bAllowPhp'           => false,
			'limitPhpAccess'      => false,
			'setFocusAfterShow'   => false,
			'askBeforeUnloadPage' => true,
			'useFileDialogs' => false,
			'useLinkStat' => false,
			'controlsMap'         => [
				['id' => 'Bold',  'compact' => true, 'sort' => 10],
				['id' => 'Italic',  'compact' => true, 'sort' => 20],
				['id' => 'Underline',  'compact' => true, 'sort' => 30],
				['id' => 'Strikeout',  'compact' => true, 'sort' => 40],
				['id' => 'RemoveFormat',  'compact' => true, 'sort' => 50],
				['id' => 'Color',  'compact' => true, 'sort' => 60],
				['id' => 'FontSelector',  'compact' => true, 'sort' => 70],
				['id' => 'FontSize',  'compact' => true, 'sort' => 80],
				['separator' => true, 'compact' => true, 'sort' => 90],
				['id' => 'OrderedList',  'compact' => true, 'sort' => 100],
				['id' => 'UnorderedList',  'compact' => true, 'sort' => 110],
				['id' => 'AlignList', 'compact' => true, 'sort' => 120],
				['separator' => true, 'compact' => true, 'sort' => 130],
				['id' => 'InsertLink',  'compact' => true, 'sort' => 140],
				['id' => 'InsertImage',  'compact' => true, 'sort' => 150],
				['id' => 'InsertTable',  'compact' => true, 'sort' => 170],
				['id' => 'Code',  'compact' => true, 'sort' => 180],
				['id' => 'Quote',  'compact' => true, 'sort' => 190],
				['separator' => true, 'compact' => true, 'sort' => 200],
				['id' => 'Fullscreen',  'compact' => true, 'sort' => 210],
				['id' => 'BbCode',  'compact' => true, 'sort' => 220],
				['id' => 'More',  'compact' => true, 'sort' => 400]
			],
			'content' => $arResult['signature'],
			'isCopilotEnabled' => false,
		]);
		?>
	</div>
	<div class="sender-select mail-adding-signature-selecting-binding" id="sender-select-row">
		<input type="hidden" name="signatureId" value="<?=$arResult['signatureId'];?>" id="mail-signature-signature-id" />
		<input type="checkbox" name="sender_bind" value="y" id="sender_bind_checkbox"
		<?
		if($arResult['signatureId'] > 0 && $arResult['sender'])
		{
			?> checked<?
		}
		?> />

		<span class="mail-signature-edit-sender-text"><?=Loc::getMessage('MAIL_USERSIGNATURE_SENDER_SELECT') ?></span>

		<div id="binding-type-field-wrapper"></div>

	</div>
</div>

<script>
	BX.ready(function() {
		<?='BX.message('.\CUtil::PhpToJSObject(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)).');'?>
		BX.Mail.UserSignature.Edit.init(<?=CUtil::PhpToJSObject($arResult);?>);
	});
</script>
