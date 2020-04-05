<?
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$context = \Bitrix\Main\Application::getInstance()->getContext();
?>

<form action="<?=$params['URL'];?>" METHOD="POST" target="_blank">

<?
	echo Loc::getMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_PROMT")."<br>";
	echo Loc::getMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_ACCOUNT_NO");
	echo $payment->getField('ACCOUNT_NUMBER').Loc::getMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_ORDER_FROM").$params['PAYMENT_DATE_INSERT']."<br>";
	echo Loc::getMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_ORDER_SUM")." <b>";
	echo SaleFormatCurrency($params['PAYMENT_SHOULD_PAY'], $params['PAYMENT_CURRENCY'])."</b><br>";
	$comment = "Invoice ".$params['PAYMENT_ID']." (".$params['PAYMENT_DATE_INSERT'].")";
?>
<br>
<input type="hidden" name="Merchant_ID" value="<?=htmlspecialcharsbx($params['ASSIST_SHOP_IDP']);?>">
<input type="hidden" name="OrderNumber" value="<?=htmlspecialcharsbx($params['PAYMENT_ID'])?>">
<input type="hidden" name="OrderAmount" value="<?=(str_replace(",", ".", $params['PAYMENT_SHOULD_PAY']));?>">
<input type="hidden" name="OrderCurrency" value="<?=(($params['PAYMENT_CURRENCY'] == "RUR") ? "RUB" : htmlspecialcharsbx($params['PAYMENT_CURRENCY']));?>">
<input type="hidden" name="Delay" value="<?=htmlspecialcharsbx($params['ASSIST_DELAY'])?>">
<input type="hidden" name="Language" value="<?=$context->getLanguage();?>">
<input type="hidden" name="URL_RETURN_OK" value="<?=htmlspecialcharsbx($params['ASSIST_SUCCESS_URL']);?>">
<input type="hidden" name="URL_RETURN_NO" value="<?=htmlspecialcharsbx($params['ASSIST_FAIL_URL']);?>">
<input type="hidden" name="OrderComment" value="<?=htmlspecialcharsbx($comment)?>">
<input type="hidden" name="Lastname" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_NAME_LAST']);?>">
<input type="hidden" name="Firstname" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_NAME_FIRST']);?>">
<input type="hidden" name="Middlename" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_NAME_SECOND']);?>">
<input type="hidden" name="Email" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_EMAIL']);?>">
<input type="hidden" name="Address" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_ADDRESS']);?>">
<input type="hidden" name="MobilePhone" value="<?=htmlspecialcharsbx($params['BUYER_PERSON_PHONE']);?>">
<input type="hidden" name="CardPayment" value="<?=((int)$params['ASSIST_PAYMENT_CardPayment'] == 1) ? 1 : 0;?>">
<input type="hidden" name="YMPayment" value="<?=((int)$params['ASSIST_PAYMENT_YMPayment'] == 1) ? 1 : 0;?>">
<input type="hidden" name="QIWIPayment" value="<?=((int)$params['ASSIST_PAYMENT_QIWIPayment'] == 1) ? 1 : 0;?>">
<input type="hidden" name="WMPayment" value="<?=((int)$params['ASSIST_PAYMENT_WebMoneyPayment'] == 1) ? 1 : 0;?>">
<input type="hidden" name="AssistIDPayment" value="<?=((int)$params['ASSIST_PAYMENT_AssistIDCCPayment'] == 1) ? 1 : 0;?>">

<input TYPE="SUBMIT" name="Submit" value="<?=Loc::getMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_ACTION");?>">
</form>

<p align="justify"><b><?echo GetMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_NOTES_TITLE")?></b></p>
<p align="justify"><?echo GetMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_NOTES")?></p>
<p align="justify"><b><?echo GetMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_NOTES_TITLE1")?></b></p>
<p align="justify"><?echo GetMessage("SALE_HANDLERS_PAY_SYSTEM_ASSIST_NOTES1")?></p>