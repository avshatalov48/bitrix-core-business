<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
]);

use Bitrix\Main\Localization\Loc;

/** @var \Bitrix\MessageService\Sender\Sms\SmsRu $sender */
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

	?><div id="messageservice_toolbar" class="pagetitle-container pagetitle-align-right-container">
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

$formatPhone = function ($phone)
{
	$phone = preg_replace('#[^0-9]+#', '', $phone);
	if (mb_strlen($phone) < 11)
		return $phone;

	return sprintf('+%s (%s) %s-%s-%s',
		mb_substr($phone, 0, -10),
		mb_substr($phone, -10, 3),
		mb_substr($phone, -7, 3),
		mb_substr($phone, -4, 2),
		mb_substr($phone, -2, 2)
	);
};
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
		<p><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_FOOTER_1")?></p>
		<p><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_FEATURES_FOOTER_2".$messageSuffix)?></p>
	</div>
	<div class="sms-settings-quick-start" id="sms-settings-steps">
		<!---->
		<?php if (!$sender->isRegistered()):?>
		<div class="sms-settings-step-section">
			<div class="sms-settings-step-number">1</div>
			<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_TITLE")?></div>
			<div class="sms-settings-step-description"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_DESCRIPTION")?></div>
			<div class="sms-settings-step-description"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_DESCRIPTION_2")?></div>
			<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
				<?=bitrix_sessid_post()?>
				<input type="hidden" name="action" value="registration">
				<div class="sms-settings-input-container">
					<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_PHONE")?>*</label>
					<input type="text" name="user_phone" class="sms-settings-input">
				</div>
				<div class="sms-settings-input-container">
					<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_LASTNAME")?>*</label>
					<input type="text" name="user_lastname" class="sms-settings-input">
				</div>
				<div class="sms-settings-input-container">
					<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_FIRSTNAME")?>*</label>
					<input type="text" name="user_firstname" class="sms-settings-input">
				</div>
				<div class="sms-settings-input-container">
					<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_EMAIL")?>*</label>
					<input type="text" name="user_email" class="sms-settings-input">
				</div>
				<div class="sms-settings-input-container">
					<button type="submit" class="webform-small-button webform-small-button-blue" data-role="sms-registration-submit">
						<span class="webform-small-button-text"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_ACTION_REGISTRATION")?></span>
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
			<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_INFO") ?></div>
			<div class="sms-settings-step-contact-info">
				<div class="sms-settings-step-contact-info-block">
					<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_PHONE")?>:</div>
					<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($formatPhone($ownerInfo['phone']))?></div>
				</div>
				<div class="sms-settings-step-contact-info-block">
					<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_NAME") ?>:</div>
					<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo['lastName'])?> <?=htmlspecialcharsbx($ownerInfo['firstName'])?></div>
				</div>
				<div class="sms-settings-step-contact-info-block">
					<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_EMAIL")?>:</div>
					<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo['email'])?></div>
				</div>
			</div>
		</div>
		<!---->
		<?endif;?>
		<!---->
		<?php if ($sender->isRegistered() && !$sender->isConfirmed()):?>
		<div class="sms-settings-step-section">
			<div class="sms-settings-step-number">2</div>
			<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CONFIRMATION_TITLE")?></div>
			<div class="sms-settings-step-description"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CONFIRMATION_DESCRIPTION")?></div>
			<form action="" method="post" class="sms-settings-step-form" name="form_sms_confirmation">
				<?=bitrix_sessid_post()?>
				<input type="hidden" name="action" value="confirmation">
				<div class="sms-settings-input-container">
					<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_CONFIRMCODE")?></label>
					<input type="text" name="confirm" class="sms-settings-input" placeholder="<?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ENTER_CONFIRMCODE")?>">
					<div class="sms-settings-input-description-container">
						<div class="sms-settings-input-description-text" data-role="sms-send-confirmation-lock" style="display: none"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CONFIRMATION_RENEW_TITLE")?></div>
						<a href="" class="sms-settings-input-description-text" data-role="sms-send-confirmation"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CONFIRMATION_RENEW_ACTION")?></a>
					</div>
				</div>
				<div class="sms-settings-input-container">
					<button type="submit" class="webform-small-button webform-small-button-blue" data-role="sms-confirmation-submit">
						<span class="webform-small-button-text"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_SUBMIT")?></span>
					</button>
				</div>
			</form>
		</div>
		<?elseif ($sender->isConfirmed()):?>
		<!---->
		<div class="sms-settings-step-section sms-settings-step-section-active">
			<div class="sms-settings-step-number">2</div>
			<div class="sms-settings-step-title"><?=Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_IS_CONFIRMED')?></div>
		</div>
		<!---->
		<?endif?>
		<!---->
		<?php if ($sender->canUse()):
			$testBalance = $sender->getDemoBalance();
		?>
		<div class="sms-settings-step-section">
			<div class="sms-settings-step-number">3</div>
			<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_TEST_TITLE")?></div>
			<div class="sms-settings-step-description"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_TEST_DESCRIPTION".$messageSuffix)?></div>
			<form action="" method="post" name="form_send_message" class="sms-settings-test-form">
				<?=bitrix_sessid_post()?>
				<input type="hidden" name="action" value="send_message">
				<div class="sms-settings-test-form-container">
					<textarea name="text" class="sms-settings-test-textarea"></textarea>
					<div class="sms-settings-test-button-container">
						<button type="submit" class="webform-small-button webform-small-button-blue" data-role="sms-send-submit">
							<span class="webform-small-button-text"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_ACTION_SEND")?></span>
						</button>
					</div>
					<div class="sms-settings-test-info-container">
						<div class="sms-settings-test-info-left"><?=Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_CHAR_COUNTER', array(
								'#VALUE#' => '<span data-role="sms-text-length">0</span>',
								'#TOTAL#' => '<span>200</span>'
							))?></div>
						<div class="sms-settings-test-info-right"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_TEST_LIMIT")?>: <span data-role="sms-test-limit"><?=$testBalance['available_today']?></span></div>
					</div>
				</div>
			</form>
		</div>
		<?endif;?>
		<!---->
		<?php if ($sender->canUse()):?>
		<div class="sms-settings-step-section <?=$sender->isDemo()? '':'sms-settings-step-section-active'?>">
			<div class="sms-settings-step-number">4</div>
			<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_TITLE")?></div>
			<?php //if ($sender->isDemo()):?>
			<div class="sms-settings-step-description"><?=Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_DESCRIPTION")?></div>
			<ul class="sms-settings-futures-list">
				<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_RULE_1", array(
						'#A1#' => '<a href="'.htmlspecialcharsbx($sender->getExternalManageUrl()).'" target="_blank">',
						'#A2#' => '</a>'
					))?></li>
				<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_RULE_2")?></li>
