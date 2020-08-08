<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$messages = Loc::loadLanguageFile(__FILE__);
$sum = round($params['SUM'], 2);
?>

<div class="mb-4" id="paysystem-yandex">
	<p class="mb-4"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION')." ".SaleFormatCurrency($sum, $params['CURRENCY']);?></p>
	<div id="payment-widget-form"></div>
	<p class="mb-4"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?></p>
</div>

<script src="https://kassa.yandex.ru/checkout-ui/v2.js"></script>
<script>
	BX.message(<?=CUtil::PhpToJSObject($messages)?>);
	var checkout = new window.YandexCheckout({
		confirmation_token: '<?=CUtil::JSEscape($params['CONFIRMATION_TOKEN'])?>',
		return_url: '<?=CUtil::JSEscape($params['RETURN_URL'])?>',
		error_callback(error) {
			console.log(error);

			var paySystemBlockNode = BX("paysystem-yandex"),
				resultDiv = document.createElement('div');

			resultDiv.innerHTML = BX.message("SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_ERROR_MESSAGE");
			resultDiv.classList.add("alert");
			resultDiv.classList.add("alert-danger");
			paySystemBlockNode.innerHTML = '';
			paySystemBlockNode.appendChild(resultDiv);
		}
	});
	checkout.render('payment-widget-form');
</script>