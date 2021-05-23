<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
/** @var array $arResult */

CJSCore::Init(array('popup'));

/** @var \Bitrix\MessageService\Sender\Sms\SmsLineBy $sender */
$sender = $arResult['sender'];

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
	<script type="text/javascript">
		BX.ready(
			function ()
			{
				BX.MessageService.ToolBar.create(
					"messageservice_toolbar",
					{
						"containerId": "messageservice_toolbar",
						"items": [{
							'TEXT': 'Clear options',
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
	<h2 class="sms-settings-title">SMS-line</h2>
	<div class="sms-settings-quick-start" id="sms-settings-steps">
		<!---->
		<?php if (!$sender->isRegistered()):?>
			<div class="sms-settings-step-section">
				<div class="sms-settings-step-number">1</div>
				<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="action" value="registration">
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label">Login*</label>
						<input type="text" name="login" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<label for="" class="sms-settings-input-label">API key*</label>
						<input type="text" name="api_key" class="sms-settings-input">
					</div>
					<div class="sms-settings-input-container">
						<button type="submit" class="webform-small-button webform-small-button-blue" data-role="sms-registration-submit">
							<span class="webform-small-button-text">Save</span>
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
				<div class="sms-settings-step-title">Account information</div>
				<div class="sms-settings-step-contact-info">
					<div class="sms-settings-step-contact-info-block">
						<div class="sms-settings-step-contact-info-name">Login:</div>
						<div class="sms-settings-step-contact-info-value"><?=htmlspecialcharsbx($ownerInfo['login'])?></div>
					</div>
				</div>
			</div>
			<!---->
		<?endif;?>
		<?php if ($sender->canUse()):?>
			<div class="sms-settings-step-section sms-settings-step-section-active">
				<div class="sms-settings-step-number">2</div>
				<div class="sms-settings-step-title">Manage account</div>
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

				var login = registrationForm.elements['login'].value;
				var apiKey = registrationForm.elements['api_key'].value;

				if (!login.length || !apiKey.length)
				{
					window.alert('Fill required fields');
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
						login: login,
						api_key: apiKey
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
			if (window.confirm('Confirm?'))
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