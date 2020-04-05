<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$paymentDesc = Loc::getMessage('SALE_HPS_WEBMONEY_PAYMENT_DESC', array('#ID#' => $params["PAYMENT_ID"], '#DATE_INSERT#' => $params["PAYMENT_DATE_INSERT"]));
if ($params['ENCODING'])
	$paymentDesc = $APPLICATION->ConvertCharset($paymentDesc, SITE_CHARSET, $params['ENCODING']);
?>
<form id="pay" name="pay" method="POST" action="<?=$params['URL']?>">
	<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=roundEx($params["PAYMENT_SHOULD_PAY"], 2);?>">
	<?if (ToUpper($params['ENCODING']) == 'UTF-8' || ToUpper(SITE_CHARSET) == 'UTF-8') :?>
		<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?=base64_encode($paymentDesc);?>">
	<?else:?>
		<input type="hidden" name="LMI_PAYMENT_DESC" value="<?=$paymentDesc;?>">
	<?endif;?>
	<input type="hidden" name="LMI_PAYMENT_NO" value="<?=htmlspecialcharsbx($params["PAYMENT_ID"])?>">
	<input type="hidden" name="LMI_PAYEE_PURSE" value="<?=htmlspecialcharsbx($params["WEBMONEY_SHOP_ACCT"])?>">
	<input type="hidden" name="LMI_SIM_MODE" value="<?=htmlspecialcharsbx($params["WEBMONEY_TEST_MODE"])?>">
	<input type="hidden" name="LMI_RESULT_URL" value="<?=htmlspecialcharsbx($params["WEBMONEY_RESULT_URL"])?>">
	<input type="hidden" name="LMI_SUCCESS_URL" value="<?=htmlspecialcharsbx($params["WEBMONEY_SUCCESS_URL"])?>">
	<input type="hidden" name="LMI_FAIL_URL" value="<?=htmlspecialcharsbx($params["WEBMONEY_FAIL_URL"])?>">
	<input type="hidden" name="LMI_PAYER_EMAIL" value="<?=htmlspecialcharsbx($params["BUYER_PERSON_EMAIL"])?>">
	<input type="hidden" name="LMI_PAYER_PHONE_NUMBER" value="<?=htmlspecialcharsbx($params["BUYER_PERSON_PHONE"])?>">
	<input type="hidden" name="LMI_SUCCESS_METHOD" value="1">
	<input type="hidden" name="LMI_FAIL_METHOD" value="1">
	<input type="hidden" name="BX_HANDLER" value="WEBMONEY">
	<input type="hidden" name="BX_PAYSYSTEM_CODE" value="<?=$params['BX_PAYSYSTEM_CODE']?>">
	<input type="submit" value="<?=Loc::getMessage('SALE_HPS_WEBMONEY_BUTTON')?>" class="btn btn-primary">
</form>