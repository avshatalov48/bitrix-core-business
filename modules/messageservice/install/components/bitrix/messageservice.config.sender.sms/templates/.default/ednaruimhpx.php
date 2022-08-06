<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
global $APPLICATION;
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
]);

use Bitrix\Main\Localization\Loc;

/** @var \Bitrix\MessageService\Sender\Sms\EdnaruImHpx $sender */
$sender = $arResult['sender'];

if ($sender->isRegistered())
{
	?><div id="messageservice_toolbar" class="pagetitle-container pagetitle-align-right-container">
	<div class="webform-small-button webform-small-button-transparent webform-cogwheel">
		<span class="webform-button-icon"></span>
	</div>
	<script type="text/javascript">
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
$ownerInfo = $sender->getOwnerInfo();
?>
<div class="sms-settings">
	<h2 class="sms-settings-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_TITLE")?></h2>
	<div class="sms-settings-quick-start" id="sms-settings-steps">
			<div class="sms-settings-step-section">
				<div class="sms-settings-step-number">1</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_TITLE_ADMIN")?></div>
				<ul class="sms-settings-futures-list">
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_1")?></li>
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_2").$sender->getCallbackUrl()?></li>
				</ul>
				<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="action" value="registration">
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_CONNECTOR_ENDPOINT")?>*</label>
						<input type="text" name="connector_endpoint" value="<?=htmlspecialcharsbx($ownerInfo['connector_endpoint'])?>" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_LOGIN")?>*</label>
						<input type="text" name="login" value="<?=htmlspecialcharsbx($ownerInfo['login'])?>" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_PASSWORD")?>*</label>
						<input type="password" name="password" value="<?=htmlspecialcharsbx($ownerInfo['password'])?>" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_SUBJECT")?>*</label>
						<input type="text" name="subject_id" value="<?=htmlspecialcharsbx($ownerInfo['subject_id'])?>" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<button type="submit" class="webform-small-button webform-small-button-blue" data-role="sms-registration-submit">
							<span class="webform-small-button-text"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_ACTION_SAVE")?></span>
						</button>
					</div>
				</form>
			</div>
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

				var connectorEndpoint = registrationForm.elements['connector_endpoint'].value;
				var login = registrationForm.elements['login'].value;
				var password = registrationForm.elements['password'].value;
				var subjectId = registrationForm.elements['subject_id'].value;

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
						sender_id: senderId,
						action: registrationForm.elements['action'].value,
						connector_endpoint: connectorEndpoint,
						login: login,
						password: password,
						subject_id: subjectId,
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