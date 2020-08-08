<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$strMerchantID = CSalePaySystemAction::GetParamValue("SHOP_ACCOUNT");
$strMerchantName = CSalePaySystemAction::GetParamValue("SHOP_NAME");

$ORDER_ID = intval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<p>Вы хотите оплатить по Смарт-карте &quot;Импэксбанка&quot; через процессинговый центр платежной системы <strong>ИМПЭКСБанка</strong>.</p>
<p>Cчет № <?= htmlspecialcharsEx($ORDER_ID." от ".$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?></p>
<p>Сумма к оплате по счету: <strong><?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"])."&nbsp;"?></strong></p>

<form method="post" action="https://www.impexbank.ru/servlets/SPCardPaymentServlet" class="mb-3">
	<input type="hidden" name="Order_ID" value="<?= $ORDER_ID ?>">
	<input type="hidden" name="Amount" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]) ?>"><br>
	<input type="hidden" name="Formtype" value="AuthForm">
	<input type="hidden" name="Merchant_ID" value="<?= htmlspecialcharsbx($strMerchantID) ?>">
	<input type="hidden" name="Merchant_Name" value="<?= htmlspecialcharsbx($strMerchantName) ?>">
	<input type="hidden" name="Currency" value="<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?>">
	<input type="submit" value="Оплатить" class="btn btn-primary">
</form>

<div class="alert alert-warning" role="alert">
	<p class="mb-1"><strong>Обратите внимание!</strong></p>
	<p class="mb-1">Все финансовые операции осуществляются в процессинговом центре платежной системы ИМПЭКСБанка.</p>
	<p class="mb-0">Все данные, необходимые для осуществления платежа, гарантированно защищены платежной системой ИМПЭКСБанка.</p>
</div>