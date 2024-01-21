<?php

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.info-helper',
]);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$newPath = \CComponentEngine::makePathFromTemplate(
	$arParams['PATH_TO_MAIL_CONFIG'],
	array('act' => 'new')
);

if (!$arResult['CAN_CONNECT_NEW_MAILBOX'])
{
	if (\CModule::includeModule('bitrix24'))
	{
		\CJsCore::init('popup');
		\CBitrix24::initLicenseInfoPopupJS();
	}
}

?>
<div class="mail-add">
	<div class="mail-add-inner">
		<div class="mail-add-header">
			<div class="mail-add-title"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_PROMPT') ?></div>
			<div class="mail-add-desc"></div>
		</div>
		<div class="mail-add-services">
			<div class="mail-add-list">
				<? foreach ($arParams['SERVICES'] as $settings): ?>
					<? if ($settings['type'] != 'imap' && $settings['ACTIVE'] !== 'N') continue; ?>
					<a class="mail-add-item"
						<? if ($arResult['CAN_CONNECT_NEW_MAILBOX']): ?>
							href="<?=htmlspecialcharsbx(\CHTTP::urlAddParams($newPath, array('id' => $settings['id']))) ?>"
						<? else: ?>
							onclick="showLicenseInfoPopup()"
						<? endif ?>>
						<?php if ($settings['icon']): ?>
							<div class="mail-add-image-block">
								<div class="mail-add-image-container">
									<img class="mail-add-img" src="<?= $settings['icon'] ?>" alt="<?= htmlspecialcharsbx($settings['name']) ?>">
								</div>
							</div>
							<div class="mail-add-text-block">
								<span class="mail-add-text mail-add-text-title"><?= htmlspecialcharsbx($settings['serviceName'] ?? ucfirst($settings['name'])) ?></span>
								<?php if ($settings['name'] === 'other'): ?>
									<span class="mail-add-text mail-add-text-subtitle">IMAP + SMTP</span>
								<?php endif; ?>
							</div>
						<? else: ?>
							<span class="mail-add-text <? if (mb_strlen($settings['name']) > 10): ?> mail-add-text-small"<? endif ?>">
								&nbsp;<?=htmlspecialcharsbx($settings['name']) ?>&nbsp;
							</span>
						<? endif ?>
					</a>
				<? endforeach ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

	BX.addCustomEvent(
		'SidePanel.Slider:onMessage',
		function (event)
		{
			var urlParams = {};
			if (window !== window.top)
			{
				urlParams.IFRAME = 'Y';
			}

			if (event.getEventId() === 'mail-mailbox-config-success')
			{
				event.data.handled = false;

				top.BX.SidePanel.Instance.postMessage(window, event.getEventId(), event.data);

				if (event.data.handled)
				{
					var slider = top.BX.SidePanel.Instance.getSliderByWindow(window);
					if (slider)
					{
						slider.setCacheable(false);
						slider.close();
					}
				}
				else
				{
					window.location.href = BX.util.add_url_param(
						'<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_MSG_LIST']) ?>'.replace('#id#', event.data.id).replace('#start_sync_with_showing_stepper#', true),
						urlParams
					);
				}
			}
		}
	);

	function showLicenseInfoPopup()
	{
		BX.UI.InfoHelper.show('limit_contact_center_mail_box_number');
	}

	<? if (!$arResult['CAN_CONNECT_NEW_MAILBOX']): ?>
		showLicenseInfoPopup();
	<? endif ?>

</script>
