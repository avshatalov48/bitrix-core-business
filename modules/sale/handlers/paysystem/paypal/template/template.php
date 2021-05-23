<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);

if ($params["PAYED"] != "Y")
{
?>
	<table border="0" width="100%" cellpadding="2" cellspacing="2">
		<tr>
			<td align="center">
				<?
					$itemName = "Invoice ".$params["PAYMENT_ID"]." (".$params["PAYMENT_DATE_INSERT"].")";
				?>
				<form action="<?=$params['URL']?>" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="buttonsource" value="Bitrix_Cart">
					<input type="hidden" name="business" value="<?= htmlspecialcharsbx($params["PAYPAL_BUSINESS"]) ?>">
					<input type="hidden" name="item_name" value="<?=htmlspecialcharsbx($itemName)?>">
					<input type="hidden" name="currency_code" value="<?=htmlspecialcharsbx($params["PAYMENT_CURRENCY"])?>">
					<input type="hidden" name="amount" value="<?=PriceMaths::roundPrecision($params["PAYMENT_SHOULD_PAY"]);?>">
					<input type="hidden" name="custom" value="<?=htmlspecialcharsbx($params["PAYMENT_ID"])?>">

					<?if ($params["PAYPAL_ON0"] != ''):?>
						<input type="hidden" name="on0" value="<?=urlencode($params["PAYPAL_ON0"])?>">
						<input type="hidden" name="os0" value="<?=urlencode($params["PAYPAL_OS0"])?>">
					<?endif;?>

					<?if ($params["PAYPAL_ON1"] != '' && $params["PAYPAL_ON0"] != ''):?>
						<input type="hidden" name="on1" value="<?=urlencode($params["PAYPAL_ON1"])?>">
						<input type="hidden" name="os1" value="<?=urlencode($params["PAYPAL_OS1"])?>">
					<?endif;?>

					<?if ($params["PAYPAL_NOTIFY_URL"] != ''):?>
						<input type="hidden" name="notify_url" value="<?=htmlspecialcharsbx($params["PAYPAL_NOTIFY_URL"])?>">
					<?endif;?>

					<?if ($params["PAYPAL_RETURN"] != ''):?>
						<input type="hidden" name="return" value="<?=htmlspecialcharsbx($params["PAYPAL_RETURN"])?>">
					<?endif;?>

					<?if ($params["PAYPAL_LC"] != ''):?>
						<input type="hidden" name="lc" value="<?=htmlspecialcharsbx($params["PAYPAL_LC"])?>">
					<?endif;?>

					<? $buttonSrc = ($params["PAYPAL_BUTTON_SRC"] <> '') ? $params["PAYPAL_BUTTON_SRC"] : "http://www.paypal.com/en_US/i/btn/x-click-but6.gif";?>

					<input type="image" class="mw-100" src="<?=$buttonSrc?>" name="submit">
				</form>
			</td>
		</tr>
	</table>
<?
}
else
{
	echo Loc::getMessage("SALE_HPS_PAYPAL_I3");
}
?>
