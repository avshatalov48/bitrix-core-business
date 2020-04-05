<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<div class="tablebodytext">
<form ACTION="http://127.0.0.1:8129/wallet" METHOD="POST" target="_blank">
<input NAME="currency" value="643" type="hidden">
<input NAME="PayManner" TYPE="HIDDEN" value="paycash">
<input NAME="invoice" TYPE="HIDDEN" value="<?= $ORDER_ID ?>">
Вы хотите оплатить через систему <b>Яндекс.Деньги</b>.<br><br>
Cчет № <?= htmlspecialcharsEx($ORDER_ID." от ".$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?><br>
Сумма к оплате по счету: <b><?echo SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></b><br>
<br>
<input name="InvoiceArticlesNames" TYPE="HIDDEN" value="Order &nbsp;<?= $ORDER_ID ?>&nbsp(<?= htmlspecialcharsEx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?>)">
<input type="HIDDEN" name="sum" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]) ?>">
<input type=hidden name="ShopID" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT")) ?>">
<input type=hidden name="wbp_InactivityPeriod" value="2">
<input type=hidden name="wbp_ShopAddress" value="195.239.63.41:8128">
<input type=hidden name="wbp_ShopKeyID" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_KEY_ID")) ?>">
<input type=hidden name="wbp_ShopEncryptionKey" value="<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("SHOP_KEY")) ?>">
<input type=hidden name="wbp_ShopErrorInfo" value="">
<input type=hidden name="wbp_Version" value="1.0">
<br>
Детали заказа:<br>
<textarea rows="5" name="OrderDetails" cols="60">
Заказ No <?= $ORDER_ID." от ".htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?>
</textarea><br>
<br>
<input type="Submit" name="Ok" value="Отправить заявку">
</form>

<p><b>ВНИМАНИЕ!</b> Возврат средств по платежной системе Яндекс.Деньги - невозможен, пожалуйста, будьте внимательны при оплате заказа.</p>

<p><b>Процедура оплаты</b></p>

<p>Перед нажатием кнопки "Оплатить", убедитесь что <i>Кошелек "Яндекс.Деньги" у вас запущен</i>. После нажатия кнопки "Оплатить" магазин выставит вашему Кошельку "Яндекс.Деньги" требование об оплате, содержащее описание заказа. Требование об оплате подписано электронной цифровой подписью магазина.<p>

<p>Ваш Кошелек предъявляет вам содержимое заказа. Если вы согласны, и у вас достаточно денег на счету, то ваш Кошелек отсылает Кошельку нашего магазина электронные деньги и подписанный вашей электронной подписью счет. После того, как вы оплатите счет в системе Яндекс.Деньги, мы введем оплату, заказ будет обработан и доставлен согласно условиям поставки. ВНИМАНИЕ, оплата заказов вводится в неавтоматическом режиме в рабочее время отдела продаж: с 10.00 до 18.00, по будням.</p>
