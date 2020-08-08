<?
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

Loc::loadMessages(__FILE__);

?>
<div class="mb-4">
	<form id="pay" name="pay" method="POST" action="<?=$params['URL']?>">
		<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=round($params["PAYMENT_SHOULD_PAY"], 2);?>">
		<input type="hidden" name="LMI_CURRENCY" value="<?=htmlspecialcharsbx($params['PAYMENT_CURRENCY'])?>">
		<input type="hidden" name="LMI_PAYMENT_DESC" value="<?=str_replace(array('#PAYMENT_ID#', '#DATE_INSERT#'), array($params['PAYMENT_ID'], $params['PAYMENT_DATE_INSERT']), Loc::getMessage('SALE_HPS_PAYMASTER_TEMPLATE_DESC_PAYMENT'))?>">
		<input type="hidden" name="LMI_PAYMENT_NO" value="<?=htmlspecialcharsbx($params["PAYMENT_ID"]) ?>">
		<input type="hidden" name="LMI_MERCHANT_ID" value="<?= htmlspecialcharsbx($params["PAYMASTER_SHOP_ACCT"]) ?>">
		<?if ($params["PS_IS_TEST"] == 'Y'): ?>
			<input type="hidden" name="LMI_SIM_MODE" value="0">
		<?endif;?>
		<input type="hidden" name="LMI_RESULT_URL" value="<?=htmlspecialcharsbx($params["PAYMASTER_RESULT_URL"])?>">
		<input type="hidden" name="LMI_SUCCESS_URL" value="<?=htmlspecialcharsbx($params["PAYMASTER_SUCCESS_URL"])?>">
		<input type="hidden" name="LMI_FAIL_URL" value="<?=htmlspecialcharsbx($params["PAYMASTER_FAIL_URL"])?>">
		<input type="hidden" name="LMI_PAYER_EMAIL" value="<?=htmlspecialcharsbx($params["BUYER_PERSON_EMAIL"])?>">
		<input type="hidden" name="LMI_PAYER_PHONE_NUMBER" value="<?=htmlspecialcharsbx($params["BUYER_PERSON_PHONE"])?>">
		<input type="hidden" name="LMI_SUCCESS_METHOD" value="1">
		<input type="hidden" name="LMI_FAIL_METHOD" value="1">
		<input type="hidden" name="BX_HANDLER" value="PAYMASTER">
		<input type="hidden" name="BX_PAYSYSTEM_CODE" value="<?=$params['BX_PAYSYSTEM_CODE'];?>">
		<input type="submit" class="btn btn-lg btn-success pl-4 pr-4" style="border-radius: 32px;" value="<?=Loc::getMessage('SALE_HPS_PAYMASTER_TEMPLATE_BUTTON_PAID');?>">
	</form>
</div>