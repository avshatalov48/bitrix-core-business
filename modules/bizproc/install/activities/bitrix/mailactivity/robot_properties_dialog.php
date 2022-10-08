<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
/** @global \CMain $APPLICATION $map */
global $APPLICATION;

$map = $dialog->getMap();
$messageText = $map['MailText'];
$subject = $map['MailSubject'];
$messageType = $dialog->getCurrentValue($map['MailMessageType']['FieldName'], 'html');
$attachmentType = isset($map['FileType']) ? $map['FileType'] : null;
$attachment = isset($map['File']) ? $map['File'] : null;
$from = isset($map['MailUserFrom']) ? $map['MailUserFrom'] : null;
$fromValue = $from ? $dialog->getCurrentValue($from['FieldName'],'') : null;
$fromValue = \CBPHelper::UsersArrayToString($fromValue, $dialog->getWorkflowTemplate(), $dialog->getDocumentType());

$runtimeData = $dialog->getRuntimeData();
$mailboxes = $runtimeData['mailboxes'];

if ($from):?>
	<div style="display:none;">
		<?
		$APPLICATION->IncludeComponent('bitrix:main.mail.confirm', '');
		?>
	</div>
	<div class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text">
		<span class="bizproc-automation-popup-settings-title"><?=htmlspecialcharsbx($from['Name'])?>:</span>
		<input type="hidden" name="<?=htmlspecialcharsbx($from['FieldName'])?>" value="<?=htmlspecialcharsbx($fromValue)?>" data-role="mailbox-selector-value">
		<a class="bizproc-automation-popup-settings-link" data-role="mailbox-selector"></a>
	</div>
<?
endif;
?>

	<div class="bizproc-automation-popup-settings">
		<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
			<?=htmlspecialcharsbx($map['MailUserTo']['Name'])?>:
		</span>
		<?=$dialog->renderFieldControl($map['MailUserTo'])?>
	</div>

	<div class="bizproc-automation-popup-settings">
		<?=$dialog->renderFieldControl($subject)?>
	</div>

	<div class="bizproc-automation-popup-settings" data-role="inline-selector-html">
		<div class="bizproc-automation-popup-select"><?php
			$emailEditor = new CHTMLEditor;

			$content = $dialog->getCurrentValue($messageText['FieldName'], '');
			if ($dialog->getCurrentValue('mail_message_encoded'))
			{
				$content = \CBPMailActivity::decodeMailText($content);
				$content = \Bitrix\Bizproc\Automation\Helper::convertExpressions($content, $dialog->getDocumentType());
			}

			if ($messageType !== 'html')
			{
				$parser = new CTextParser();
				$content = $parser->convertText($content);
			}

			$emailEditor->show(array(
				'name'                => $messageText['FieldName'],
				'content'			  => $content,
				'siteId'              => SITE_ID,
				'width'               => '100%',
				'minBodyWidth'        => 630,
				'normalBodyWidth'     => 630,
				'height'              => 198,
				'minBodyHeight'       => 198,
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
				'controlsMap'         => array(
					array('id' => 'Bold',  'compact' => true, 'sort' => 10),
					array('id' => 'Italic',  'compact' => true, 'sort' => 20),
					array('id' => 'Underline',  'compact' => true, 'sort' => 30),
					array('id' => 'Strikeout',  'compact' => true, 'sort' => 40),
					array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 50),
					array('id' => 'Color',  'compact' => true, 'sort' => 60),
					array('id' => 'FontSelector',  'compact' => false, 'sort' => 70),
					array('id' => 'FontSize',  'compact' => false, 'sort' => 80),
					array('separator' => true, 'compact' => false, 'sort' => 90),
					array('id' => 'OrderedList',  'compact' => true, 'sort' => 100),
					array('id' => 'UnorderedList',  'compact' => true, 'sort' => 110),
					array('id' => 'AlignList', 'compact' => false, 'sort' => 120),
					array('separator' => true, 'compact' => false, 'sort' => 130),
					array('id' => 'InsertLink',  'compact' => true, 'sort' => 140),
					array('id' => 'InsertImage',  'compact' => false, 'sort' => 150),
					array('id' => 'InsertTable',  'compact' => false, 'sort' => 170),
					array('id' => 'Code',  'compact' => true, 'sort' => 180),
					array('id' => 'Quote',  'compact' => true, 'sort' => 190),
					array('separator' => true, 'compact' => false, 'sort' => 200),
					array('id' => 'Fullscreen',  'compact' => false, 'sort' => 210),
					array('id' => 'ChangeView',  'compact' => true, 'sort' => 220),
					array('id' => 'More',  'compact' => true, 'sort' => 400)
				),
			));
			?></div>
	</div>
	<input type="hidden" name="<?=htmlspecialcharsbx($map['MailMessageType']['FieldName'])?>" value="html">
	<input type="hidden" name="<?=htmlspecialcharsbx($map['MailCharset']['FieldName'])?>" value="<?=htmlspecialcharsbx(SITE_CHARSET)?>">
	<input type="hidden" name="<?=htmlspecialcharsbx($map['DirrectMail']['FieldName'])?>" value="Y">
	<input type="hidden" name="<?=htmlspecialcharsbx($map['MailSite']['FieldName'])?>" value="<?=htmlspecialcharsbx(SITE_ID)?>">
