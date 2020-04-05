<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<form id="pay" name="pay" method="POST" action="https://z-payment.ru/merchant.php">
	<input type="hidden" name="LMI_PAYEE_PURSE" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("ZP_SHOP_ID")) ?>">
	<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?= htmlspecialcharsbx( CCurrencyRates::ConvertCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"] , CSalePaySystemAction::GetParamValue("ZP_CODE_RUR"))  )  ?>">
	<input type="hidden" name="LMI_PAYMENT_DESC" value="Заказ <?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?> от <?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?>">
	<input type="hidden" name="LMI_PAYMENT_NO" value="<?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?>">
	<input type="hidden" name="CLIENT_MAIL" value="<?= $USER->GetEmail() ?>">
	<input type="submit" value="Оплатить заказ" class="btn btn-primary">
</form>