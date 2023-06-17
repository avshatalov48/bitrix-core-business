<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$messages = Loc::loadLanguageFile(__FILE__);
$sum = round($params['SUM'], 2);
?>
<style>
	<?php
		require 'style.css';
	?>
</style>

<div class="mb-4" id="paysystem-yookassa">
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_MSGVER_1') ?></div>
	<div class="widget-payment-checkout-info"><?= Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION_SUM', ['#SUM#' => SaleFormatCurrency($sum, $params['CURRENCY'])]) ?></div>
	<p class="mb-4"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_DESCRIPTION')." ".SaleFormatCurrency($sum, $params['CURRENCY']);?></p>
	<div id="payment-widget-form"></div>
	<div class="alert alert-info"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_CHECKOUT_WARNING_RETURN');?></div>
</div>

<script src="https://yookassa.ru/checkout-widget/v1/checkout-widget.js"></script>
<script>
	BX.message(<?=CUtil::PhpToJSObject($messages)?>);
	var checkout = new window.YooMoneyCheckoutWidget({
		confirmation_token: '<?=CUtil::JSEscape($params['CONFIRMATION_TOKEN'])?>',
		return_url: '<?=CUtil::JSEscape($params['RETURN_URL'])?>',
		error_callback: function(error) {
			var paySystemBlockNode = BX("paysystem-yookassa"),
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