<?
$config = array(
	'type' => $dialog->getCurrentValue($attachmentType['FieldName']),
	'typeInputName' => $attachmentType['FieldName'],
	'valueInputName' => $attachment['FieldName'],
	'multiple' => $attachment['Multiple'],
	'required' => !empty($attachment['Required']),
	'useDisk' => CModule::IncludeModule('disk'),
	'label' => $attachment['Name'],
	'labelFile' => $attachmentType['Options']['file'],
	'labelDisk' => $attachmentType['Options']['disk']
);

if ($dialog->getCurrentValue($attachmentType['FieldName']) === 'disk')
{
	$config['selected'] = \Bitrix\Bizproc\Automation\Helper::prepareDiskAttachments(
		$dialog->getCurrentValue($attachment['FieldName'])
	);
}
else
{
	$config['selected'] = \Bitrix\Bizproc\Automation\Helper::prepareFileAttachments(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($attachment['FieldName'])
	);
}
$configAttributeValue = htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($config));
?>
	<div class="bizproc-automation-popup-settings" data-role="file-selector" data-config="<?=$configAttributeValue?>"></div>
<?if ($from):?>
	<script>

		BX.ready(function ()
		{
			var dialog = BX.Bizproc.Automation.Designer.getInstance().getRobotSettingsDialog();
			if (!dialog)
			{
				return;
			}

			var mailboxes = <?=\Bitrix\Main\Web\Json::encode($mailboxes);?>;

			var mailboxSelector = dialog.form.querySelector('[data-role="mailbox-selector"]');
			var mailboxSelectorValue = dialog.form.querySelector('[data-role="mailbox-selector-value"]');

			var setMailbox = function(value)
			{
				mailboxSelector.textContent = value ? value : '<?=GetMessageJS('BPMA_RPD_FROM_EMPTY')?>';
				mailboxSelectorValue.value = value;
			};

			var getMenuItems = function()
			{
				var i, menuItems = [];

				for (i = 0; i < mailboxes.length; ++i)
				{
					var mailbox = mailboxes[i];
					var mailboxName = mailbox['name'].length > 0
						? mailbox['name'] + ' <' + mailbox['email'] + '>'
						: mailbox['email'];

					menuItems.push({
						text: BX.util.htmlspecialchars(mailboxName),
						value: mailboxName,
						onclick: function(e, item)
						{
							this.popupWindow.close();
							setMailbox(item.value);
						}
					});
				}

				if (window.BXMainMailConfirm)
				{
					if (menuItems.length > 0)
					{
						menuItems.push({delimiter: true});
					}

					menuItems.push({
						text: '<?=GetMessageJS('BPMA_RPD_FROM_ADD')?>',
						onclick: function(e, item)
						{
							this.popupWindow.close();
							window.BXMainMailConfirm.showForm(function(mailbox)
							{
								mailboxes.push(mailbox);
								setMailbox(mailbox['name'].length > 0
									? mailbox['name'] + ' <' + mailbox['email'] + '>'
									: mailbox['email']);
							});
						}
					});
				}

				return menuItems;
			};

			BX.bind(mailboxSelector, 'click', function(e)
				{
					var menuId = 'bpma-mailboxes' + Math.random();
					BX.PopupMenu.show(
						menuId,
						this,
						getMenuItems(),
						{
							autoHide: true,
							offsetLeft: (BX.pos(this)['width'] / 2),
							angle: { position: 'top', offset: 0 },
							overlay: { backgroundColor: 'transparent' },
							events:
								{
									onPopupClose: function()
									{
										this.destroy();
									}
								}
						},
					);
				}
			);

			//init
			setMailbox(mailboxSelectorValue.value);
		});
	</script>
<?endif;