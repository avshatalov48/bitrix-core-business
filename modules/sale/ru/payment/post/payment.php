<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<p><strong>Адрес перевода:</strong></p>
<p><?= htmlspecialcharsbx(CSalePaySystemAction::GetParamValue("POST_ADDRESS")) ?></p>
<p><strong>Счет № <?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?> от <?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_UPDATE"]) ?></strong></p>

<p><strong>Плательщик:</strong> <?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("PAYER_NAME")) ?></p>
<p>Сумма к оплате: <strong><?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></strong></p>

<p>Счет действителен в течение трех дней.</p>
