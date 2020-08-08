<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<style>
	<?php include_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/sale/handlers/applepay.css"?>
</style>

<div class="mb-4" id="salePaySystemWrapper">
	<p><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_DESCRIPTION')." ".SaleFormatCurrency($params['TOTAL_SUM'], $params['CURRENCY']);?></p>
	<div class="mb-4 mt-4" id="payButtonWrapper">
		<div class="apple-pay-button apple-pay-button-black" id="payButton" style="display: none;"></div>
	</div>
	<p><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_WARNING_RETURN');?></p>
</div>

<script>
	<?php include_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/sale/handlers/applepay.js"?>

	<?php
	$jsParams = [
		'ajaxUrl' => '/bitrix/tools/sale_ps_ajax.php',
		'salePaySystemWrapperId' => 'salePaySystemWrapper',
		'paymentButtonId' => 'payButton',
		'paymentButtonWrapperId' => 'payButtonWrapper',
		'params' => $params,
		'message' => [
			'ORDER_TITLE' => Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_ORDER'),
			'PAY_SYSTEM_NOT_AVAILABLE' => Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_NOT_AVAILABLE'),
			'PAYMENT_APPROVED' => Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_MESSAGE_APPROVED'),
			'PAID_MESSAGE' => Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_MESSAGE_PAY_SYSTEM'),
			'ERROR_MESSAGE' => Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_APPLE_PAY_AJAX_FAILURE'),
		],
	];
	?>

	BX.Sale.PaymentApplePay.init(<?=CUtil::PhpToJSObject($jsParams)?>);
</script>