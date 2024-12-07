<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.dialogs.messagebox',
	'popup',
]);

use Bitrix\Main\Localization\Loc;

/** @var \Bitrix\MessageService\Sender\Sms\Twilio $sender */
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
	</div>
	<div class="sms-settings-quick-start" id="sms-settings-steps">
		<!---->
		<?php if (!$sender->isRegistered()):?>
			<div class="sms-settings-step-section">
				<div class="sms-settings-step-number">1</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_TITLE".$messageSuffix)?></div>
				<ul class="sms-settings-futures-list">
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_1", array(
							'#A1#' => '<a href="https://www.twilio.com/console" target="_blank">',
							'#A2#' => '</a>'
						))?></li>
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_2")?></li>
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_3", array(
							'#A1#' => '<a href="https://www.twilio.com/console" target="_blank">',
							'#A2#' => '</a>'
						))?></li>
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_4")?></li>
				</ul>
				<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="action" value="registration">
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_SID")?>*</label>
						<input type="text" name="account_sid" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_TOKEN")?>*</label>
						<input type="text" name="account_token" class="sms-settings-input">
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
						<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_SID")?>:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo['sid'])?></div>
					</div>
					<div class="sms-settings-step-contact-info-block">
						<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_FRIENDLY_NAME") ?>:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo['friendly_name'])?></div>
					</div>
				</div>
			</div>
			<!---->
		<?endif;?>
		<?php if ($sender->canUse()):?>
			<div class="sms-settings-step-section sms-settings-step-section-active">
				<div class="sms-settings-step-number">2</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_TITLE")?></div>
					<div class="sms-settings-step-description"><?=Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_DEMO_IS_DISABLED", array(
							'#A1#' => '<a href="'.htmlspecialcharsbx($sender->getExternalManageUrl()).'" target="_blank">',
							'#A2#' => '</a>'
						))?></div>
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

				var sid = registrationForm.elements['account_sid'].value;
				var token = registrationForm.elements['account_token'].value;

				if (!sid.length || !token.length)
				{
					window.alert('<?=GetMessageJS('MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_ERROR')?>');
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
						account_sid: sid,
						account_token: token
					},
					onsuccess: function (response)
					{
						if (!response.success)
						{
							BX.UI.Dialogs.MessageBox.alert(response.errors[0]);
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