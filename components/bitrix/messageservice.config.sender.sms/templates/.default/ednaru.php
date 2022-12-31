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
	'ui.dialogs.messagebox',
]);

use Bitrix\Main\Localization\Loc;

/** @var \Bitrix\MessageService\Sender\Sms\Ednaru $sender */
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
?>
<div class="sms-settings">
	<h2 class="sms-settings-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_TITLE")?></h2>
	<div class="sms-settings-quick-start" id="sms-settings-steps">
		<?php if (!$sender->isRegistered()):?>
			<div class="sms-settings-step-section">
				<div class="sms-settings-step-number">1</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_TITLE_ADMIN")?></div>
				<ul class="sms-settings-futures-list">
					<li class="sms-settings-futures-list-item"><?= str_replace(
						'im.edna.ru',
						'app.edna.ru',
						Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_1", array(
							'#A1#' => '<a href="https://app.edna.ru/auth/signin" target="_blank">',
							'#A2#' => '</a>'
						)))?></li>
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_2")?></li>
					<li class="sms-settings-futures-list-item"><?= str_replace(
							'im.edna.ru',
							'app.edna.ru',
							Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_3", array(
								'#A1#' => '<a href="https://app.edna.ru/auth/signin" target="_blank">',
								'#A2#' => '</a>'
							)))?></li>
					<li class="sms-settings-futures-list-item"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_RULES_LIST_4")?></li>
				</ul>
				<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="action" value="registration">
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_API_KEY")?>*</label>
						<input type="text" name="api_key" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_SUBJECT_ID")?>*</label>
						<input type="text" name="subject_id" class="sms-settings-input">
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

				<div class="sms-settings-step-title"><?=str_replace(
						'im.edna.ru',
						'app.edna.ru',
						Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_REGISTRATION_INFO", [
							'#A1#' => '<a href="https://app.edna.ru/auth/signin" target="_blank">',
							'#A2#' => '</a>'
						]));?></div>
				<div class="sms-settings-step-contact-info">
					<div class="sms-settings-step-contact-info-block">
						<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_API_KEY")?>:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo['api_key'])?></div>
					</div>
					<div class="sms-settings-step-contact-info-block">
						<div class="sms-settings-step-contact-info-name"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_LABEL_ACCOUNT_SUBJECT_ID") ?>:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx(implode('; ', $ownerInfo['sender_id']))?></div>
					</div>
				</div>
			</div>
			<!---->
		<?endif;?>
		<?php if ($sender->canUse()):?>
			<div class="sms-settings-step-section sms-settings-step-section-active">
				<div class="sms-settings-step-number">2</div>
				<div class="sms-settings-step-title"><?= Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_CABINET_TITLE")?></div>
				<div class="sms-settings-step-description"><?= str_replace(
						'im.edna.ru',
						'app.edna.ru',
						Loc::getMessage("MESSAGESERVICE_CONFIG_SENDER_SMS_GO_TO_ACCOUNT", array(
							'#A1#' => '<a href="'.htmlspecialcharsbx($sender->getExternalManageUrl()).'" target="_blank">',
							'#A2#' => '</a>'
						)))?></div>
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

				var apiKey = registrationForm.elements['api_key'].value;
				var subjectId = registrationForm.elements['subject_id'].value;

				if (!apiKey.length || !subjectId.length)
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
						sender_id: senderId,
						action: registrationForm.elements['action'].value,
						api_key: apiKey,
						subject_id: subjectId
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