<!--				<li class="sms-settings-futures-list-item">--><?//= htmlspecialcharsbx(Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_RULE_3"))?><!--</li>-->
			</ul>
			<?/* else:?>
			<div class="sms-settings-step-description"><?=Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_DEMO_IS_DISABLED", array(
					'#A1#' => '<a href="'.htmlspecialcharsbx($sender->getExternalManageUrl()).'" target="_blank">',
					'#A2#' => '</a>'
				))?></div>
			<?endif; */?>
		</div>
		<?endif;?>
		<?php if ($sender->isDemo()):?>
		<form action="" method="post" class="sms-settings-step-form" name="form_disable_demo">
			<?=bitrix_sessid_post()?>
			<input type="hidden" name="action" value="disable_demo">
			<div class="sms-settings-input-container" style="text-align: center">
				<button type="submit" class="webform-small-button webform-small-button-blue">
					<span class="webform-small-button-text"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_DISABLE_DEMO")?></span>
				</button>
			</div>
		</form>
		<?endif;?>
		<!---->
	</div>
</div>
<script>
	BX.ready(function()
	{
		var senderId = '<?=CUtil::JSEscape($sender->getId())?>';
		var serviceUrl = '/bitrix/components/bitrix/messageservice.config.sender.sms/ajax.php';

		var sendMessageForm = document.forms['form_send_message'];
		var registrationForm = document.forms['form_sms_registration'];
		var disableDemoForm = document.forms['form_disable_demo'];
		var confirmationForm = document.forms['form_sms_confirmation'];
		if (registrationForm)
		{
			var registrationSubmit = document.querySelector('[data-role="sms-registration-submit"]');
			BX.bind(registrationForm, 'submit', function(e)
			{
				e.preventDefault();

				var phone = registrationForm.elements['user_phone'].value;
				var lastName = registrationForm.elements['user_lastname'].value;
				var firstName = registrationForm.elements['user_firstname'].value;
				var email = registrationForm.elements['user_email'].value;

				if (
					!phone.length
					|| !lastName.length
					|| !firstName.length
					|| !email.length
				)
				{
					alert('<?=GetMessageJS('MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_ERROR')?>');
					return false;
				}

				BX.addClass(registrationSubmit, 'webform-small-button-wait');
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: BX.util.add_url_param(serviceUrl, {
						action: 'registration',
						sender_id: senderId
					}),
					data: {
						sessid: BX.bitrix_sessid(),
						site_id: BX.message('SITE_ID'),
						sender_id: senderId,
						action: registrationForm.elements['action'].value,
						user_phone: phone,
						user_lastname: lastName,
						user_firstname: firstName,
						user_email: email
					},
					onsuccess: function (response)
					{
						if (!response.success)
						{
							alert(response.errors[0]);
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
		if (sendMessageForm)
		{
			var formSubmit = document.querySelector('[data-role="sms-send-submit"]');
			var textLengthCounter = document.querySelector('[data-role="sms-text-length"]');
			var smsTestLimit = document.querySelector('[data-role="sms-test-limit"]');
			BX.bind(sendMessageForm, 'submit', function(e)
			{
				e.preventDefault();
				var text = sendMessageForm.elements['text'].value;

				if (!text.length)
				{
					BX.focus(sendMessageForm.elements['text']);
					return false;
				}

				BX.addClass(formSubmit, 'webform-small-button-wait');
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: serviceUrl,
					data: {
						sessid: BX.bitrix_sessid(),
						site_id: BX.message('SITE_ID'),
						sender_id: senderId,
						action: sendMessageForm.elements['action'].value,
						text: text
					},
					onsuccess: function (response)
					{
						if (!response.success)
						{
							alert(response.errors[0]);
						}
						else
						{
							sendMessageForm.elements['text'].value = '';
							if (smsTestLimit)
							{
								var current = parseInt(smsTestLimit.textContent);
								if (current > 0)
								{
									smsTestLimit.textContent = --current;
								}
							}
							if (textLengthCounter)
							{
								textLengthCounter.textContent = 0;
							}
						}
						BX.removeClass(formSubmit, 'webform-small-button-wait');
					}
				});
			});


			if (textLengthCounter && sendMessageForm.elements['text'])
			{
				BX.bind(sendMessageForm.elements['text'], 'keyup', function()
				{
					textLengthCounter.textContent = this.value.length;
				});
			}
		}
		if (confirmationForm)
		{
			var confirmationSubmit = document.querySelector('[data-role="sms-confirmation-submit"]');
			BX.bind(confirmationForm, 'submit', function(e)
			{
				e.preventDefault();

				var confirm = confirmationForm.elements['confirm'].value;

				if (!confirm.length)
				{
					alert('<?=GetMessageJS('MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_ERROR')?>');
					return false;
				}

				BX.addClass(confirmationSubmit, 'webform-small-button-wait');
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: serviceUrl,
					data: {
						sessid: BX.bitrix_sessid(),
						site_id: BX.message('SITE_ID'),
						sender_id: senderId,
						action: confirmationForm.elements['action'].value,
						confirm: confirm
					},
					onsuccess: function (response)
					{
						if (!response.success)
						{
							alert(response.errors[0]);
						}
						else
						{
							window.location = window.location.href;
						}
						BX.removeClass(confirmationSubmit, 'webform-small-button-wait');
					}
				});
			});
		}
		var sendConfirmationButton = document.querySelector('[data-role="sms-send-confirmation"]');
		var sendConfirmationButtonLock = document.querySelector('[data-role="sms-send-confirmation-lock"]');
		if (sendConfirmationButton && sendConfirmationButtonLock)
		{
			BX.bind(sendConfirmationButton, 'click', function(e)
			{
				e.preventDefault();
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: serviceUrl,
					data: {
						sessid: BX.bitrix_sessid(),
						site_id: BX.message('SITE_ID'),
						sender_id: senderId,
						action: 'send_confirmation'
					},
					onsuccess: function (response)
					{
						if (!response.success)
						{
							alert(response.errors[0]);
						}
						else
						{
							BX.style(sendConfirmationButton, 'display', 'none');
							BX.style(sendConfirmationButtonLock, 'display', '');
							setTimeout(function()
							{
								BX.style(sendConfirmationButton, 'display', '');
								BX.style(sendConfirmationButtonLock, 'display', 'none');
							}, 30000);
						}
					}
				});
			});
		}

		if (disableDemoForm)
		{
			var disableDemoSubmit = disableDemoForm.querySelector('button[type="submit"]');
			BX.bind(disableDemoForm, 'submit', function(e)
			{
				e.preventDefault();
				BX.addClass(disableDemoSubmit, 'webform-small-button-wait');
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: serviceUrl,
					data: {
						sessid: BX.bitrix_sessid(),
						site_id: BX.message('SITE_ID'),
						sender_id: senderId,
						action: disableDemoForm.elements['action'].value
					},
					onsuccess: function (response)
					{
						if (!response.success)
						{
							alert(response.errors[0]);
						}
						else
						{
							window.location = window.location.href;
						}
						BX.removeClass(disableDemoSubmit, 'webform-small-button-wait');
					}
				});
			});
		}

		BX.namespace('BX.MessageService.ConfigSenderSms');
		BX.MessageService.ConfigSenderSms.clearOptions = function()
		{
			if (window.confirm('<?=CUtil::JSEscape(Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_CLEAR_CONFIRMATION'))?>'))
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
		};
		<?if ($sender->isRegistered()):?>
		var steps = BX('sms-settings-steps');
		if (steps)
		{
			BX.scrollToNode(steps);
		}
		<?endif?>
	});
</script>