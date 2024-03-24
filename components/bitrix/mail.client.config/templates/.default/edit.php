<?php

use Bitrix\Mail\Helper\LicenseManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
\Bitrix\Main\UI\Extension::load([
	'ui.info-helper',
	'ui.alerts',
	'ui.forms'
]);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (\CModule::includeModule('bitrix24'))
{
	\CBitrix24::initLicenseInfoPopupJS();
}

\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.hint']);
\Bitrix\Main\Loader::includeModule('socialnetwork');
\CJsCore::init(array('socnetlogdest', 'popup', 'fx'));
$APPLICATION->setAdditionalCSS('/bitrix/components/bitrix/main.post.form/templates/.default/style.css');

$mailbox = $arParams['MAILBOX'];
$settings = $arParams['SERVICE'];

if ('N' == $_REQUEST['oauth'])
{
	$hiddenOAuth = !empty($settings['oauth']);
	unset($settings['oauth']);
}

$baseUri = \CHTTP::urlDeleteParams(Main\Context::getCurrent()->getRequest()->getRequestUri(), array('oauth'));

if (!empty($mailbox))
{
	if (!is_array($mailbox['OPTIONS']))
	{
		$mailbox['OPTIONS'] = array();
	}

	$mailbox['OPTIONS']['flags'] = is_array($mailbox['OPTIONS']['flags'])
		? array_values($mailbox['OPTIONS']['flags'])
		: array();
}

// @TODO: split by types
$accessList = array();
$accessLast = array();
$accessSelected = array();
foreach ($arParams['ACCESS_LIST'] as $type => $list)
{
	foreach ($list as $id => $item)
	{
		if ('users' == $type)
		{
			$accessList[$id] = $item;
		}

		$accessLast[$id] = $id;
		$accessSelected[$id] = $type;
	}
}

$crmQueueList = array();
$crmQueueLast = array();
$crmQueueSelected = array();
if ($arParams['CRM_AVAILABLE'])
{
	foreach ($arParams['CRM_QUEUE'] as $item)
	{
		$id = sprintf('U%u', $item['ID']);

		$crmQueueList[$id] = array(
			'id'       => $id,
			'entityId' => $item['ID'],
			'name'     => \CUser::formatName(\CSite::getNameFormat(), $item, true),
			'avatar'   => '',
			'desc'     => $item['WORK_POSITION'] ?: $item['PERSONAL_PROFESSION'] ?: '&nbsp;'
		);
		$crmQueueLast[$id] = $id;
		$crmQueueSelected[$id] = 'users';
	}
}

$APPLICATION->includeComponent('bitrix:main.mail.confirm', '', array());

?>

