<?php

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.info-helper',
	'ui.mail.provider-showcase',
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

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."workarea-transparent no-background");

$isMainPage = $arParams['VARIABLES']['IS_MAIN_MAIL_PAGE'] ?? false;
?>

<div class="mail-provider-showcase-container <?= $isMainPage === true ? 'mail-provider-showcase-container-wide' : '' ?>">

</div>

<script>
	const slider = BX.SidePanel.Instance.getTopSlider();
	const providerContainer = document.querySelector('.mail-provider-showcase-container');
	let titleNode = null;
	<?php if ($isMainPage === true): ?>
		titleNode = BX.Tag.render`
			<div class="mail-provider-showcase-title"><?= Loc::getMessage('MAIL_CLIENT_CONFIG_PROMPT') ?></div>
		`;
	<?php endif; ?>

	if (slider)
	{
		const loader = new BX.Loader({
			size: 120,
			color: getComputedStyle(document.body).getPropertyValue('--ui-color-palette-gray-15') || '#e6e7e9',
			target: providerContainer,
		});

		loader.show();
		BX.UI.Mail.ProviderShowcase.renderTo(providerContainer, {})
			.then(() => {
				if (!BX.Type.isNull(titleNode))
				{
					BX.Dom.prepend(titleNode, providerContainer);
				}
				loader.destroy();
			})
			.catch(() => {
				slider.close();
			})
		;
	}

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

</script>
