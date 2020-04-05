<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<div class="tablebodytext">
<p>
Вы хотите оплатить по кредитной карте через процессинговый центр платежной системы <b>ИМПЭКСБанка</b>.<br><br>
Cчет № <?echo $ORDER_ID." от ".CSalePaySystemAction::GetParamValue("DATE_INSERT")?><br>
Сумма к оплате по счету: <b><?echo SaleFormatCurrency(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), CSalePaySystemAction::GetParamValue("CURRENCY"))?></b>
</p>

<!-- START IMPEXBANK SMART-CARD PAY FORM -->
<form method=post action="https://e-commerce.impexbank.ru/vsmc3ds/3dsproxy_init.jsp" target=_blank>
<input type="hidden" name="AcquirerBin" value="<?echo CSalePaySystemAction::GetParamValue("AcquirerBin")?>">
<input type="hidden" name="PurchaseAmt" value="<?echo CSalePaySystemAction::GetParamValue("SHOULD_PAY")?>">
<input type="hidden" name="PurchaseDesc" value="<?echo $ORDER_ID?>">
<input type="hidden" name="CountryCode" value="643">
<input type="hidden" name="CurrencyCode" value="810">
<input type="hidden" name="MerchantName" value="<?echo CSalePaySystemAction::GetParamValue("MerchantName")?>">
<input type="hidden" name="MerchantURL" value="<?echo CSalePaySystemAction::GetParamValue("MerchantURL")?>">
<input type="hidden" name="MerchantCity" value="<?echo CSalePaySystemAction::GetParamValue("MerchantCity")?>">
<input type="hidden" name="MerchantID" value="<?echo CSalePaySystemAction::GetParamValue("MerchantID")?>">
<input type="submit" value="Оплатить">
</form>
<!-- END IMPEXBANK SMART-CARD PAY FORM -->

<p>
<b>Обратите внимание!</b><br><br>
Все финансовые операции осуществляются в процессинговом центре платежной системы ИМПЭКСБанка. 
Все данные, необходимые для осуществления платежа, гарантированно защищены платежной системой ИМПЭКСБанка.
</p>
</div>