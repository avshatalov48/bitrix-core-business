<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Mail\Internals\Entity\MailboxDirectory;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.buttons',
]);

Asset::getInstance()->addJS('/bitrix/components/bitrix/mail.client.config.dirs/templates/.default/script.js');
$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/mail.client.config.dirs/templates/.default/style.css');

$mailboxId = isset($arResult['MAILBOX_ID']) ? $arResult['MAILBOX_ID'] : '';
$maxLevelDirs = isset($arResult['MAX_LEVEL_DIRS']) ? $arResult['MAX_LEVEL_DIRS'] : 0;
$dirs = isset($arResult['DIRS']) ? $arResult['DIRS'] : [];
$dirsTree = isset($arResult['DIRS_TREE']) ? $arResult['DIRS_TREE'] : null;
$outcome = isset($arResult['OUTCOME']) ? $arResult['OUTCOME'] : null;
$trash = isset($arResult['TRASH']) ? $arResult['TRASH'] : null;
$spam = isset($arResult['SPAM']) ? $arResult['SPAM'] : null;
?>

<form>
	<div class="mail-connect mail-connect-slider">
		<div class="mail-config-dirs-title-block">
			<div class="mail-config-dirs-title"><?php echo Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_SYNC') ?></div>
		</div>

		<div class="mail-config-dirs">
			<?php echo $dirsTree; ?>
		</div>

		<div class="mail-config-dirs-title-block">
			<div class="mail-config-dirs-title"><?php echo Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_FOR') ?></div>
		</div>

		<div class="mail-config-dirs-section-block">
			<div class="mail-connect-option-email mail-connect-form-check-hidden">
				<?php echo \CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_OUTCOME_SAVE')) ?>

				<label
					class="mail-config-dirs-singleselect"
					data-type="<?php echo MailboxDirectoryTable::TYPE_OUTCOME ?>"
					data-id="mail-client-config-dirs-outcome"
				>
					<?php echo ($outcome instanceof MailboxDirectory) ? $outcome->getFormattedName() : sprintf('<span>%s</span>',
						Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_NOT_SPECIFIED')
					) ?>
				</label>
			</div>
			<div class="mail-connect-option-email mail-connect-form-check-hidden">
				<?php echo \CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_TRASH_SAVE')) ?>

				<label
					class="mail-config-dirs-singleselect"
					data-type="<?php echo MailboxDirectoryTable::TYPE_TRASH ?>"
					data-id="mail-client-config-dirs-trash"
				>
					<?php echo ($trash instanceof MailboxDirectory) ? $trash->getFormattedName() : sprintf('<span>%s</span>',
						Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_NOT_SPECIFIED')
					) ?>
				</label>
			</div>
			<div class="mail-connect-option-email mail-connect-form-check-hidden">
				<?php echo \CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_SPAM_SAVE')) ?>

				<label
					class="mail-config-dirs-singleselect"
					data-type="<?php echo MailboxDirectoryTable::TYPE_SPAM ?>"
					data-id="mail-client-config-dirs-spam"
				>
					<?php echo ($spam instanceof MailboxDirectory) ? $spam->getFormattedName() : sprintf('<span>%s</span>',
						Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_NOT_SPECIFIED')
					) ?>
				</label>
			</div>
		</div>
	</div>

	<div class="mail-connect-footer mail-connect-footer-fixed">
		<div class="main-connect-form-error" id="mail_connect_form_error"></div>
		<div class="mail-connect-footer-container">
			<button
				class="ui-btn ui-btn-md ui-btn-success ui-btn-success mail-config-dirs-btn-save"
				type="submit"
				id="mail_connect_save_btn"
			>
				<?php echo Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_BTN_SAVE') ?>
			</button>

			<?php if (empty($_REQUEST['INIT'])): ?>
				<button
					class="ui-btn ui-btn-md ui-btn-link mail-config-dirs-btn-cancel"
					type="button"
					id="mail_connect_cancel_btn"
				>
					<?php echo Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_BTN_CANCEL') ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
</form>

<script>
	BX.ready(function ()
	{
		BX.message({
			"MAIL_CLIENT_CONFIG_DIRS_BTN_SELECT_ALL": "<?=\CUtil::JSEscape(Loc::getMessage("MAIL_CLIENT_CONFIG_DIRS_BTN_SELECT_ALL"))?>",
			"MAIL_CLIENT_CONFIG_DIRS_BTN_CANCEL_ALL": "<?=\CUtil::JSEscape(Loc::getMessage("MAIL_CLIENT_CONFIG_DIRS_BTN_CANCEL_ALL"))?>",
			"MAIL_CLIENT_AJAX_ERROR": "<?=\CUtil::JSEscape(Loc::getMessage("MAIL_CLIENT_AJAX_ERROR"))?>",
			"MAIL_CLIENT_BUTTON_LOADING": "<?=\CUtil::JSEscape(Loc::getMessage("MAIL_CLIENT_BUTTON_LOADING"))?>"
		});

		new BX.Mail.Client.Config.Dirs.Form({
			mailboxId: '<?php echo CUtil::JSEscape($mailboxId); ?>',
			maxLevelDirs: <?php echo (int)$maxLevelDirs; ?>,
			container: document.querySelector('.mail-config-dirs'),
			buttonSubmit: document.querySelector('.mail-config-dirs-btn-save'),
			buttonCancel: document.querySelector('.mail-config-dirs-btn-cancel'),
			itemRowSelector: '.mail-config-dirs-item',
			subMenuSelector: '.mail-config-dirs-submenu',
			itemContentSelector: '.mail-config-dirs-item-content',
			checkboxSelector: '.mail-config-dirs-item-input-check',
			levelButtonSelector: '.mail-config-dirs-level-button',
			childCounterContainerSelector: '.child-counter-container',
			syncChildCounterSelector: '.sync-child-counter',
			totalChildCounterSelector: '.total-child-counter',
			menuItemDirsTypes: document.querySelectorAll('.mail-config-dirs-singleselect'),
			dirs: <?php echo \Bitrix\Main\Web\Json::encode($dirs); ?>,
			urlRedirect: '<?php echo \CComponentEngine::makePathFromTemplate(
				$arParams['PATH_TO_MAIL_MSG_LIST'],
				['id' => $mailboxId]
			); ?>'
		});
	});
</script>