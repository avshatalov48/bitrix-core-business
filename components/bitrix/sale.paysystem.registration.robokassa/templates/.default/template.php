<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 * @var string $templateFolder
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$isAjaxRequest = Application::getInstance()->getContext()->getRequest()->isAjaxRequest();

$linkClassName = '';
$isAdminSection = Application::getInstance()->getContext()->getRequest()->isAdminSection();
if (!$isAdminSection && Loader::includeModule('ui'))
{
	Extension::load([
		'ui.buttons',
	]);

	$linkClassName = 'ui-btn ui-btn-md ui-btn-primary ui-btn-width';
}
?>

<a href="javascript:void(0);" id="js-roboxchange-reg-button" class="<?= $linkClassName ?>"><?= Loc::getMessage('SALE_SPRR_TEMPLATE_REGISTRATION_BUTTON') ?></a>

<script>

	<?php
	if ($isAjaxRequest)
	{
		include_once Application::getDocumentRoot() . $templateFolder . '/script.js';
	}
	?>

	BX.ready(function()
	{
		(new BX.Sale.Component.RegistrationRobokassa({
			buttonId: 'js-roboxchange-reg-button',
			siteUrl: '<?= CUtil::JSEscape($arResult['SITE_URL']) ?>',
			resultUrl: '<?= CUtil::JSEscape($arResult['RESULT_URL']) ?>',
			successUrl: '<?= CUtil::JSEscape($arResult['SUCCESS_URL']) ?>',
			failUrl: '<?= CUtil::JSEscape($arResult['FAIL_URL']) ?>',
			callbackUrl: '<?= CUtil::JSEscape($arResult['CALLBACK_URL']) ?>',
		})).run();
	});
</script>
