<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
$recipientCurrencyB = CSalePaySystemAction::GetParamValue("CURRENCY");
if ($recipientCurrencyB == "RUB")
	$recipientCurrencyB = "RUR";
$ACTION_TYPE = CSalePaySystemAction::GetParamValue("PAYMENT_VALUE");
?>
<form action="https://Rbkmoney.ru/acceptpurchase.aspx" method="POST" name="payment_form">
<?if (strlen($ACTION_TYPE) > 0)
{
	?><input type="hidden" name="preference" value="<?= $ACTION_TYPE?>" /><?
}
?>
	<input type="hidden" name="eshopId" value="<?=CSalePaySystemAction::GetParamValue("ESHOP_ID")?>">
	<input type="hidden" name="orderId" value="<?=CSalePaySystemAction::GetParamValue("ORDER_ID")?>">
	<input type="hidden" name="serviceName" value="<?=trim(CSalePaySystemAction::GetParamValue("SERVICE_NAME").' '.CSalePaySystemAction::GetParamValue("ORDER_ID"))?>">
	<input type="hidden" name="recipientAmount" value="<?=number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '')?>">
	<input type="hidden" name="recipientCurrency" value="<?=$recipientCurrencyB?>">
	<input type="hidden" name="userName" value="<?=CSalePaySystemAction::GetParamValue("F_NAME")?> <?=CSalePaySystemAction::GetParamValue("S_NAME")?>">
	<input type="hidden" name="user_email" value="<?=CSalePaySystemAction::GetParamValue("EMAIL")?>">
	<input type="hidden" name="successUrl" value="<?=CSalePaySystemAction::GetParamValue("SUCCESS_URL")?>">
	<input type="hidden" name="failUrl" value="<?=CSalePaySystemAction::GetParamValue("FAIL_URL")?>">
	<input type="hidden" name="userField_1" value="<?=CSalePaySystemAction::GetParamValue("USER_FIELD_1")?>">
	<input type="hidden" name="userField_2" value="<?=CSalePaySystemAction::GetParamValue("USER_FIELD_2")?>">
	<input type="hidden" name="userField_3" value="<?=CSalePaySystemAction::GetParamValue("USER_FIELD_3")?>">
	<input type="submit" value="<?=CSalePaySystemAction::GetParamValue("PAY_BUTTON")?>">
</form>