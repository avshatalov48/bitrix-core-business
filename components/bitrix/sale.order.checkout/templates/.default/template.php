<?php

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

/**
 * @var array $arParams
 * @var array $arResult
 * @var \SaleOrderCheckout $component
 * @var string $templateFolder
 */

Extension::load('sale.checkout.application');
CJSCore::Init('phone_number');

$model = $arResult['JSON_DATA']['MODEL'];
$scheme = $arResult['JSON_DATA']['SCHEME'];
$parameters = $arResult['JSON_DATA']['PARAMETERS'];

if ($arResult['IS_NEW_ORDER'])
{
	$options = [
		'stage' => 'Application.stage.edit',
		'basket' => $scheme['basket'],
		'total' => $scheme['total'],
		'currency' => $scheme['currency'],
		'discount' => $scheme['discount'],
		'property' => $scheme['properties'],
		'paySystem' => $scheme['paySystems'],
	];

	if ($arParams['USER_CONSENT_ID'] > 0)
	{
		$options['consent'] = [
			'id' => $arParams['USER_CONSENT_ID'],
			'title' => Loc::getMessage('SOC_T_CHECKOUT_ORDER_BUTTON'),
			'isLoaded' => $arParams['USER_CONSENT_IS_LOADED'],
			'autoSave' => 'Y',
			'isChecked' => $arParams['USER_CONSENT_IS_CHECKED'],
			'submitEventName' => 'Consent.validate.submit',
			'params' => $parameters['userConsentPropertyData'],
		];

		$options['consentStatus'] =
			($arParams['USER_CONSENT_IS_CHECKED'] === 'Y')
				? 'Consent.status.accepted'
				: 'Consent.status.init'
		;
	}
}
else
{
	$isPaidStage = $scheme['order']['paid'] === 'Y' && $arResult['MODE'] !== 'detail';
	$options = [
		'stage' => $isPaidStage ? 'Application.stage.payed' : 'Application.stage.view',
		'order'=> $scheme['order'],
		'basket' => $scheme['basket'],
		'total' => $scheme['total'],
		'currency' => $scheme['currency'],
		'discount' => $scheme['discount'],
		'property' => $scheme['properties'],
		'hash' => $scheme['order']['hash'],
		'accountNumber' => $scheme['order']['accountNumber'],
		'paySystem' => $scheme['paySystems'],
		'payment' => $scheme['payments'],
		'check' => $scheme['check'],
	];
}

$options['messages']['buttonCheckoutTitle'] = Loc::getMessage('SOC_T_CHECKOUT_ORDER_BUTTON');
$options['path']['emptyCart'] = $arParams['URL_PATH_TO_EMPTY_BASKET'];
$options['path']['mainPage'] = $arParams['URL_PATH_TO_MAIN_PAGE'];
$options['product']['noImage'] = $component->getPath() . '/templates/.default/images/pickup_logo.png';
$options['common']['siteId'] = $scheme['siteId'];
$options['common']['personTypeId'] = $scheme['personTypeId'];
$options['common']['tradingPlatformId'] = $model['tradingPlatformId'];
$options['option']['signedParameters'] = $component->getSignedParameters();

$containerId = 'sale-order-checkout-form';
?>
<style>
	.form-control + .form-control {margin-top: 12px;}
	body { min-width: 0 !important;}
</style>

<div id="<?= $containerId ?>" class="checkout-container">
	<script>
		BX.ready(function() {

			BX.Currency.CurrencyCore.setCurrencyFormat(
				'<?= $arResult['CURRENCY'] ?>',
				<?= \CUtil::PhpToJSObject(\CCurrencyLang::GetFormatDescription($arResult['CURRENCY']), false, true) ?>
			);

			let form = new BX.Sale.Checkout.Application(<?= \Bitrix\Main\Web\Json::encode($options)?>);
			document.getElementById('<?= $containerId ?>').appendChild(form.layout());

			function isOverflown(element) {
				return element.scrollHeight > element.clientHeight || element.scrollWidth > element.clientWidth;
			}

			// let el = document.querySelectorAll('.checkout-item-price-block');
			//
			// for (var i = 0; i < el.length; i++)
			// {
			// 	let item = el[i];
			// 	let fontSize = parseInt(item.style.fontSize);
			// 	for (let i = fontSize; i >= 0; i--)
			// 	{
			// 		let overflow = isOverflown(item);
			// 		if (overflow)
			// 		{
			// 			fontSize--;
			// 			item.style.fontSize = fontSize + "px";
			// 		}
			// 		console.log('el:',item);
			// 	}
			// 	console.log('item:', item);
			// }
		});
	</script>
</div>
