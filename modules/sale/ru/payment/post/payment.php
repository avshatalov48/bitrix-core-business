<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><p><font class="tableheadtext"><b>Адрес перевода:</b></font></p>
<p><font class="tablebodytext">
<?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("POST_ADDRESS")) ?>
</font></p>

<p><font class="tablebodytext"><b>Счет № <?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?> от <?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_UPDATE"]) ?></b></font></p>

<p><font class="tablebodytext">Плательщик: <?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("PAYER_NAME")) ?><br>
Сумма к оплате: <b><?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></b>
</font></p>


<p><font class="tablebodytext">Счет действителен в течение трех дней.</font></p>
