<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<div id="new_from_email_dialog_content" style="display: none; ">
	<div class="new-from-email-dialog-error" style="display: none; "></div>
	<div class="new-from-email-dialog-content">
		<div class="new-from-email-dialog-block new-from-email-dialog-email-block">
			<div class="new-from-email-dialog-block-content">
				<div style="padding-bottom: 8px; "><?=getMessage(
					empty($arParams['IS_SMTP_AVAILABLE'])
						? 'MAIN_MAIL_CONFIRM_EMAIL_HINT'
						: 'MAIN_MAIL_CONFIRM_EMAIL_HINT_SMTP'
				) ?></div>
				<div class="new-from-email-dialog-table" style="padding: 0; ">
					<div class="new-from-email-dialog-row-group">
						<div class="new-from-email-dialog-row">
							<div class="new-from-email-dialog-text new-from-email-dialog-cell">
								<span class="new-from-email-dialog-text-spacer"></span>
								<span><?=getMessage('MAIN_MAIL_CONFIRM_NAME') ?>:</span>
							</div>
							<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
								<div class="new-from-email-dialog-string-block">
									<input tabindex="1" type="text" class="new-from-email-dialog-square-string" data-name="name"
										<? if (!empty($arParams['USER_FULL_NAME'])): ?> value="<?=htmlspecialcharsbx($arParams['USER_FULL_NAME']) ?>"<? endif ?>>
								</div>
							</div>
						</div>
						<div class="new-from-email-dialog-row">
							<div class="new-from-email-dialog-text new-from-email-dialog-cell">
								<span class="new-from-email-dialog-text-spacer"></span>
								<span><?=getMessage('MAIN_MAIL_CONFIRM_EMAIL') ?>:</span>
							</div>
							<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
								<div class="new-from-email-dialog-string-block">
									<input tabindex="2" type="text" class="new-from-email-dialog-square-string" data-name="email">
								</div>
							</div>
						</div>
						<div class="new-from-email-dialog-row">
							<div class="new-from-email-dialog-text new-from-email-dialog-cell"></div>
							<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
								<label style="display: flex; align-items: center; ">
									<input tabindex="3" type="checkbox" data-name="public" value="Y" style="margin: 0 5px; "><?=getMessage('MAIN_MAIL_CONFIRM_PUBLIC') ?>
									<span class="new-from-email-dialog-hint-icon"
										title="<?=getMessage('MAIN_MAIL_CONFIRM_PUBLIC_HINT') ?>">?</span>
								</label>
							</div>
						</div>
					</div>
					<? if (!empty($arParams['IS_SMTP_AVAILABLE'])): ?>
						<div class="new-from-email-dialog-row-group new-from-email-dialog-smtp-block" style="display: none; ">
							<div class="new-from-email-dialog-row">
								<div class="new-from-email-dialog-cell"></div>
								<div class="new-from-email-dialog-cell"></div>
								<div class="new-from-email-dialog-cell" style="padding-left: 0; padding-right: 0;">
									<div class="new-from-email-dialog-smtp-warning" style="margin-left: -440px;">
										<?=getMessage('MAIN_MAIL_CONFIRM_SMTP_WARNING') ?>
									</div>
								</div>
							</div>
							<div class="new-from-email-dialog-row">
								<div class="new-from-email-dialog-text new-from-email-dialog-cell">
									<span class="new-from-email-dialog-text-spacer"></span>
									<span><?=getMessage('MAIN_MAIL_CONFIRM_SMTP_SERVER') ?>:</span>
								</div>
								<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
									<div class="new-from-email-dialog-string-block new-from-email-dialog-smtp-port-block">
										<input tabindex="5" type="text" class="new-from-email-dialog-square-string" data-name="smtp-port">
									</div>
									<div class="new-from-email-dialog-string-block new-from-email-dialog-smtp-server-block">
										<input tabindex="4" type="text" class="new-from-email-dialog-square-string" data-name="smtp-server">
									</div>
								</div>
							</div>
							<div class="new-from-email-dialog-row">
								<div class="new-from-email-dialog-text new-from-email-dialog-cell">
									<span class="new-from-email-dialog-text-spacer"></span>
									<span><?=getMessage('MAIN_MAIL_CONFIRM_SMTP_LOGIN') ?>:</span>
								</div>
								<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
									<div class="new-from-email-dialog-string-block">
										<input tabindex="6" type="text" class="new-from-email-dialog-square-string" data-name="smtp-login">
									</div>
								</div>
							</div>
							<div class="new-from-email-dialog-row">
								<div class="new-from-email-dialog-text new-from-email-dialog-cell">
									<span class="new-from-email-dialog-text-spacer"></span>
									<span><?=getMessage('MAIN_MAIL_CONFIRM_SMTP_PASSWORD') ?>:</span>
								</div>
								<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
									<div class="new-from-email-dialog-string-block">
										<input tabindex="7" type="password" class="new-from-email-dialog-square-string" data-name="smtp-password">
									</div>
								</div>
							</div>
						</div>
					<? endif ?>
				</div>
			</div>
		</div>
		<div class="new-from-email-dialog-block new-from-email-dialog-code-block" style="display: none; ">
			<div class="new-from-email-dialog-block-content">
				<div style="padding-bottom: 8px; "><?=getMessage('MAIN_MAIL_CONFIRM_CODE_HINT') ?></div>
				<div class="new-from-email-dialog-table" style="padding: 0; width: 100%; ">
					<div class="new-from-email-dialog-row">
						<div class="new-from-email-dialog-cell new-from-email-dialog-full-width-cell">
							<div class="new-from-email-dialog-string-block">
								<input tabindex="8" type="text" class="new-from-email-dialog-square-string"
									data-name="code" placeholder="<?=getMessage('MAIN_MAIL_CONFIRM_CODE_PLACEHOLDER') ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

	BX.message({
		MAIN_MAIL_CONFIRM_USER_FULL_NAME: '<?=\CUtil::jsEscape($arParams['USER_FULL_NAME']) ?>',
		MAIN_MAIL_CONFIRM_AJAX_ERROR: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_AJAX_ERROR')) ?>',
		MAIN_MAIL_CONFIRM_MENU: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_MENU')) ?>',
		MAIN_MAIL_CONFIRM_TITLE: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_TITLE')) ?>',
		MAIN_MAIL_CONFIRM_GET_CODE: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_GET_CODE')) ?>',
		MAIN_MAIL_CONFIRM_SAVE: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_SAVE')) ?>',
		MAIN_MAIL_CONFIRM_CANCEL: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_CANCEL')) ?>',
		MAIN_MAIL_CONFIRM_EMPTY_EMAIL: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_EMPTY_EMAIL')) ?>',
		MAIN_MAIL_CONFIRM_INVALID_EMAIL: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_INVALID_EMAIL')) ?>',
		MAIN_MAIL_CONFIRM_EMPTY_SMTP_SERVER: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_SERVER')) ?>',
		MAIN_MAIL_CONFIRM_INVALID_SMTP_SERVER: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_INVALID_SMTP_SERVER')) ?>',
		MAIN_MAIL_CONFIRM_EMPTY_SMTP_PORT: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PORT')) ?>',
		MAIN_MAIL_CONFIRM_INVALID_SMTP_PORT: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_INVALID_SMTP_PORT')) ?>',
		MAIN_MAIL_CONFIRM_EMPTY_SMTP_LOGIN: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_LOGIN')) ?>',
		MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD')) ?>',
		MAIN_MAIL_CONFIRM_EMPTY_CODE: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_CONFIRM_EMPTY_CODE')) ?>'
	});

</script>
