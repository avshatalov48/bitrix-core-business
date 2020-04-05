<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$strMerchantID = CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT");
$strMerchantName = CSalePaySystemAction::GetParamValue("SHOP_NAME");

$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<div class="tablebodytext">
<p>
Вы хотите оплатить по Смарт-карте &quot;Импэксбанка&quot; через процессинговый центр платежной системы <b>ИМПЭКСБанка</b>.<br><br>
Cчет № <?= htmlspecialcharsEx($ORDER_ID." от ".$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?><br>
Сумма к оплате по счету: <b><?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"])."&nbsp;"?></b>
</p>

<form method="post" action="https://www.impexbank.ru/servlets/SPCardPaymentServlet">
<input type="hidden" name="Order_ID" value="<?= $ORDER_ID ?>"><br>
<input type="hidden" name="Amount" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]) ?>"><br>
<input type="hidden" name="Formtype" value="AuthForm">
<input type="hidden" name="Merchant_ID" value="<?= htmlspecialcharsbx($strMerchantID) ?>">
<input type="hidden" name="Merchant_Name" value="<?= htmlspecialcharsbx($strMerchantName) ?>">
<input type="hidden" name="Currency" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?>">
<input type="submit" value="Оплатить">
</form>

<p>
<b>Обратите внимание!</b><br><br>
Все финансовые операции осуществляются в процессинговом центре платежной системы ИМПЭКСБанка. 
Все данные, необходимые для осуществления платежа, гарантированно защищены платежной системой ИМПЭКСБанка.
</p>

</div>