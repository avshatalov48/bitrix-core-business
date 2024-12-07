<?php

use \Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Providers\Constants\InternalOption;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
	'ui.dialogs.messagebox',
]);

global $APPLICATION;

/** @var array $arResult */
/** @var \Bitrix\MessageService\Sender\Sms\SmsEdnaru $sender */
$sender = $arResult['sender'];

$messageSuffix = (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '_ADMIN' : '';

if ($sender->isRegistered())
{
	if (defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === 'bitrix24')
	{
		$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
		$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'pagetitle-toolbar-field-view flexible-layout crm-toolbar crm-pagetitle-view');
	}

	if (!defined('ADMIN_SECTION'))
	{
		$this->SetViewTarget('inside_pagetitle', 10000);
	}
	?>
	<div id="messageservice_toolbar" class="pagetitle-container pagetitle-align-right-container">
		<div class="webform-small-button webform-small-button-transparent webform-cogwheel">
			<span class="webform-button-icon"></span>
		</div>
		<script>
			BX.ready(
				function ()
				{
					BX.MessageService.ToolBar.create(
						"messageservice_toolbar",
						{
							"containerId": "messageservice_toolbar",
							"items": [{
								'TEXT': '<?=CUtil::JSEscape(Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CLEAR_OPTIONS"))?>',
								'ONCLICK': 'BX.MessageService.ConfigSenderSms&&BX.MessageService.ConfigSenderSms.clearOptions?BX.MessageService.ConfigSenderSms.clearOptions():null;'
							}],
							"menuButtonClassName": "webform-cogwheel"
						}
					);
				}
			);
		</script>
	</div><?
	if (!defined('ADMIN_SECTION'))
	{
		$this->EndViewTarget();
	}
}
?>
<div class="sms-settings">
	<h2 class="sms-settings-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_TITLE")?></h2>
	<h3 class="sms-settings-title-paragraph"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_TITLE_2")?></h3>
	<div class="sms-settings-cover-container">
		<div class="sms-settings-cover"></div>
	</div>
	<div class="sms-settings-futures-rings-container">
		<div class="sms-settings-futures-rings">
			<div class="sms-settings-futures-rings-item sms-settings-futures-rings-item-first"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RING_1") ?></div>
			<div class="sms-settings-futures-rings-item sms-settings-futures-rings-item-second"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RING_2") ?></div>
			<div class="sms-settings-futures-rings-item sms-settings-futures-rings-item-third"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RING_3") ?></div>
		</div>
	</div>
	<div class="sms-settings-border"></div>
	<h3 class="sms-settings-title-paragraph"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_TITLE")?></h3>
	<div class="sms-settings-description">
		<p><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_LIST_DESCRIPTION".$messageSuffix)?></p>
		<ul class="sms-settings-futures-list">
			<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_LIST_1")?></li>
			<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_LIST_2")?></li>
			<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_LIST_3")?></li>
		</ul>
		<p><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_FOOTER_1")?> <a href="<?= $sender->getRegistrationUrl() ?>" target="_blank">Edna</a></p>
		<p>
			<?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_FOOTER_2".$messageSuffix)?>
			<a href="<?= $sender->getRegistrationUrl() ?>" target="_blank"><?= Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_FOOTER_2_REGISTRATION_TEXT') ?></a>
		</p>
	</div>
	<div class="sms-settings-quick-start" id="sms-settings-steps">
		<!---->
		<?php if (!$sender->isRegistered()):?>
			<div class="sms-settings-step-section">
				<div class="sms-settings-step-number">1</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_TITLE")?></div>
				<div class="sms-settings-step-description">
					<?= Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_REGISTRATION_TEXT') ?>
					<a href="<?=htmlspecialcharsbx($sender->getExternalManageUrl()) ?>" target="_blank"><?= htmlspecialcharsbx($sender->getExternalManageUrl()) ?></a>
				</div>
				<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="action" value="registration">
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label">X-API-KEY*</label>
						<input type="text" name="api_key" class="sms-settings-input">
					</div>
					<?php if ($sender->isMigratedToNewAPI()): ?>
						<div class="sms-settings-input-container">
							<label for="" class="sms-settings-input-label">Subject ID*</label>
							<input type="text" name="subject_id" class="sms-settings-input">
						</div>
					<?php endif;?>
					<div class="sms-settings-input-container">
						<button type="submit" class="webform-small-button webform-small-button-blue" data-role="`sms-registration-submit`">
							<span class="webform-small-button-text"><?= Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_ACTION_SAVE') ?></span>
						</button>
					</div>
				</form>
			</div>
		<?else:
			$ownerInfo = $sender->getOwnerInfo();
			?>
			<!---->
			<div class="sms-settings-step-section sms-settings-step-section-active">
				<div class="sms-settings-step-number">1</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_ACCOUNT_INFORMATION') ?></div>
				<div class="sms-settings-step-contact-info">
					<div class="sms-settings-step-contact-info-block">
						<div class="sms-settings-step-contact-info-name">X-API-KEY:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo[InternalOption::API_KEY])?></div>
					</div>
					<div class="sms-settings-step-contact-info-block">
						<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_SUBJECT') ?>:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx(implode(', ', $ownerInfo[InternalOption::SENDER_ID]))?></div>
					</div>
				</div>
			</div>
			<!---->
		<?endif;?>
		<?php if ($sender->canUse()):?>
			<div class="sms-settings-step-section sms-settings-step-section-active">
				<div class="sms-settings-step-number">2</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_MANAGE_ACCOUNT') ?></div>
				<div class="sms-settings-step-description">
					<a href="<?=htmlspecialcharsbx($sender->getExternalManageUrl())?>" target="_blank">
						<?=htmlspecialcharsbx($sender->getExternalManageUrl())?>
					</a>
				</div>
			</div>
		<?endif;?>
	</div>
