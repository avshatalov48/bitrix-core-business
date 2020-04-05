<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
?>
<form method=post action=http://www.kreditpilot.com/servlets/com.kreditpilot.server.FirstStep target="_blank">
	<input type=hidden name=BillNumber value="<?echo $ORDER_ID?>">
	<p>Вы хотите оплатить через систему <strong>www.kreditpilot.ru</strong>.</p>
	<p>Cчет № <?echo $ORDER_ID." от ".CSalePaySystemAction::GetParamValue("DATE_INSERT")?></p>
	<input type=hidden name=BillDescription value="Order &nbsp;<?echo $ORDER_ID?>&nbsp">
	<input type=hidden name=BillSum value="<?echo CSalePaySystemAction::GetParamValue("SHOULD_PAY")?>">
	<p>Сумма к оплате по счету: <?echo SaleFormatCurrency(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), CSalePaySystemAction::GetParamValue("CURRENCY"))?></p>
	<input type=hidden name=BillShopId value="<?echo CSalePaySystemAction::GetParamValue("SHOP_ID")?>">
	<input type=hidden name=BillDate value="<?echo CSalePaySystemAction::GetParamValue("DATE_INSERT")?>">
	<input type=hidden name=BillCurrency value="<?echo (CSalePaySystemAction::GetParamValue("CURRENCY") == "RUR"? "руб.":"")?>">
	<input type=submit name=sub value="Оплатить" class="btn btn-primary">
</form>
