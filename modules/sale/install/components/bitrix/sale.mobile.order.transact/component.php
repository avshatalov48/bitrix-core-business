<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage("SMOT_SALE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError("SMOT_MOBILEAPP_NOT_INSTALLED");
	return;
}

if (isset($_REQUEST['id']))
	$orderId = $_REQUEST['id'];
else
	return;

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($orderId, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());

if(!$bUserCanViewOrder)
{
	echo ShowError(GetMessage("SMOT_NO_PERMS2VIEW"));
	return;
}

$arResult["ORDER"] = CSaleMobileOrderUtils::getOrderInfoDetail($orderId);

$arResult["TYPES"] = array(
	"ORDER_PAY" => GetMessage("SMOT_TR_TYPE_PAYMENT"),
	"CC_CHARGE_OFF" => GetMessage("SMOT_TR_TYPE_FROM_CARD"),
	"OUT_CHARGE_OFF" => GetMessage("SMOT_TR_TYPE_INPUT"),
	"ORDER_UNPAY" => GetMessage("SMOT_TR_TYPE_CANCEL_PAYMENT"),
	"ORDER_CANCEL_PART" => GetMessage("SMOT_TR_TYPE_CANCEL_SEMIPAYMENT"),
	"MANUAL" => GetMessage("SMOT_TR_TYPE_HAND"),
	"DEL_ACCOUNT" => GetMessage("SMOT_TR_TYPE_DELETE"),
	"AFFILIATE" => GetMessage("SMOT_MOBILEAPP_NOT_INSTALLED")
);

$dbTransact = CSaleUserTransact::GetList(
		array("TRANSACT_DATE" => "DESC"),
		array("ORDER_ID" => $orderId),
		false,
		false,
		array("ID", "USER_ID", "AMOUNT", "CURRENCY", "DEBIT", "ORDER_ID", "DESCRIPTION", "NOTES", "TIMESTAMP_X", "TRANSACT_DATE")
	);

while ($arTransact = $dbTransact->Fetch())
{
	$arTransact["AMOUNT_PREPARED"] = (($arTransact["DEBIT"] == "Y") ? "+" : "-").SaleFormatCurrency($arTransact["AMOUNT"], $arTransact["CURRENCY"]);
	$arResult["TRANSACTS"][] = $arTransact;
}

$this->IncludeComponentTemplate();
?>