</div>
<script>
	BX.ready(function()
	{
		var senderId = '<?=CUtil::JSEscape($sender->getId())?>';
		var serviceUrl = '/bitrix/components/bitrix/messageservice.config.sender.sms/ajax.php';

		var registrationForm = document.forms['form_sms_registration'];
		if (registrationForm)
		{
			var registrationSubmit = document.querySelector('[data-role="sms-registration-submit"]');
			BX.bind(registrationForm, 'submit', function(e)
			{
				e.preventDefault();
				const isMigratedToNewApi = '<?=$sender->isMigratedToNewAPI() ? 'Y' : 'N'?>' === 'Y';
				let subjectId = '';
				var apiKey = registrationForm.elements['api_key'].value;


				if (!apiKey.length)
				{
					window.alert('<?= CUtil::JSEscape(Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_ERROR_EMPTY_FIELDS')) ?>');
					return false;
				}

				const requestData = {
					sessid: BX.bitrix_sessid(),
					site_id: BX.message('SITE_ID'),
					sender_id: senderId,
					action: registrationForm.elements['action'].value,
					api_key: apiKey,
				}
				if (isMigratedToNewApi)
				{
					requestData.subject_id = registrationForm.elements['subject_id'].value;
				}

				BX.addClass(registrationSubmit, 'webform-small-button-wait');
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: BX.util.add_url_param(serviceUrl, {
						action: 'registration',
						sender_id: senderId
					}),
					data: requestData,
					onsuccess: function (response)
					{
						if (!response.success)
						{
							BX.UI.Dialogs.MessageBox.show({
								message: response.errors[0],
								minWidth: 500,
								buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
								onOk: (messageBox, button, event) => {
									messageBox.close()
								}
							});
						}
						else
						{
							window.location = window.location.href;
						}
						BX.removeClass(registrationSubmit, 'webform-small-button-wait');
					}
				});
			});
		}

		BX.namespace('BX.MessageService.ConfigSenderSms');
		BX.MessageService.ConfigSenderSms.clearOptions = function()
		{
			var confirmTitle = '<?= CUtil::JSEscape(Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_CLEAR_OPTION_CONFIRM_TITLE')) ?>';
			var confirmMessage = '<?= CUtil::JSEscape(Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_CLEAR_OPTION_CONFIRM_TEXT')) ?>';
			BX.UI.Dialogs.MessageBox.confirm(
				confirmMessage,
				confirmTitle,
				function()
				{
					BX.ajax({
						method: 'POST',
						dataType: 'json',
						url: serviceUrl,
						data: {
							sessid: BX.bitrix_sessid(),
							site_id: BX.message('SITE_ID'),
							sender_id: senderId,
							action: 'clear_options'
						},
						onsuccess: function (response)
						{
							window.location = window.location.href;
						}
					});
				}
			);
		};
		<?php if ($sender->isRegistered()):?>
		var steps = BX('sms-settings-steps');
		if (steps)
		{
			BX.scrollToNode(steps);
		}
		<?php endif?>
	});
</script>
