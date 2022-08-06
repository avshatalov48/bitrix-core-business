<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
/** @var array $arResult */

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'popup',
]);

/** @var \Bitrix\MessageService\Sender\Sms\DummyHttp $sender */
$sender = $arResult['sender'];
?>
<div class="sms-settings">
	<h2 class="sms-settings-title">Dummy HTTP SMS Sender</h2>
	<div class="sms-settings-quick-start">
		<form action="" method="post" class="sms-settings-step-form" name="form_sms_registration">
			<?= bitrix_sessid_post() ?>
			<input type="hidden" name="action" value="registration">
			<div class="sms-settings-input-container">
				<label for="" class="sms-settings-input-label">Remote Host</label>
				<input type="text" name="remoteHost" class="sms-settings-input" value="<?=htmlspecialcharsbx($sender->getRemoteHost())?>">
			</div>
			<div class="sms-settings-input-container">
				<button type="submit" class="webform-small-button webform-small-button-blue" data-role="sms-registration-submit">
					<span class="webform-small-button-text">Save</span>
				</button>
			</div>
		</form>

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

				var remoteHost = registrationForm.elements['remoteHost'].value;

				if (!remoteHost.length)
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
						remoteHost: remoteHost,
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
