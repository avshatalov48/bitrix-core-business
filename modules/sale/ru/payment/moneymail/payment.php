<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);?>
<p>Вы хотите оплатить через систему <strong>MoneyMail</strong>.</p>
<p>Cчет № <?echo $ORDER_ID." от ".CSalePaySystemAction::GetParamValue("DATE_INSERT")?></p>
<p>Сумма к оплате по счету: <strong><?echo SaleFormatCurrency(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), CSalePaySystemAction::GetParamValue("CURRENCY"))?></strong></p>
<FORM method=POST action='https://www.moneymail.ru/' target=_blank>
	<INPUT type=hidden name=action value='PostInvoice'>
	<INPUT type=hidden name=issuer value="<?echo CSalePaySystemAction::GetParamValue("ShopEmail")?>">
	<INPUT type=hidden name=currency value="<?echo CSalePaySystemAction::GetParamValue("CURRENCY")?>">
	<INPUT type=hidden name=price value="<?echo number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, ".", "")?>">
	<INPUT type=hidden name=product value="<?echo "Заказ ".$ORDER_ID." от ".CSalePaySystemAction::GetParamValue("DATE_INSERT")?>">
	<INPUT type=hidden name=issuer_id value='<?echo $ORDER_ID?>'>
	<INPUT type=hidden name=purp value='0'>
	<INPUT type=hidden name=cert value='0'>
	<INPUT type=hidden name=credit value='0'>
	<INPUT type=hidden name=valid_days value='10'>
	<INPUT type=hidden name=security_code value="<?echo md5(CSalePaySystemAction::GetParamValue("PASS").CSalePaySystemAction::GetParamValue("CURRENCY").number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, ".", "").("Заказ ".$ORDER_ID." от ".CSalePaySystemAction::GetParamValue("DATE_INSERT")).$ORDER_ID."0"."0"."0"."10")?>">
	<INPUT type=hidden name=buyer value="<?echo CSalePaySystemAction::GetParamValue("PAYER_EMAIL")?>">
	<INPUT type=hidden name=error_url value="<?echo CSalePaySystemAction::GetParamValue("ERROR_URL")?>">
	<INPUT TYPE="SUBMIT" NAME="Submit" VALUE="Оплатить" class="btn btn-primary">
</FORM>