<div class="mail-connect mail-connect-slider">
	<form id="mail_connect_form" method="POST"
		action="/bitrix/services/main/ajax.php?c=<?=rawurlencode($this->getComponent()->getName()) ?>&action=save&mode=class">

		<?=bitrix_sessid_post() ?>
		<input type="hidden" name="fields[site_id]" value="<?=SITE_ID ?>">
		<input type="hidden" name="fields[service_id]" value="<?=$settings['id'] ?>">
		<? if (!empty($mailbox)): ?>
			<input type="hidden" name="fields[mailbox_id]" value="<?=$mailbox['ID'] ?>">
			<input type="hidden" name="fields[pass_placeholder]" value="<?=htmlspecialcharsbx($arParams['PASSWORD_PLACEHOLDER']) ?>">
		<? endif ?>

		<? if (empty($settings['oauth'])): ?>
			<div class="mail-connect-section-block">
				<div class="mail-connect-img-block">
					<? if ($settings['icon']): ?>
						<img class="mail-connect-img" src="<?=$settings['icon'] ?>" alt="<?=htmlspecialcharsbx($settings['name']) ?>">
					<?php endif; ?>
						<span class="mail-connect-text <? if (mb_strlen($settings['serviceName'] ?? $settings['name']) > 10): ?> mail-connect-text-small"<? endif ?>">
							<?= htmlspecialcharsbx($settings['serviceName'] ?? ucfirst($settings['name'])) ?>
						</span>
				</div>
			</div>
		<? endif ?>

		<? if (!empty($mailbox)): ?>
			<div class="mail-connect-section-block">
				<div class="mail-connect-mailbox-block">
					<div class="mail-connect-mailbox-name"><?=htmlspecialcharsbx($mailbox['EMAIL'] ?: sprintf('#%u', $mailbox['ID'])) ?></div>
					<? if ($arResult['LAST_MAIL_CHECK_DATE'] > 0): ?>
						<div class="mail-connect-last-sync-wrapper">
							<span class="mail-connect-last-sync-title">
								<?=Loc::getMessage(
									'MAIL_CLIENT_CONFIG_LAST_MAIL_CHECK_TITLE',
									array(
										'#TIME_AGO#' => formatDate(
											array('s' => 'sago', 'i' => 'iago', 'H' => 'Hago', 'd' => 'dago', 'm' => 'mago', 'Y' => 'Yago'),
											(int) $arResult['LAST_MAIL_CHECK_DATE']
										)
									)
								) ?>
							</span>
							<? $isSuccessSyncStatus = $arResult['LAST_MAIL_CHECK_STATUS']; ?>
							<span class="mail-connect-last-sync-status mail-connect-last-sync-<?= $isSuccessSyncStatus ? 'success' : 'error'; ?>">
								<?= Loc::getMessage('MAIL_CLIENT_CONFIG_LAST_MAIL_CHECK_' . ($isSuccessSyncStatus ? 'SUCCESS' : 'ERROR')); ?>
							</span>
						</div>
					<? endif ?>
				</div>
			</div>
		<? endif ?>

		<div class="mail-connect-section-block">
			<? if (!empty($settings['oauth'])): ?>
				<input type="hidden" name="fields[oauth_uid]" value="<?=htmlspecialcharsbx($settings['oauth']->getStoredUid()) ?>">
				<input type="hidden" id="mail_connect_mb_oauth_url_field"
					value="<?=htmlspecialcharsbx($settings['oauth']->getUrl()) ?>">
				<input type="hidden" name="fields[oauth_mode]" id="mail_connect_mb_oauth_field"
					value="<?=(empty($settings['oauth_user']) ? 'N' : 'S') ?>">
				<div class="mail-connect-inner">
					<div class="mail-connect-img-block">
						<? if ($settings['icon']): ?>
							<img class="mail-connect-img" src="<?=$settings['icon'] ?>" alt="<?=htmlspecialcharsbx($settings['name']) ?>">
						<? endif; ?>
						<span class="mail-connect-text">
							<?= htmlspecialcharsbx($settings['serviceName'] ?? ucfirst($settings['name'])) ?>
						</span>
					</div>
					<button class="ui-btn ui-btn-primary" id="mail_connect_mb_oauth_btn" type="button"
						<? if (!empty($settings['oauth_user'])): ?> style="display: none; "<? endif ?>><?=Loc::getMessage('MAIL_CLIENT_CONFIG_OAUTH_CONNECT') ?></button>
					<div class="mail-connect-email-block" id="mail_connect_mb_oauth_status"
						<? if (empty($settings['oauth_user'])): ?> style="display: none; "<? endif ?>>
						<div id="mail-connect-email-inner" class="mail-connect-email-inner">
							<span class="mail-connect-email-img" id="mail_connect_mb_oauth_status_image"></span>
							<a class="mail-connect-email-text" title="<?= htmlspecialcharsbx($settings['oauth_user']['email']); ?>" id="mail_connect_mb_oauth_status_email">
								<? if (!empty($settings['oauth_user'])) echo htmlspecialcharsbx($settings['oauth_user']['email']); ?>
							</a>
						</div>
						<button class="ui-btn ui-btn-md ui-btn-link ui-btn-no-caps mail-connect-email-btn-disable"
							type="button" id="mail_connect_mb_oauth_cancel_btn"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_OAUTH_DISCONNECT') ?></button>
					</div>
				</div>
				<a href="<?=htmlspecialcharsbx(\CHTTP::urlAddParams($baseUri, array('oauth' => 'N'))) ?>"
					data-slider-ignore-autobinding="true" style="display: none; ">password mode</a>


				<div id="mail-email-oauth" class="ui-alert ui-alert-warning mail-connect-form-item">
					<label class="mail-connect-form-label" for="mail-email-oauth-field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_OAUTH_FIELD_TITLE_OFFICE365') ?></label>
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100">
						<div class="ui-ctl-after ui-ctl-icon-loader" id="oauth-wait-icon"></div>
						<input type="text" disabled="disabled" class="mail-connect-form-input ui-ctl-element ui-ctl-textbox" id="mail-email-oauth-field" placeholder="info@example.com" name="fields[email]" value="<?=htmlspecialcharsbx($mailbox['EMAIL']) ?>">
					</div>
					<div class="mail-connect-form-error"></div>
				</div>

				<div class="ui-alert ui-alert-danger" id="mail-client-config-email-oauth-field-error">
					<span class="ui-alert-message"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_OAUTH_FIELD_ERROR') ?></span>
				</div>

				<div class="ui-alert ui-alert-success" id="mail-client-config-email-oauth-field-success">
					<span class="ui-alert-message"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_OAUTH_FIELD_SUCCESS') ?></span>
				</div>
			<? else: ?>
				<div class="mail-connect-form-inner">
					<? if (empty($mailbox['EMAIL'])): ?>
						<div class="mail-connect-form-item">
							<label class="mail-connect-form-label" for="mail_connect_mb_email_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_MAILBOX_EMAIL') ?></label>
							<input class="mail-connect-form-input" type="text" placeholder="info@example.com"
								name="fields[email]" id="mail_connect_mb_email_field">
							<div class="mail-connect-form-error"></div>
						</div>
					<?else:?>
						<input type="hidden" name="fields[email]" value="<?=htmlspecialcharsbx($mailbox['EMAIL']) ?>">
					<? endif ?>
					<? if (empty($settings['server'])): ?>
						<div class="mail-connect-form-item">
							<label class="mail-connect-form-label" for="mail_connect_mb_server_imap_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_SERVER') ?></label>
							<input class="mail-connect-form-input" type="text" placeholder="imap.example.com"
								name="fields[server_imap]" id="mail_connect_mb_server_imap_field"
								<? if (!empty($mailbox)): ?> value="<?=htmlspecialcharsbx($mailbox['SERVER']) ?>" <? endif ?>>
							<div class="mail-connect-form-error"></div>
						</div>
						<div class="mail-connect-form-item">
							<label class="mail-connect-form-label" for="mail_connect_mb_port_imap_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_PORT') ?></label>
							<div class="mail-connect-form-item-inner">
								<input class="mail-connect-form-input" type="text" placeholder="993"
									name="fields[port_imap]" id="mail_connect_mb_port_imap_field"
									<? if (!empty($mailbox)): ?> value="<?=htmlspecialcharsbx($mailbox['PORT']) ?>" <? endif ?>>
								<div class="mail-connect-option-email">
									<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
										name="fields[ssl_imap]" id="mail_connect_mb_ssl_imap_field"
										<? if (!empty($mailbox) && in_array($mailbox['USE_TLS'], array('Y', 'S'))): ?> value="<?=$mailbox['USE_TLS'] ?>" <? else: ?> value="Y" <? endif ?>
										<? if (empty($mailbox) || in_array($mailbox['USE_TLS'], array('Y', 'S'))): ?> checked <? endif ?>>
									<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_ssl_imap_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_SSL') ?></label>
								</div>
							</div>
							<div class="mail-connect-form-error"></div>
						</div>
					<? endif ?>
					<div class="mail-connect-form-item">
						<label class="mail-connect-form-label" for="mail_connect_mb_login_imap_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_LOGIN') ?></label>
						<input class="mail-connect-form-input" type="text"
							name="fields[login_imap]" id="mail_connect_mb_login_imap_field"
							onchange="this['__filled'] = this.value.length > 0; "
							<? if (!empty($mailbox)): ?> value="<?=htmlspecialcharsbx($mailbox['LOGIN']) ?>" disabled <? endif ?>>
						<div class="mail-connect-form-error"></div>
					</div>
					<div class="mail-connect-form-item">
						<label class="mail-connect-form-label" for="mail_connect_mb_pass_imap_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_PASS') ?></label>
						<input class="mail-connect-form-input" type="password" name="fields[pass_imap]" id="mail_connect_mb_pass_imap_field"
							<? if (!empty($mailbox['PASSWORD'])): ?>
								data-placeholder="<?=htmlspecialcharsbx($arParams['PASSWORD_PLACEHOLDER']) ?>"
								onfocus="if (this.value == this.getAttribute('data-placeholder')) this.value = ''; "
								onblur="if ('' == this.value) this.value = this.getAttribute('data-placeholder'); "
								value="<?=htmlspecialcharsbx($arParams['PASSWORD_PLACEHOLDER']) ?>"
							<? endif ?>>
						<div class="mail-connect-form-error"></div>
					</div>
				</div>
				<? if (!empty($hiddenOAuth)): ?>
					<a href="<?=htmlspecialcharsbx(\CHTTP::urlAddParams($baseUri, array('oauth' => 'Y'))) ?>"
						data-slider-ignore-autobinding="true" style="display: none; ">oauth mode</a>
				<? endif ?>
			<? endif ?>
		</div>

		<? $maxAgeLimit = LicenseManager::getSyncOldLimit(); ?>
		<? if (empty($mailbox)): ?>
			<div class="mail-connect-section-block">
				<div class="mail-connect-form-inner">
					<input type="checkbox" class="mail-connect-form-input mail-connect-form-input-check" name="fields[mail_connect_import_messages]" value="Y" id="mail_connect_mb_import_messages" checked>
					<? [$label1, $label2] = explode('#AGE#', Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE'), 2); ?>
					<label class="mail_connect_mb_import_messages_label" for="mail_connect_mb_import_messages"><?=$label1 ?></label>
					<? $maxAgeDefault = $maxAgeLimit > 0 && $maxAgeLimit < 7 ? 1 : 7; ?>
					<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_connect_mb_max_age_field_<?=$maxAgeDefault ?>">
						<input id="mail_connect_mb_max_age_field_0" type="radio" name="fields[msg_max_age]" value="0">
						<label for="mail_connect_mb_max_age_field_0"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE_2_<?=$maxAgeDefault ?>') ?></label>
						<div class="mail-set-singleselect-wrapper">
							<? foreach ($maxAgeDefault < 7 ? array(1, 7, 30, 60, 90) : array(7, 30, 60, 90) as $value): ?>
								<? $disabled = $maxAgeLimit > 0 && $value > $maxAgeLimit; ?>
								<input type="radio" name="fields[msg_max_age]" value="<?=$value ?>"
									id="mail_connect_mb_max_age_field_<?=$value ?>"
									<? if ($maxAgeDefault == $value): ?> checked<? endif ?>
									<? if ($disabled): ?> disabled<? endif ?>>
								<label for="mail_connect_mb_max_age_field_<?=$value ?>"
									<? if ($disabled): ?>
										class="mail-set-singleselect-option-disabled"
										onclick="showLicenseInfoPopup(); "
									<? endif ?>><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE_2_' . $value) ?></label>
							<? endforeach ?>
							<? if ($maxAgeLimit <= 0): ?>
								<input type="radio" name="fields[msg_max_age]" value="-1" id="mail_connect_mb_max_age_field_i">
								<label for="mail_connect_mb_max_age_field_i"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE_2_I') ?></label>
							<? endif ?>
						</div>
					</label>
					<?=$label2 ?>
				</div>
			</div>
		<? else: ?>
			<div class="mail-connect-section-block">
				<a
					class="mail-connect-dashed-switch"
					href="<?php echo \CHTTP::urlAddParams(
						$arParams['PATH_TO_MAIL_CONFIG_DIRS'],
						['mailboxId' => $mailbox['ID']]
					) ?>"
				>
					<?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_LINK') ?>
				</a>
			</div>
		<? endif ?>

		<div class="mail-connect-section-block">
			<span class="mail-connect-dashed-switch"
				onclick="this.style.display = 'none'; BX('mail_connect_mb_ext_params').style.display = ''; "
				><?=Loc::getMessage('MAIL_CLIENT_CONFIG_EXT_SWITCH') ?></span>
			<div id="mail_connect_mb_ext_params" style="display: none; ">
				<div class="mail-connect-form-item">
					<label class="mail-connect-form-label" for="mail_connect_mb_name_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_MAILBOX_NAME') ?></label>
					<input class="mail-connect-form-input" type="text"
						name="fields[name]" id="mail_connect_mb_name_field"
						onchange="this['__filled'] = this.value.length > 0; "
						<? if (!empty($mailbox)): ?> value="<?=htmlspecialcharsbx($mailbox['NAME']) ?>" <? endif ?>>
				</div>
				<div class="mail-connect-form-item">
					<label class="mail-connect-form-label" for="mail_connect_mb_sender_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_MAILBOX_USERNAME') ?></label>
					<input class="mail-connect-form-input" type="text" name="fields[sender]" id="mail_connect_mb_sender_field"
						<? if (!empty($mailbox)): ?> value="<?=htmlspecialcharsbx($mailbox['USERNAME']) ?>" <? endif ?>>
				</div>
				<? if (empty($settings['link'])): ?>
					<div class="mail-connect-form-item">
						<label class="mail-connect-form-label" for="mail_connect_mb_link_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_MAILBOX_LINK') ?></label>
						<input class="mail-connect-form-input" type="text" name="fields[link]" id="mail_connect_mb_link_field"
							<? if (!empty($mailbox)): ?> value="<?=htmlspecialcharsbx($mailbox['LINK']) ?>" <? endif ?>>
						<div class="mail-connect-form-error"></div>
					</div>
				<? endif ?>
			</div>
		</div>

		<? if (!empty($arParams['IS_SMTP_AVAILABLE'])): ?>
			<?php if (!empty($settings['oauth_smtp_enabled'])): ?>
				<input type="hidden"
					name="fields[user_principal_name]"
					id="mail_user_principal_name"
					value="<?=htmlspecialcharsbx($mailbox['__smtp']['login'] ?? '') ?>">
			<?php endif; ?>
			<?php $isExchangeService = in_array($settings['name'], $arResult['MICROSOFT_SERVICE_NAMES'], true); ?>
			<?php if (empty($settings['oauth_smtp_enabled']) || $isExchangeService): ?>
			<?php $hasSmtpFields = empty($settings['smtp']['server']) || !$settings['smtp']['login'] || !$settings['smtp']['password']; ?>
			<div class="mail-connect-section-block">
				<div class="mail-connect-title-block">
					<div class="mail-connect-title"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP') ?></div>
				</div>
				<div class="mail-connect-form-hidden-block">
					<div class="mail-connect-option-email">
						<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
							name="fields[use_smtp]" value="1" id="mail_connect_mb_server_smtp_switch"
							<?php if ((empty($mailbox) && (empty($settings['oauth']) || !$isExchangeService)) || !empty($mailbox['__smtp'])): ?> checked <?php endif ?>
							onchange="BX('mail_connect_mb_server_smtp_form').style.display = this.checked ? '' : 'none'; ">
						<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_server_smtp_switch">
							<?=htmlspecialcharsbx(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_ACTIVE')) ?>
						</label>
					</div>
					<div class="mail-connect-form-inner" id="mail_connect_mb_server_smtp_form"
						<? if (!empty($mailbox) && empty($mailbox['__smtp'])): ?> style="display: none; " <? endif ?>>
						<? if (empty($settings['upload_outgoing'])): ?>
							<div class="mail-connect-form-item">
								<div class="mail-connect-option-email" style="position: relative; ">
									<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
										name="fields[upload_outgoing]" value="1" id="mail_connect_mb_server_smtp_upload"
										<? if (empty($mailbox) || !in_array('deny_upload', $mailbox['OPTIONS']['flags'])): ?> checked <? endif ?>>
									<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_server_smtp_upload">
										<?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_UPLOAD') ?>
									</label>
									<span style="position: absolute; bottom: 0; "
										data-hint="<?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_UPLOAD_HINT') ?>"></span>
								</div>
							</div>
						<? endif ?>
						<? if ($hasSmtpFields): ?>
							<div class="mail-connect-warning-block">
								<div class="mail-connect-warning-text"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_WARNING') ?></div>
							</div>
						<? endif ?>
						<? if (empty($settings['smtp']['server'])): ?>
							<div class="mail-connect-form-item">
								<label class="mail-connect-form-label" for="mail_connect_mb_server_smtp_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_SERVER_2') ?></label>
								<input class="mail-connect-form-input" type="text" placeholder="smtp.example.com"
									name="fields[server_smtp]" id="mail_connect_mb_server_smtp_field"
									<? if (!empty($mailbox['__smtp'])): ?> value="<?=htmlspecialcharsbx($mailbox['__smtp']['server']) ?>" <? endif ?>>
								<div class="mail-connect-form-error"></div>
							</div>
							<div class="mail-connect-form-item">
								<label class="mail-connect-form-label" for="mail_connect_mb_port_smtp_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PORT') ?></label>
								<div class="mail-connect-form-item-inner">
									<input class="mail-connect-form-input" type="text" placeholder="587"
										name="fields[port_smtp]" id="mail_connect_mb_port_smtp_field"
										<? if (!empty($mailbox['__smtp'])): ?> value="<?=htmlspecialcharsbx($mailbox['__smtp']['port']) ?>" <? endif ?>>
									<div class="mail-connect-option-email">
										<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
											name="fields[ssl_smtp]" id="mail_connect_mb_ssl_smtp_field" value="Y"
											<? if (!empty($mailbox['__smtp']) && 'smtps' == $mailbox['__smtp']['protocol']): ?> checked <? endif ?>>
										<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_ssl_smtp_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_SSL') ?></label>
									</div>
								</div>
								<div class="mail-connect-form-error"></div>
							</div>
						<? endif ?>
						<? if (!$settings['smtp']['login']): ?>
							<div class="mail-connect-form-item">
								<label class="mail-connect-form-label" for="mail_connect_mb_login_smtp_field"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_LOGIN') ?></label>
								<input class="mail-connect-form-input" type="text"
									name="fields[login_smtp]" id="mail_connect_mb_login_smtp_field"
									onchange="this['__filled'] = this.value.length > 0; "
									<? if (!empty($mailbox['__smtp'])): ?> value="<?=htmlspecialcharsbx($mailbox['__smtp']['login']) ?>" <? endif ?>>
								<div class="mail-connect-form-error"></div>
							</div>
						<? endif ?>
						<? if (!$settings['smtp']['password']): ?>
							<div class="mail-connect-form-item">
										<label class="mail-connect-form-label" for="mail_connect_mb_pass_smtp_field">
											<?php
											$passLabel = !empty($settings['oauth'])
												? Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_APP_PASS')
												: Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PASS')
											;
											?>
											<?= htmlspecialcharsbx($passLabel) ?>
										</label>
								<input class="mail-connect-form-input" type="password"
									name="fields[pass_smtp]" id="mail_connect_mb_pass_smtp_field"
									onchange="this['__filled'] = this.value.length > 0; "
									<? if (!empty($mailbox['__smtp'])): ?>
										data-placeholder="<?=htmlspecialcharsbx($arParams['PASSWORD_PLACEHOLDER']) ?>"
										onfocus="if (this.value == this.getAttribute('data-placeholder')) this.value = ''; "
										onblur="if ('' == this.value) this.value = this.getAttribute('data-placeholder'); "
										value="<?=htmlspecialcharsbx($arParams['PASSWORD_PLACEHOLDER']) ?>"
									<? endif ?>>
								<div class="mail-connect-form-error"></div>
							</div>
						<? endif ?>
					</div>
				</div>
			</div>
			<?php endif ?>
		<? endif ?>

		<? if ($arParams['CRM_AVAILABLE']): ?>
			<div class="mail-connect-section-block">
				<div class="mail-connect-title-block">
					<div class="mail-connect-title">
						<a name="configcrm" id="configcrm"></a>
						<?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM') ?>
					</div>
				</div>
				<div class="mail-connect-form-hidden-block">
					<div class="mail-connect-option-email">
						<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
							name="fields[use_crm]" value="Y" id="mail_connect_mb_crm_switch"
							onchange="BX('mail_connect_mb_crm_form').style.display = this.checked ? '' : 'none'; "
							<? if (empty($mailbox) || !empty($mailbox['__crm'])): ?> checked <? endif ?>>
						<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_switch"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_ACTIVE') ?></label>
					</div>
					<div class="mail-connect-form-inner" id="mail_connect_mb_crm_form"
						<? if (!empty($mailbox) && empty($mailbox['__crm'])): ?> style="display: none; " <? endif ?>>
						<? if (empty($mailbox)): ?>
							<div class="mail-connect-option-email mail-connect-form-check-hidden">
								<? [$label1, $label2] = explode('#AGE#', Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_AGE'), 2); ?>
								<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
									name="fields[crm_sync_old]" value="Y" id="mail_connect_mb_crm_sync_old"
									<? if (empty($mailbox)): ?> checked <? endif ?>>
								<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_sync_old">
									<?=$label1 ?>
								</label>
								<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_connect_mb_crm_max_age_field_7">
									<input id="mail_connect_mb_crm_max_age_field_0" type="radio" name="fields[crm_max_age]" value="0">
									<label for="mail_connect_mb_crm_max_age_field_0"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE_2_7') ?></label>
									<div class="mail-set-singleselect-wrapper">
										<? foreach (array(7, 30) as $value): ?>
											<input type="radio" name="fields[crm_max_age]" value="<?=$value ?>"
												id="mail_connect_mb_crm_max_age_field_<?=$value ?>"
												<? if (7 == $value): ?> checked <? endif ?>>
											<label for="mail_connect_mb_crm_max_age_field_<?=$value ?>"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE_2_' . $value) ?></label>
										<? endforeach ?>
										<input type="radio" name="fields[crm_max_age]" value="-1" id="mail_connect_mb_crm_max_age_field_i">
										<label for="mail_connect_mb_crm_max_age_field_i"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_AGE_2_I') ?></label>
									</div>
								</label>
								<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_sync_old">
									<?=$label2 ?>
								</label>
							</div>
						<? endif ?>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
								name="fields[crm_public]" value="Y" id="mail_connect_mb_crm_public"
								<? if (!empty($mailbox) && in_array('crm_public_bind', $mailbox['OPTIONS']['flags'])): ?> checked <? endif ?>>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_public">
								<?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_PUBLIC') ?>
							</label>
						</div>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<? [$label1, $label2] = explode('#ENTITY#', Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_NEW_ENTITY_IN'), 2); ?>
							<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
								name="fields[crm_allow_entity_in]" value="Y" id="mail_connect_mb_crm_allow_entity_in"
								<? if (empty($mailbox) || !array_intersect(array('crm_deny_new_lead', 'crm_deny_entity_in'), $mailbox['OPTIONS']['flags'])): ?> checked <? endif ?>>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_allow_entity_in">
								<?=$label1 ?>
							</label>
							<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_connect_mb_crm_entity_in_<?=htmlspecialcharsbx($arParams['DEFAULT_NEW_ENTITY_IN']) ?>">
								<input id="mail_connect_mb_crm_entity_in_0" type="radio" name="fields[crm_entity_in]" value="0">
								<label for="mail_connect_mb_crm_entity_in_0"><?=htmlspecialcharsbx($arParams['NEW_ENTITY_LIST'][$arParams['DEFAULT_NEW_ENTITY_IN']]) ?></label>
								<div class="mail-set-singleselect-wrapper">
									<? foreach ($arParams['NEW_ENTITY_LIST'] as $value => $title): ?>
										<input type="radio" name="fields[crm_entity_in]" value="<?=htmlspecialcharsbx($value) ?>"
											id="mail_connect_mb_crm_entity_in_<?=htmlspecialcharsbx($value) ?>"
											<? if ($value == $arParams['DEFAULT_NEW_ENTITY_IN']): ?> checked <? endif ?>>
										<label for="mail_connect_mb_crm_entity_in_<?=htmlspecialcharsbx($value) ?>"><?=htmlspecialcharsbx($title) ?></label>
									<? endforeach ?>
								</div>
							</label>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_allow_entity_in">
								<?=$label2 ?>
							</label>
						</div>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<? [$label1, $label2] = explode('#ENTITY#', Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_NEW_ENTITY_OUT'), 2); ?>
							<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
								name="fields[crm_allow_entity_out]" value="Y" id="mail_connect_mb_crm_allow_entity_out"
								<? if (empty($mailbox) || !array_intersect(array('crm_deny_new_lead', 'crm_deny_entity_out'), $mailbox['OPTIONS']['flags'])): ?> checked <? endif ?>>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_allow_entity_out">
								<?=$label1 ?>
							</label>
							<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_connect_mb_crm_entity_out_<?=htmlspecialcharsbx($arParams['DEFAULT_NEW_ENTITY_OUT']) ?>">
								<input id="mail_connect_mb_crm_entity_out_0" type="radio" name="fields[crm_entity_out]" value="0">
								<label for="mail_connect_mb_crm_entity_out_0"><?=htmlspecialcharsbx($arParams['NEW_ENTITY_LIST'][$arParams['DEFAULT_NEW_ENTITY_IN']]) ?></label>
								<div class="mail-set-singleselect-wrapper">
									<? foreach ($arParams['NEW_ENTITY_LIST'] as $value => $title): ?>
										<input type="radio" name="fields[crm_entity_out]" value="<?=htmlspecialcharsbx($value) ?>"
											id="mail_connect_mb_crm_entity_out_<?=htmlspecialcharsbx($value) ?>"
											<? if ($value == $arParams['DEFAULT_NEW_ENTITY_OUT']): ?> checked <? endif ?>>
										<label for="mail_connect_mb_crm_entity_out_<?=htmlspecialcharsbx($value) ?>"><?=htmlspecialcharsbx($title) ?></label>
									<? endforeach ?>
								</div>
							</label>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_allow_entity_out">
								<?=$label2 ?>
							</label>
						</div>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
								name="fields[crm_vcf]" value="Y" id="mail_connect_mb_crm_vcf"
								<? if (empty($mailbox) || !in_array('crm_deny_new_contact', $mailbox['OPTIONS']['flags'])): ?> checked <? endif ?>>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_vcf">
								<?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_VCF') ?>
							</label>
						</div>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<? [$label1, $label2] = explode('#SOURCE#', Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_LEAD_SOURCE'), 2); ?>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_lead_source">
								<?=$label1 ?>
							</label>
							<label class="mail-set-singleselect mail-set-singleselect-line" data-checked="mail_connect_mb_crm_lead_source_<?=htmlspecialcharsbx($arParams['DEFAULT_LEAD_SOURCE']) ?>">
								<input id="mail_connect_mb_crm_lead_source_0" type="radio" name="fields[crm_lead_source]" value="0">
								<label for="mail_connect_mb_crm_lead_source_0"><?=htmlspecialcharsbx($arParams['LEAD_SOURCE_LIST'][$arParams['DEFAULT_LEAD_SOURCE']]) ?></label>
								<div class="mail-set-singleselect-wrapper">
									<? foreach ($arParams['LEAD_SOURCE_LIST'] as $value => $title): ?>
										<input type="radio" name="fields[crm_lead_source]" value="<?=htmlspecialcharsbx($value) ?>"
											id="mail_connect_mb_crm_lead_source_<?=htmlspecialcharsbx($value) ?>"
											<? if ($value == $arParams['DEFAULT_LEAD_SOURCE']): ?> checked <? endif ?>>
										<label for="mail_connect_mb_crm_lead_source_<?=htmlspecialcharsbx($value) ?>"><?=htmlspecialcharsbx($title) ?></label>
									<? endforeach ?>
								</div>
							</label>
							<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_crm_lead_source">
								<?=$label2 ?>
							</label>
						</div>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<? [$label1, $label2] = explode('#LIST#', Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_NEW_LEAD_ALLWAYS'), 2); ?>
							<label class="mail-connect-form-label mail-connect-form-label-check">
								<?=$label1 ?>
							</label>
							<span class="mail-set-textarea-show <? if (!empty($arParams['NEW_LEAD_FOR'])): ?> mail-set-textarea-show-open<? endif ?>"
								id="mail_connect_mb_crm_new_lead_for_link"
								><?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_NEW_LEAD_ALLWAYS_LIST') ?></span>
							<label class="mail-connect-form-label mail-connect-form-label-check">
								<?=$label2 ?>
							</label>
						</div>
						<div class="mail-connect-form-textarea-block" id="mail_connect_mb_crm_new_lead_for"
							<? if (empty($arParams['NEW_LEAD_FOR'])): ?> style="display: none; " <? endif ?>>
							<textarea class="mail-connect-form-textarea" name="fields[crm_new_lead_for]"
								placeholder="<?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_NEW_LEAD_FOR_PROMPT') ?>"><?
								echo join(', ', (array) $arParams['NEW_LEAD_FOR']);
							?></textarea>
						</div>
						<div class="mail-connect-option-email mail-connect-form-check-hidden">
							<label class="mail-connect-form-label mail-connect-form-label-check">
								<?=Loc::getMessage('MAIL_CLIENT_CONFIG_CRM_QUEUE') ?>
							</label>
							<?
							$APPLICATION->IncludeComponent('bitrix:main.user.selector', '', [
								"ID" => "mail_client_config_queue",
								"API_VERSION" => 3,
								"LIST" => array_keys($crmQueueSelected),
								"INPUT_NAME" => "fields[crm_queue][]",
								"USE_SYMBOLIC_ID" => true,
								"BUTTON_SELECT_CAPTION" => Loc::getMessage("MAIL_CLIENT_CONFIG_CRM_QUEUE_ADD"),
								"SELECTOR_OPTIONS" => [
									'apiVersion' => 3,
									"departmentSelectDisable" => "Y",
									'context' => 'MAIL_CLIENT_CONFIG_QUEUE',
									'multiple' => 'Y',
									'contextCode' => 'U',
									'enableAll' => 'N',
									'userSearchArea' => 'I'
								]
							]);
							?>
						</div>
					</div>
				</div>
			</div>
		<? endif ?>

		<?php if ($arParams['IS_CALENDAR_AVAILABLE']): ?>
			<div class="mail-connect-section-block">
				<div class="mail-connect-title-block">
					<div class="mail-connect-title"><?= htmlspecialcharsbx(Loc::getMessage('MAIL_CLIENT_CONFIG_ICAL_OPTIONS')) ?></div>
				</div>
				<div class="mail-connect-form-hidden-block">
					<div class="mail-connect-option-email">
						<input class="mail-connect-form-input mail-connect-form-input-check" type="checkbox"
							   name="fields[ical_access]" value="Y" id="mail_connect_mb_server_ical_switch"
							<?php if (empty($mailbox) || $arParams['IS_ICAL_CHECK']): ?> checked <?php endif; ?>
						/>
						<label class="mail-connect-form-label mail-connect-form-label-check" for="mail_connect_mb_server_ical_switch">
							<?= htmlspecialcharsbx(Loc::getMessage('MAIL_CLIENT_CONFIG_ICAL_ACTIVE')) ?>
						</label>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="mail-connect-section-block">
			<div class="mail-connect-title-block">
				<div class="mail-connect-title"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_ACCESS') ?></div>
			</div>
			<div class="mail-connect-notice-block">
				<div class="mail-connect-notice-text">
					<?=Loc::getMessage('MAIL_CLIENT_CONFIG_ACCESS_HINT_MSGVER_1') ?>
					<!--span class="mail-connect-notice-more"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_ACCESS_MORE') ?></span-->
				</div>
			</div>

			<?
			$APPLICATION->IncludeComponent('bitrix:main.user.selector', '', [
				"ID" => "mail_client_config_access",
				"API_VERSION" => 3,
				"LOCK" => $arResult['FORBIDDEN_TO_SHARE_MAILBOX'],
				"LIST" => array_keys($accessSelected),
				"UNDELETABLE" => [ sprintf('U%u', empty($mailbox) ? $USER->getId() : $mailbox['USER_ID']) ],
				"INPUT_NAME" => "fields[access_dest][]",
				"USE_SYMBOLIC_ID" => true,
				"BUTTON_SELECT_CAPTION" => Loc::getMessage("MAIL_CLIENT_CONFIG_ACCESS_ADD"),
				"SELECTOR_OPTIONS" => [
					"departmentSelectDisable" => "N",
					'context' => 'MAIL_CLIENT_CONFIG_ACCESS',
					'multiple' => 'Y',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'userSearchArea' => 'I'
				],
				"CALLBACK_BEFORE" => [
					'openDialog' => 'BX.MailClientConfig.Edit.beforeOpenDialog',
					'context' => 'BX.MailClientConfig.Edit'
				]
			]);
			?>
		</div>

		<div class="mail-connect-footer mail-connect-footer-fixed">
			<div id="mail_connect_form_error"></div>
			<div class="mail-connect-footer-container">
				<button class="ui-btn ui-btn-md ui-btn-success ui-btn-success mail-connect-btn-connect"
					type="submit" id="mail_connect_save_btn"><?=Loc::getMessage(empty($mailbox) ? 'MAIL_CLIENT_CONFIG_BTN_CONNECT' : 'MAIL_CLIENT_CONFIG_BTN_SAVE') ?></button>
				<? if (!empty($mailbox)): ?>
					<button class="ui-btn ui-btn-md ui-btn ui-btn-danger mail-connect-btn-disconnect"
						type="button" id="mail_connect_disconnect_btn"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_BTN_DISCONNECT') ?></button>
				<? endif ?>
				<button class="ui-btn ui-btn-md ui-btn-link mail-connect-btn-cancel"
					type="reset" id="mail_connect_cancel_btn"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_BTN_CANCEL') ?></button>
			</div>
		</div>

	</form>

</div>

<?
$arJsParams = array(
	'isForbiddenToShare' => $arResult["FORBIDDEN_TO_SHARE_MAILBOX"]
);
?>
<script type="text/javascript">

	if (window === window.top)
	{
		BX.ready(function ()
		{
			var footerPanel = BX.findChildByClassName(BX('mail_connect_form'), 'mail-connect-footer', true);
			footerPanel && document.body.appendChild(footerPanel);
		});
	}
	else
	{
		top.BX.loadCSS('/bitrix/components/bitrix/mail.client.sidepanel/templates/.default/style.css');
		top.BX.loadCSS('/bitrix/components/bitrix/mail.client.config/templates/.default/style.css');
	}

	BX.UI.Hint.init(BX('mail_connect_form'));

	BX.ready(function() {
		BX.MailClientConfig.Edit.init(<?=CUtil::PhpToJSObject($arJsParams)?>);
	});

	BX.message({
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_TITLE': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_TITLE')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_SYNC': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_SYNC')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_FOR': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_FOR')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_OUTCOME': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_OUTCOME')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_TRASH': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_TRASH')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_SPAM': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_SPAM')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_EMPTY_DEFAULT': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_EMPTY_DEFAULT')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_BTN_SAVE': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_BTN_SAVE')) ?>',
		'MAIL_CLIENT_CONFIG_IMAP_DIRS_BTN_CANCEL': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_DIRS_BTN_CANCEL')) ?>',
		'MAIL_MAILBOX_LICENSE_SHARED_LIMIT_BODY': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MAILBOX_LICENSE_SHARED_LIMIT_BODY', array('#LIMIT#' => LicenseManager::getSharedMailboxesLimit()))) ?>',
		'MAIL_MAILBOX_LICENSE_SHARED_LIMIT_TITLE': '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MAILBOX_LICENSE_SHARED_LIMIT_TITLE')) ?>'
	});


	(function()
	{
		var form = BX('mail_connect_form');
		var emailOauthBlock = BX('mail-email-oauth');

		if(emailOauthBlock)
		{
			var emailOauthField = BX('mail-email-oauth-field');
			var oauthBtn = BX('mail_connect_mb_oauth_btn');
			var oauthFieldError = BX('mail-client-config-email-oauth-field-error');
			var oauthFieldSuccess = BX('mail-client-config-email-oauth-field-success');
			var oauthWaitIcon = BX('oauth-wait-icon');

			BX.hide(emailOauthBlock);
			BX.hide(oauthFieldError);
			BX.hide(oauthFieldSuccess);
			BX.hide(oauthWaitIcon);

			emailOauthField.oninput = function()
			{
				BX.hide(oauthFieldError);
			}.bind(this);

			emailOauthField.onchange = function()
			{
				BX.show(oauthWaitIcon);
				BX.ajax.runComponentAction('bitrix:mail.client.config', 'checkAvailabilityEMail', {
					mode: 'class',
					data: {
						serviceId: form.elements['fields[service_id]'].value,
						email: emailOauthField.value,
						oauthUid: form.elements['fields[oauth_uid]'].value,
					}
				}).then(
					function(response) {
						BX.hide(oauthWaitIcon);
						if (response['data'] === false)
						{
							BX.show(oauthFieldError);
							BX.hide(oauthFieldSuccess);
						}
						else
						{
							BX.show(oauthFieldSuccess);
							BX.hide(oauthFieldError);
						}
					}.bind(this)
				);
			}.bind(this);
		}

		var oauthHandler = function(uid, url, user, init)
		{
			if (uid != form.elements['fields[oauth_uid]'].value)
			{
				return;
			}

			if(user['emailIsIntended'])
			{
				BX.addClass(oauthBtn, 'ui-btn-wait');
				oauthBtn.disabled = true;

				BX.hide(BX('mail-connect-email-inner'));
				BX.ajax.runComponentAction('bitrix:mail.client.config', 'checkAvailabilityEMail', {
					mode: 'class',
					data: {
						serviceId: form.elements['fields[service_id]'].value,
						email: user['email'],
						oauthUid: form.elements['fields[oauth_uid]'].value,
					}
				}).then(
					function(response) {

						BX('mail_connect_mb_oauth_status').style.display = '';
						oauthBtn.style.display = 'none';
						oauthBtn.disabled = false;
						BX.removeClass(oauthBtn, 'ui-btn-wait');

						if (response['data'] === false)
						{
							emailOauthField.value = '';
							BX.show(emailOauthBlock);
							var emailField = form.elements['fields[email]'];
							if (emailField)
							{
								emailField.removeAttribute('disabled');
							}
						}
					}.bind(this)
				);
			}
			else
			{
				BX('mail_connect_mb_oauth_status').style.display = '';
				oauthBtn.disabled = false;
				BX.removeClass(oauthBtn, 'ui-btn-wait');
				oauthBtn.style.display = 'none';
				BX.show(BX('mail-connect-email-inner'));
			}

			if (user.image && user.image.length > 0)
			{
				BX.adjust(
					BX('mail_connect_mb_oauth_status_image'),
					{
						style: {
							backgroundImage: 'url("' + encodeURI(user.image) + '")',
							backgroundSize: 'cover'
						}
					}
				);
			}
			else
			{
				var initials = '';
				if (user.first_name && user.first_name.length > 0)
				{
					initials += user.first_name.substr(0, 1);
				}
				if (user.last_name && user.last_name.length > 0)
				{
					initials += user.last_name.substr(0, 1);
				}
				if (!(initials.length > 0) && user.full_name && user.full_name.length > 0)
				{
					initials += user.full_name.substr(0, 1);
				}
				if (!(initials.length > 0))
				{
					initials += user.email.substr(0, 1);
				}

				initials = initials.toUpperCase();

				// @TODO: initials -> color
				var color = Math.round(160 + Math.random() * (Math.pow(2, 24) - 320)).toString(16);
				color = '#' + '0'.repeat(6 - color.length) + color;

				BX.adjust(
					BX('mail_connect_mb_oauth_status_image'),
					{
						text: initials,
						style: {
							background: color
						}
					}
				);
			}

			BX.adjust(BX('mail_connect_mb_oauth_status_email'), { text: user.email });
			BX('mail_connect_mb_oauth_url_field').value = url;
			BX('mail_connect_mb_oauth_field').value = init ? 'S' : 'Y';

			var emailField = form.elements['fields[email]'];
			if (emailField)
			{
				emailField.value = user.email;
				emailField.removeAttribute('disabled');
			}
			var userPrincipalField = form.elements['fields[user_principal_name]'];
			if (userPrincipalField)
			{
				userPrincipalField.value = user.userPrincipalName || '';
			}

			var nameField = BX('mail_connect_mb_name_field');
			if (!(nameField.value.length > 0) || !nameField['__filled'])
			{
				nameField.value = user.email;
			}

			if (oauthHandler['__submit'])
			{
				oauthHandler['__submit'] = false;

				submitForm();
			}
		};

		BX.addCustomEvent('OnMailOAuthBCompleted', oauthHandler);

		var changedDirs = false;

		BX.addCustomEvent(
			'SidePanel.Slider:onMessage',
			function (event) {
				if (event.getEventId() === 'mail-mailbox-config-dirs-success') {
					changedDirs = event.data.changed;
				}
			}
		);

		BX.bind(
			BX('mail_connect_mb_oauth_btn'),
			'click',
			function (e)
			{
				BX.util.popup(BX('mail_connect_mb_oauth_url_field').value, 500, 600);

				e.preventDefault();
			}
		);

		var cancelHandler = function (e)
		{
			var emailOauthBlock = BX('mail-email-oauth');

			if(emailOauthBlock)
			{
				BX.hide(emailOauthBlock);
				BX.hide(oauthFieldError);
				BX.hide(oauthFieldSuccess);
			}

			BX('mail_connect_mb_oauth_field').value = 'N';

			if (!form.elements['fields[mailbox_id]'])
			{
				var nameField = BX('mail_connect_mb_name_field');
				if (!nameField['__filled'])
				{
					nameField.value = '';
				}
			}

			BX('mail_connect_mb_oauth_status').style.display = 'none';
			BX('mail_connect_mb_oauth_btn').style.display = '';

			e.preventDefault();
		};

		BX.bind(BX('mail_connect_mb_oauth_cancel_btn'), 'click', cancelHandler);

		for (var i = 0; i < form.elements.length; i++)
		{
			if (form.elements[i].name && form.elements[i].type.match(/^text|password$/i))
			{
				if ('fields[email]' == form.elements[i].name)
				{
					BX.bind(
						form.elements[i],
						'bxchange',
						function ()
						{
							var nameField = BX('mail_connect_mb_name_field');
							if (!(nameField.value.length > 0) || !nameField['__filled'])
							{
								nameField.value = this.value;
							}

							var loginField = BX('mail_connect_mb_login_imap_field');
							if (loginField && (!(loginField.value.length > 0) || !loginField['__filled']))
							{
								loginField.value = this.value;
							}

							var loginSmtpField = BX('mail_connect_mb_login_smtp_field');
							if (loginSmtpField && (!(loginSmtpField.value.length > 0) || !loginSmtpField['__filled']))
							{
								loginSmtpField.value = this.value;
							}
						}
					);
				}

				if ('fields[login_imap]' == form.elements[i].name)
				{
					BX.bind(
						form.elements[i],
						'bxchange',
						function ()
						{
							var loginSmtpField = BX('mail_connect_mb_login_smtp_field');
							if (loginSmtpField && (!(loginSmtpField.value.length > 0) || loginSmtpField['__filled'] !== true))
							{
								loginSmtpField.value = this.value;
								loginSmtpField['__filled'] = 1;
							}
						}
					);
				}

				if ('fields[pass_imap]' == form.elements[i].name)
				{
					BX.bind(
						form.elements[i],
						'bxchange',
						function ()
						{
							var passSmtpField = BX('mail_connect_mb_pass_smtp_field');
							if (passSmtpField && (!(passSmtpField.value.length > 0) || !passSmtpField['__filled']))
							{
								passSmtpField.value = this.value;
							}
						}
					);
				}

				BX.bind(
					form.elements[i],
					'bxchange',
					BX.defer(
						function ()
						{
							if (this.value != this['__last_value'])
							{
								var fieldContainer = BX.findParent(
									this,
									{
										class: 'mail-connect-form-item'
									},
									form
								);

								BX.removeClass(fieldContainer, 'mail-connect-form-item-confirmed');
								BX.removeClass(fieldContainer, 'mail-connect-form-item-warning');
								BX.removeClass(fieldContainer, 'mail-connect-form-item-error');
							}
						},
						form.elements[i]
					)
				);
			}
		}

		var fieldError = function (field, error, text)
		{
			field['__last_value'] = field.value;

			var fieldContainer = BX.findParent(
				field,
				{
					class: 'mail-connect-form-item'
				},
				form
			);

			if (error)
			{
				BX.removeClass(fieldContainer, 'mail-connect-form-item-confirmed');
				if (error.warning)
				{
					BX.removeClass(fieldContainer, 'mail-connect-form-item-error');
					BX.addClass(fieldContainer, 'mail-connect-form-item-warning');
				}
				else
				{
					BX.removeClass(fieldContainer, 'mail-connect-form-item-warning');
					BX.addClass(fieldContainer, 'mail-connect-form-item-error');
				}
				BX.adjust(
					BX.findChildByClassName(fieldContainer, 'mail-connect-form-error', true),
					{
						text: text
					}
				);
			}
			else
			{
				BX.removeClass(fieldContainer, 'mail-connect-form-item-warning');
				BX.removeClass(fieldContainer, 'mail-connect-form-item-error');
				//BX.addClass(fieldContainer, 'mail-connect-form-item-confirmed');
			}

			return !(error && !error.warning);
		};

		var checkForm = function ()
		{
			if (BX('mail_connect_mb_oauth_field'))
			{
				if ('N' == BX('mail_connect_mb_oauth_field').value)
				{
					oauthHandler['__submit'] = true;

					BX.util.popup(BX('mail_connect_mb_oauth_url_field').value, 500, 600);

					return false;
				}
			}

			var result = true;

			var emailField = form.elements['fields[email]'];
			if (emailField)
			{
				if (emailField.value.length > 0)
				{
					var atom = "[=a-z0-9_+~'!$&*^`|#%/?{}-]";
					var pattern = new RegExp('^\\s*'+atom+'+(\\.'+atom+'+)*@([a-z0-9-]+\\.)+[a-z0-9-]{2,20}\\s*$', 'i');

					result *= fieldError(
						emailField,
						!emailField.value.match(pattern),
						'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_BAD')) ?>'
					);
				}
				else
				{
					result *= fieldError(emailField, true, '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_EMAIL_EMPTY')) ?>');
				}
			}

			var serverField = form.elements['fields[server_imap]'];
			if (serverField)
			{
				if (serverField.value.length > 0)
				{
					result *= fieldError(
						serverField,
						!serverField.value.match(/^\s*((http|https|ssl|tls|imap):\/\/)?([a-z0-9](-*[a-z0-9])*\.?)+\s*$/i),
						'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SERVER_BAD')) ?>'
					);
				}
				else
				{
					result *= fieldError(serverField, true, '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SERVER_EMPTY')) ?>');
				}
			}

			var portField = form.elements['fields[port_imap]'];
			if (portField)
			{
				result *= fieldError(
					portField,
					!(portField.value.match(/^\s*[0-9]+\s*$/) && portField.value > 0 && portField.value <= 65535),
					'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_PORT_BAD')) ?>'
				);
			}

			var linkField = form.elements['fields[link]'];
			if (linkField)
			{
				if (linkField.value.length > 0)
				{
					result *= fieldError(
						linkField,
						!linkField.value.match(/^\s*(https?:\/\/)?([a-z0-9](-*[a-z0-9])*\.?)+(:[0-9]+)?\/?/i),
						'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_LINK_BAD')) ?>'
					);
				}
			}

			var loginField = form.elements['fields[login_imap]'];
			if (loginField && !loginField.disabled)
			{
				result *= fieldError(
					loginField,
					!(loginField.value.length > 0),
					'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_LOGIN_EMPTY')) ?>'
				);
			}

			var passwordField = form.elements['fields[pass_imap]'];
			if (passwordField && !passwordField.hasAttribute('data-placeholder'))
			{
				result *= fieldError(
					passwordField,
					!(passwordField.value.length > 0),
					'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_PASS_EMPTY')) ?>'
				);
			}

			var smtpSwitch = form.elements['fields[use_smtp]'];
			if (smtpSwitch ? smtpSwitch.checked : form.elements['fields[mailbox_id]'])
			{
				var serverSmtpField = form.elements['fields[server_smtp]'];
				var serverError = false;
				if (serverSmtpField)
				{
					if (serverSmtpField.value.length > 0)
					{
						result *= fieldError(
							serverSmtpField,
							serverError = !serverSmtpField.value.match(/^\s*((http|https|ssl|tls|smtp):\/\/)?([a-z0-9](-*[a-z0-9])*\.?)+\s*$/i),
							'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SERVER_BAD')) ?>'
						);
					}
					else
					{
						result *= fieldError(serverSmtpField, serverError = true, '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SERVER_EMPTY')) ?>');
					}
				}

				var portSmtpField = form.elements['fields[port_smtp]'];
				if (portSmtpField && !serverError)
				{
					result *= fieldError(
						portSmtpField,
						!(portSmtpField.value.match(/^\s*[0-9]+\s*$/) && portSmtpField.value > 0 && portSmtpField.value <= 65535),
						'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_PORT_BAD')) ?>'
					);
				}

				var loginSmtpField = form.elements['fields[login_smtp]'];
				if (loginSmtpField && !loginSmtpField.disabled)
				{
					result *= fieldError(
						loginSmtpField,
						!(loginSmtpField.value.length > 0),
						'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_LOGIN_EMPTY')) ?>'
					);
				}

				var passwordSmtpField = form.elements['fields[pass_smtp]'];
				if (passwordSmtpField)
				{
					if (passwordSmtpField.value.length > 0)
					{
						if (passwordSmtpField.value.match(/^\^/))
						{
							result *= fieldError(
								passwordSmtpField,
								true,
								'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PASS_BAD_CARET')) ?>'
							);
						}
						else if (passwordSmtpField.value.match(/\x00/))
						{
							result *= fieldError(
								passwordSmtpField,
								true,
								'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PASS_BAD_NULL')) ?>'
							);
						}
						else if (passwordSmtpField.value.match(/^\s|\s$/))
						{
							result *= fieldError(
								passwordSmtpField,
								{warning: true},
								'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_SMTP_PASS_SPACE')) ?>'
							);
						}
					}
					else if (!form.elements['fields[mailbox_id]'])
					{
						result *= fieldError(
							passwordSmtpField,
							true,
							'<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_PASS_EMPTY')) ?>'
						);
					}
				}
			}

			return result;
		};

		var closeForm = function (id)
		{
			id = id > 0 ? id : <?=intval($mailbox['ID']) ?>;

			var slider = top.BX.SidePanel.Instance.getSliderByWindow(window);
			if (slider)
			{
				slider.setCacheable(false);
				slider.close();
			}
			else
			{
				if (id > 0)
				{
					window.location.href = BX.util.add_url_param(
						'<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_MSG_LIST']) ?>'.replace('#id#', id).replace('#start_sync_with_showing_stepper#', true),
						{ 'strict': 'N' }
					);
				}
				else
				{
					window.location.href = '<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_HOME']) ?>';
				}
			}
		};

		var submitForm = function (e)
		{
			if (e && e.preventDefault)
			{
				e.preventDefault();
			}

			var button = BX('mail_connect_save_btn');

			if (button.disabled)
			{
				return false;
			}

			button.disabled = false;

			if (!checkForm())
			{
				return false;
			}

			BX.addClass(button, 'ui-btn-wait');
			button.disabled = true;

			var formField = function (name)
			{
				return form.elements['fields[' + name + ']'] || {};
			}

			var showError = function (text)
			{
				var alert = new BX.UI.Alert({
					text: text,
					inline: true,
					closeBtn: true,
					animate: true,
					color: BX.UI.Alert.Color.DANGER,
				});

				var errorWrapper = BX('mail_connect_form_error');
				errorWrapper.textContent ='';
				errorWrapper.append(alert.getContainer());
			}

			BX.ajax.submitAjax(
				form,
				{
					url: BX.util.add_url_param(
						form.getAttribute('action'),
						{
							is_new: '<?=(empty($mailbox) ? 'Y' : 'N') ?>',
							use_crm: formField('use_crm').checked ? 'Y' : 'N',
							use_smtp: formField('use_smtp').checked ? 'Y' : 'N',
							msg_age: formField('mail_connect_import_messages').checked ? formField('msg_max_age').value : 0,
							crm_age: formField('use_crm').checked && formField('crm_sync_old').checked ? formField('crm_max_age').value : 0,
							mail_serv: '<?=\CUtil::jsEscape($settings['name']) ?>'
						}
					),
					method: 'POST',
					data: form.__extData,
					dataType: 'json',
					onsuccess: function(json)
					{
						if ('success' != json.status)
						{
							button.disabled = false;
							BX.removeClass(button, 'ui-btn-wait');

							var errorText = '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_FORM_ERROR')) ?>';
							if (json.errors && json.errors.length > 0)
							{
								if (json.errors.length == 1 && 'MAIL_CLIENT_CONFIG_SMTP_CONFIRM' == json.errors[0].message)
								{
									BXMainMailConfirm.showForm(
										submitForm, // @TODO: skip if edit
										{
											mode: 'confirm',
											data: {
												email: form.elements['fields[email]'].value
											}
										}
									);

									return;
								}

								errorText = json.errors.map(
									function (item)
									{
										var result = item.message;

										if (item.customData)
										{
											result += ' (' +
												'<span class="mail-connect-dashed-switch" onclick="BX.hide(this); BX.show(BX.findNextSibling(this, {class: \'main-connect-form-error-ext\'}), \'inline\'); return false;"><?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_CONFIG_IMAP_ERR_EXT')) ?></span>' +
												'<span class="main-connect-form-error-ext">' + item.customData + '</span>' +
											')';
										}

										return result;
									}
								).join('<br>');
							}

							showError(errorText);
						}
						else
						{
							<? if (!empty($mailbox)): ?>

							if (json.data && json.data.id > 0)
							{
								top.BX.SidePanel.Instance.postMessage(
									window,
									'mail-mailbox-config-success',
									{
										id: json.data.id,
										changed: changedDirs
									}
								);
							}

							closeForm(json.data ? json.data.id : 0);

							<? else: ?>

							if (json.data && json.data.id > 0) {
								top.BX.SidePanel.Instance.open(
									'<?=\CUtil::jsEscape(\CHTTP::urlAddParams(
										$arParams['PATH_TO_MAIL_CONFIG_DIRS'],
										['mailboxId' => '#id#', 'INIT' => 'Y']
									)) ?>'.replace('#id#', json.data.id),
									{
										width: 640,
										cacheable: false,
										events: {
											onClose: function () {
												closeForm(json.data.id);
												top.BX.SidePanel.Instance.postMessage(
													window,
													'mail-mailbox-config-success',
													{
														id: json.data.id,
														changed: changedDirs
													}
												);
											},
										}
									}
								);
							} else {
								closeForm(0);
							}

							<? endif; ?>
						}
					},
					onfailure: function(json)
					{
						button.disabled = false;
						BX.removeClass(button, 'ui-btn-wait');
						showError('<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR')) ?>');
					}
				}
			);
		};

		BX.bind(form, 'submit', submitForm);
		BX.bind(BX('mail_connect_save_btn'), 'click', submitForm);

		var nameField = BX('mail_connect_mb_name_field');
		if (nameField && nameField.value.length > 0)
		{
			nameField['__filled'] = true;
		}

		var loginField = BX('mail_connect_mb_login_imap_field');
		if (loginField && loginField.value.length > 0)
		{
			loginField['__filled'] = true;
		}

		var loginSmtpField = BX('mail_connect_mb_login_smtp_field');
		if (loginSmtpField && loginSmtpField.value.length > 0)
		{
			loginSmtpField['__filled'] = true;
		}

		var passSmtpField = BX('mail_connect_mb_pass_smtp_field');
		if (passSmtpField && passSmtpField.value.length > 0)
		{
			passSmtpField['__filled'] = true;
		}

		BX.bind(
			BX('mail_connect_cancel_btn'),
			'click',
			function (e)
			{
				closeForm();
			}
		);

		if (top.BX.SidePanel.Instance.getTopSlider())
		{
			BX.addCustomEvent(
				top.BX.SidePanel.Instance.getTopSlider().getWindow(),
				"SidePanel.Slider:onClose",
				function (event)
				{
					top.BX.SidePanel.Instance.postMessage(
						window,
						'mail-mailbox-config-close',
						{
							changed: changedDirs
						}
					);
				}
			);
		}

		<? if (!empty($mailbox)): ?>

		var deletePopup = false;
		BX.bind(
			BX('mail_connect_disconnect_btn'),
			'click',
			function (e)
			{
				var button = BX('mail_connect_disconnect_btn');

				if (button.disabled)
				{
					return false;
				}

				BX.addClass(button, 'ui-btn-wait');
				button.disabled = true;

				if (deletePopup === false)
				{
					deletePopup = new BX.PopupWindow('delete-mailbox-confirm', null, {
						closeIcon: true,
						closeByEsc: true,
						overlay: true,
						lightShadow: true,
						titleBar: '<?=\CUtil::jsEscape(getMessage('MAIL_MAILBOX_REMOVE_CONFIRM')) ?>',
						content: '<?=\CUtil::jsEscape(getMessage('MAIL_MAILBOX_REMOVE_CONFIRM_TEXT')) ?>',
						buttons: [
							new BX.PopupWindowButton({
								className: 'popup-window-button-decline',
								text: '<?=\CUtil::jsEscape(getMessage('MAIL_CLIENT_CONFIG_BTN_DISCONNECT')) ?>',
								events: {
									click: function()
									{
										this.popupWindow.close();

										var pr = BX.ajax.runComponentAction(
											'bitrix:mail.client.config',
											'delete',
											{
												mode: 'class',
												data: {
													id: form.elements['fields[mailbox_id]'].value
												}
											}
										);

										pr.then(
											function (json)
											{
												top.BX.SidePanel.Instance.postMessage(
													window,
													'mail-mailbox-config-delete',
													{
														id: form.elements['fields[mailbox_id]'].value
													}
												);

												closeForm();
											},
											function (json)
											{
												button.disabled = false;
												BX.removeClass(button, 'ui-btn-wait');
											}
										);
									}
								}
							}),
							new BX.PopupWindowButtonLink({
								text: '<?=CUtil::jsEscape(getMessage('MAIL_CLIENT_CONFIG_BTN_CANCEL')) ?>',
								className: 'popup-window-button-link',
								events: {
									click: function()
									{
										this.popupWindow.close();

										button.disabled = false;
										BX.removeClass(button, 'ui-btn-wait');
									}
								}
							})
						]
					});
				}

				deletePopup.show();
			}
		);

		var mailboxData = <?=\Bitrix\Main\Web\Json::encode(array(
			'ID'       => $mailbox['ID'],
			'EMAIL'    => $mailbox['EMAIL'],
			'NAME'     => $mailbox['NAME'],
			'USERNAME' => $mailbox['USERNAME'],
			'SERVER'   => $mailbox['SERVER'],
			'PORT'     => $mailbox['PORT'],
			'USE_TLS'  => $mailbox['USE_TLS'],
			'LOGIN'    => $mailbox['LOGIN'],
			'LINK'     => $mailbox['LINK'],
			'OPTIONS'  => array(
				'flags' => $mailbox['OPTIONS']['flags'],
			),
		)) ?>;

		BXMailMailbox.init(mailboxData);

		<? endif ?>

		<? if (!empty($settings['oauth']) && !empty($settings['oauth_user'])): ?>

		BX.onCustomEvent(
			'OnMailOAuthBCompleted',
			[
				'<?=\CUtil::jsEscape($settings['oauth']->getStoredUid()) ?>',
				'<?=\CUtil::jsEscape($settings['oauth']->getUrl()) ?>',
				<?=\Bitrix\Main\Web\Json::encode($settings['oauth_user']) ?>,
				true
			]
		);

		<? endif ?>

	})();

	<?

	function get_plural_messages($prefix)
	{
		global $MESS;

		$result = array();

		$k = 0;
		while ($form = getMessage($prefix.'PLURAL_'.++$k))
			$result[] = $form;

		return $result;
	}

	// http://localization-guide.readthedocs.org/en/latest/l10n/pluralforms.html
	function plural_form($n, $forms)
	{
		switch (LANGUAGE_ID)
		{
			case 'ru':
			case 'ua':
				$p = $n%10 == 1 && $n%100 != 11 ? 0 : ($n%10 >= 2 && $n%10 <= 4 && ($n%100 < 10 || $n%100 >= 20) ? 1 : 2);
				break;
			case 'en':
			case 'de':
			case 'es':
				$p = $n == 1 ? 0 : 1;
				break;
		}

		return isset($forms[$p]) ? $forms[$p] : end($forms);
	}

	?>

	function showLicenseInfoPopup()
	{
		BX.UI.InfoHelper.show('limit_contact_center_mail_storage');
	}

</script>
