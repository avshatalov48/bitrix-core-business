<?php

use Bitrix\Mail\Internals\UserSignatureTable;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load("ui.notification");
\CJSCore::init("sidepanel");
$isIframe = isset($arResult["IFRAME"]) && $arResult["IFRAME"] === "Y";

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view')));

if ($isIframe)
{?>
<div class="mail-signature-edit-wrap">
	<div class="mail-signature-is-iframe">
<?}?>

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
	]);
	?></div>


<?if ($isIframe)
{?>
	</div>
<?}
?>

	<div class="sender-select" id="sender-select-row">
		<input type="hidden" name="signatureId" value="<?=$arResult['signatureId'];?>" id="mail-signature-signature-id" />
		<input type="checkbox" name="sender_bind" value="y" id="sender_bind_checkbox"
		<?
		if($arResult['signatureId'] > 0 && $arResult['sender'])
		{
			?> checked<?
		}
		?> />
		<span class="mail-signature-edit-sender-text"><?=Loc::getMessage('MAIL_USERSIGNATURE_SENDER_SELECT') ?></span>
		<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_user_signature_sender_type_<?=$arResult['senderType'];?>">
			<input id="mail_user_signature_sender_type_0" type="radio" name="sender_type" value="0">
			<label for="mail_user_signature_sender_type_0"><?=Loc::getMessage('MAIL_USERSIGNATURE_SENDER_TYPE_'.strtoupper($arResult['senderType'])) ?></label>
			<div class="mail-set-singleselect-wrapper">
				<input type="radio" name="sender_type" value="<?=UserSignatureTable::TYPE_SENDER;?>" id="mail_user_signature_sender_type_<?=UserSignatureTable::TYPE_SENDER;?>" onchange="BX.Mail.UserSignature.Edit.showList('<?=UserSignatureTable::TYPE_SENDER;?>');"<?
				if($arResult['senderType'] != UserSignatureTable::TYPE_ADDRESS)
				{
					?> checked<?
				}?>>
				<label for="mail_user_signature_sender_type_<?=UserSignatureTable::TYPE_SENDER;?>"><?=Loc::getMessage('MAIL_USERSIGNATURE_SENDER_TYPE_SENDER') ?></label>
				<input type="radio" name="sender_type" value="<?=UserSignatureTable::TYPE_ADDRESS;?>" id="mail_user_signature_sender_type_<?=UserSignatureTable::TYPE_ADDRESS;?>" onchange="BX.Mail.UserSignature.Edit.showList('<?=UserSignatureTable::TYPE_ADDRESS;?>');"<?
				if($arResult['senderType'] == UserSignatureTable::TYPE_ADDRESS)
				{
					?> checked<?
				}?>>
				<label for="mail_user_signature_sender_type_<?=UserSignatureTable::TYPE_ADDRESS;?>"><?=Loc::getMessage('MAIL_USERSIGNATURE_SENDER_TYPE_ADDRESS') ?></label>
			</div>
		</label>

		<?$display = 'inline-block';
		if($arResult['senderType'] != UserSignatureTable::TYPE_SENDER)
		{
			$display = 'none';
		}
		?>
		<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_user_signature_sender_<?=htmlspecialcharsbx($arResult['selectedSender']);?>" style="display: <?=$display;?>" id="mail_user_signature_list_<?=UserSignatureTable::TYPE_SENDER;?>">
			<input id="mail_user_signature_sender_0" type="radio" name="sender" value="0">
			<label for="mail_user_signature_sender_0"><?=htmlspecialcharsbx($arResult['selectedSender']);?></label>
			<div class="mail-set-singleselect-wrapper">
				<?
				foreach($arResult['senders'] as $sender)
				{
					?>
					<input type="radio" name="sender" value="<?=htmlspecialcharsbx($sender);?>" id="mail_user_signature_sender_<?=htmlspecialcharsbx($sender);?>"<?
					if($sender == $arResult['selectedSender'])
					{
						echo ' checked';
					}?>>
					<label for="mail_user_signature_sender_<?=htmlspecialcharsbx($sender);?>"><?=htmlspecialcharsbx($sender);?></label>
					<?
				}
				?>
			</div>
		</label>

		<?$display = 'none';
		if($arResult['senderType'] == UserSignatureTable::TYPE_ADDRESS)
		{
			$display = 'inline-block';
		}
		?>
		<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_user_signature_address_<?=htmlspecialcharsbx($arResult['selectedAddress']);?>" style="display: <?=$display;?>;" id="mail_user_signature_list_<?=UserSignatureTable::TYPE_ADDRESS;?>">
			<input id="mail_user_signature_address_0" type="radio" name="address" value="0">
			<label for="mail_user_signature_address_0"><?=htmlspecialcharsbx($arResult['selectedAddress']);?></label>
			<div class="mail-set-singleselect-wrapper">
				<?
				foreach($arResult['addresses'] as $sender)
				{
					?>
					<input type="radio" name="address" value="<?=htmlspecialcharsbx($sender);?>" id="mail_user_signature_address_<?=htmlspecialcharsbx($sender);?>"<?
					if($sender == $arResult['selectedAddress'])
					{
						echo ' checked';
					}?>>
					<label for="mail_user_signature_address_<?=htmlspecialcharsbx($sender);?>"><?=htmlspecialcharsbx($sender);?></label>
					<?
				}
				?>
			</div>
		</label>
	</div>
</div>

	<script>
		BX.ready(function() {
			BX.Mail.UserSignature.Edit.init(<?=CUtil::PhpToJSObject($arResult);?>);
			<?='BX.message('.\CUtil::PhpToJSObject(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)).');'?>
		});
	</script>

<?
if($isIframe)
{
	$closeLink = '';
}
else
{
	$closeLink = $arParams['PATH_TO_MAIL_SIGNATURES'];
}

$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'BUTTONS' => [
		[
			'TYPE' => 'save',
			'ONCLICK' => 'BX.Mail.UserSignature.Edit.save(true)',
		],
//		((isset($arResult['signatureId']) && $arResult['signatureId'] > 0) ?
//		[
//			'TYPE' => 'apply',
//			'ONCLICK' => 'BX.Mail.UserSignature.Edit.save()',
//		] : null),
		[
			'TYPE' => 'close',
			'LINK' => $closeLink,
		]
	]
]);?>
