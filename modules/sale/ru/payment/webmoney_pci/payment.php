<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$strPAYEE_PURSE = CSalePaySystemAction::GetParamValue("ACC_NUMBER");

$SERVER_NAME_tmp = "";
if (defined("SITE_SERVER_NAME"))
	$SERVER_NAME_tmp = SITE_SERVER_NAME;
if (strlen($SERVER_NAME_tmp)<=0)
	$SERVER_NAME_tmp = COption::GetOptionString("main", "server_name", "");

$strPayPath  = "";
$strPayPath .= "url=".urlencode("http://".$SERVER_NAME_tmp.(CSalePaySystemAction::GetParamValue("PATH_TO_RESULT"))."?ORDER_ID=".IntVal(CSalePaySystemAction::GetParamValue("ORDER_ID")));
$strPayPath .= "&purse=".$strPAYEE_PURSE;
$strPayPath .= "&amount=".round(CSalePaySystemAction::GetParamValue("SHOULD_PAY"), 2);
$strPayPath .= "&method=POST";
$strPayPath .= "&desc=Order_".IntVal(CSalePaySystemAction::GetParamValue("ORDER_ID"));
$strPayPath .= "&mode=".CSalePaySystemAction::GetParamValue("TEST_MODE");
?>
<div class="container-fluid">
	<div class="row mb-3">
		<div class="col">
			Если Вы пользуетесь <strong>WMKeeper Classic</strong>, перейдите для оплаты заказа по следующей ссылке: <a class="" href="wmk:paylink?<?= $strPayPath ?>"><strong>Оплатить заказ</strong></a>
		</div>
	</div>

	<div class="row">
		<div class="col">
			Если Вы пользуетесь <strong>WMKeeper Light</strong>, перейдите для оплаты заказа по следующей ссылке: <a class="" href="https://light.webmoney.ru/pci.aspx?<?= $strPayPath ?>"><strong>Оплатить заказ</strong></a>
		</div>
	</div>
</div>