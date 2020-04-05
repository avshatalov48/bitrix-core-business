<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$product_price = number_format(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2, '.', '');

?>
<form action="https://payments.chronopay.com/" method="POST" name="payment_form">
<input type="hidden" name="product_id" value="<?=CSalePaySystemAction::GetParamValue("PRODUCT_ID");?>">
<input type="hidden" name="product_name" value="<?=CSalePaySystemAction::GetParamValue("PRODUCT_NAME").CSalePaySystemAction::GetParamValue("ORDER_ID")?>">
<input type="hidden" name="product_price" value="<?=$product_price?>">
<input type="hidden" name="cs1" value="<?=CSalePaySystemAction::GetParamValue("ORDER_ID");?>">
<input type="hidden" name="cs2" value="<?=CSalePaySystemAction::GetParamValue("CS2");?>">
<input type="hidden" name="cs3" value="<?=CSalePaySystemAction::GetParamValue("CS3");?>">
<input type="hidden" name="cb_url" value="<?=CSalePaySystemAction::GetParamValue("CB_URL")?>">
<input type="hidden" name="cb_type" value="P"> 
<input type="hidden" name="success_url" value="<?=CSalePaySystemAction::GetParamValue("SUCCESS_URL")?>">
<input type="hidden" name="decline_url" value="<?=CSalePaySystemAction::GetParamValue("DECLINE_URL")?>">
<input type="hidden" name="language" value="<?=CSalePaySystemAction::GetParamValue("LANGUAGE")?>">
<input type="hidden" name="f_name" value="<?=CSalePaySystemAction::GetParamValue("F_NAME")?>">
<input type="hidden" name="s_name" value="<?=CSalePaySystemAction::GetParamValue("S_NAME")?>">
<input type="hidden" name="street" value="<?=CSalePaySystemAction::GetParamValue("STREET")?>"> 
<input type="hidden" name="city" value="<?=CSalePaySystemAction::GetParamValue("CITY")?>"> 
<input type="hidden" name="state" value="<?=CSalePaySystemAction::GetParamValue("STATE")?>">
<input type="hidden" name="zip" value="<?=CSalePaySystemAction::GetParamValue("ZIP")?>">
<input type="hidden" name="country" value="<?=CSalePaySystemAction::GetParamValue("COUNTRY")?>">
<input type="hidden" name="phone" value="<?=CSalePaySystemAction::GetParamValue("PHONE")?>"> 
<input type="hidden" name="email" value="<?=CSalePaySystemAction::GetParamValue("EMAIL")?>">
<?
if(strlen(CSalePaySystemAction::GetParamValue("ORDER_UNIQ")) > 0)
{
	?>
	<input type="hidden" name="order_id" value="<?=CSalePaySystemAction::GetParamValue("ORDER_ID");?>">
	<input type="hidden" name="sign" value="<?=md5(CSalePaySystemAction::GetParamValue("PRODUCT_ID")."-".$product_price."-".CSalePaySystemAction::GetParamValue("ORDER_ID")."-".CSalePaySystemAction::GetParamValue("SHARED"))?>">
	<?
}
else
{
	?>
	<input type="hidden" name="sign" value="<?=md5(CSalePaySystemAction::GetParamValue("PRODUCT_ID")."-".$product_price."-".CSalePaySystemAction::GetParamValue("SHARED"))?>">
	<?
}

if(strlen(CSalePaySystemAction::GetParamValue("YANDEX_FORWARD")) > 0)
{
	?>
	<input type="hidden" name="payment_type_group_id" value="16">
	<?
}
elseif(strlen(CSalePaySystemAction::GetParamValue("WEBMONEY_FORWARD")) > 0)
{
	?>
	<input type="hidden" name="payment_type_group_id" value="15">
	<?
}
elseif(strlen(CSalePaySystemAction::GetParamValue("QIWI_FORWARD")) > 0)
{
	?>
	<input type="hidden" name="payment_type_group_id" value="21">
	<?
}
else
{
	?>
	<input type="hidden" name="payment_type_group_id" value="1">
	<?
}
?>
<input type="submit" value="<?=CSalePaySystemAction::GetParamValue("PAY_BUTTON")?>">
</form>