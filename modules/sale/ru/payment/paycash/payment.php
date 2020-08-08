<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = intval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<form ACTION="http://127.0.0.1:8129/wallet" METHOD="POST" target="_blank">
	<input type="hidden" NAME="currency" value="643">
	<input type="hidden" NAME="PayManner" value="paycash">
	<input type="hidden" NAME="invoice" value="<?= $ORDER_ID ?>">
	<p>Вы хотите оплатить через систему <strong>Яндекс.Деньги</strong>.</p>
	<p>Cчёт № <?= htmlspecialcharsEx($ORDER_ID." от ".$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?></p>
	<p>Сумма к оплате по счету: <strong><?echo SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></strong></p>
	<input type="hidden" name="InvoiceArticlesNames" value="Order &nbsp;<?= $ORDER_ID ?>&nbsp(<?= htmlspecialcharsEx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?>)">
	<input type="hidden" name="sum" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]) ?>">
	<input type="hidden" name="ShopID" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT")) ?>">
	<input type="hidden" name="wbp_InactivityPeriod" value="2">
	<input type="hidden" name="wbp_ShopAddress" value="195.239.63.41:8128">
	<input type="hidden" name="wbp_ShopKeyID" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_KEY_ID")) ?>">
	<input type="hidden" name="wbp_ShopEncryptionKey" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_KEY")) ?>">
	<input type="hidden" name="wbp_ShopErrorInfo" value="">
	<input type="hidden" name="wbp_Version" value="1.0">
	<label for="OrderDetails">Детали заказа:</label>
	<textarea rows="5" name="OrderDetails" id="OrderDetails" cols="60" class="form-control mb-2">
		Заказ No <?= $ORDER_ID." от ".htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?>
	</textarea>
	<input type="Submit" name="Ok" value="Отправить заявку" class="btn btn-primary">
</form>

<div class="alert alert-warning mt-4"><strong>ВНИМАНИЕ!</strong> Возврат средств по платежной системе Яндекс.Деньги - невозможен, пожалуйста, будьте внимательны при оплате заказа.</div>

<h4>Процедура оплаты</h4>

<p>Перед нажатием кнопки "Оплатить", убедитесь что <i>Кошелек "Яндекс.Деньги" у вас запущен</i>. После нажатия кнопки "Оплатить" магазин выставит вашему Кошельку "Яндекс.Деньги" требование об оплате, содержащее описание заказа. Требование об оплате подписано электронной цифровой подписью магазина.<p>

<p>Ваш Кошелек предъявляет вам содержимое заказа. Если вы согласны, и у вас достаточно денег на счету, то ваш Кошелек отсылает Кошельку нашего магазина электронные деньги и подписанный вашей электронной подписью счет. После того, как вы оплатите счет в системе Яндекс.Деньги, мы введем оплату, заказ будет обработан и доставлен согласно условиям поставки. ВНИМАНИЕ, оплата заказов вводится в неавтоматическом режиме в рабочее время отдела продаж: с 10.00 до 18.00, по будням.</p>
