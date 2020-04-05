<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Sale\Order;

include(GetLangFileName(dirname(__FILE__)."/", "/assist.php"));

$ordernumber = $_POST["ordernumber"];
list($orderId, $paymentId) = explode(':', $ordernumber);
$orderId = intval($orderId);
$paymentId = intval($paymentId);
$merchant_id = $_POST["merchant_id"];
$billnumber = $_POST["billnumber"];
$ordercomment = $_POST["ordercomment"];
$orderamount = $_POST["orderamount"];
$ordercurrency = $_POST["ordercurrency"];
$amount = $_POST["amount"];
$currency = $_POST["currency"];
$meantypename = $_POST["meantypename"];
$meantype_id = $_POST["meantype_id"];
$meansubtype = $_POST["meansubtype"];
$meannumber = $_POST["meannumber"];
$orderstate = $_POST["orderstate"];
$orderdate = $_POST["orderdate"];
$responsecode = $_POST["responsecode"];
$message = $_POST["message"];
$customermessage = $_POST["customermessage"];
$recommendation = $_POST["recommendation"];
$approvalcode = $_POST["approvalcode"];
$processingname = $_POST["processingname"];
$operationtype = $_POST["operationtype"];
$checkvalue = $_POST["checkvalue"];
$packetdate = $_POST["packetdate"];

$bCorrectPayment = true;

/** @var \Bitrix\Sale\Order $order */
$order = Order::load($orderId);

if(!$order)
	$bCorrectPayment = false;

$payment = $order->getPaymentCollection()->getItemById($paymentId);

if(!$payment)
	$bCorrectPayment = false;

$arOrder = $order->getFieldValues();

if ($bCorrectPayment)
	CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"], '', array(), $payment->getFieldValues());

$assist_Shop_IDP = CSalePaySystemAction::GetParamValue("SHOP_IDP");
$password = CSalePaySystemAction::GetParamValue("SHOP_SECRET_WORLD");

if(strlen($password) <= 0)
	$bCorrectPayment = false;

if($bCorrectPayment)
{
	$check = ToUpper(md5(toUpper(md5($password).md5($assist_Shop_IDP.$ordernumber.$amount.$currency.$orderstate))));

	if(ToUpper($checkvalue) != ToUpper($check))
		$bCorrectPayment = False;
}

$aDesc = array(
	"In Process" => array(GetMessage("SASP_IP"), GetMessage("SASPD_IP")),
	"Delayed" => array(GetMessage("SASP_D"), GetMessage("SASPD_D")),
	"Approved" => array(GetMessage("SASP_A"), GetMessage("SASPD_A")),
	"PartialApproved" => array(GetMessage("SASP_PA"), GetMessage("SASPD_PA")),
	"PartialDelayed" => array(GetMessage("SASP_PD"), GetMessage("SASPD_PD")),
	"Canceled" => array(GetMessage("SASP_C"), GetMessage("SASPD_C")),
	"PartialCanceled" => array(GetMessage("SASP_PC"), GetMessage("SASPD_PC")),
	"Declined" => array(GetMessage("SASP_DEC"), GetMessage("SASPD_DEC")),
	"Timeout" => array(GetMessage("SASP_T"), GetMessage("SASPD_T")),
);

if($bCorrectPayment)
{
	$arFields = array(
			"PS_STATUS" => ($orderstate == "Approved"?"Y":"N"),
			"PS_STATUS_CODE" => substr($orderstate, 0, 5),
			"PS_STATUS_DESCRIPTION" => $aDesc[$orderstate][0],
			"PS_STATUS_MESSAGE" => $aDesc[$orderstate][1],
			"PS_SUM" => DoubleVal($orderamount),
			"PS_CURRENCY" => $ordercurrency,
			"PS_RESPONSE_DATE" => new \Bitrix\Main\Type\DateTime(),
		);

	if (CSalePaySystemAction::GetParamValue("PAYED") != "Y" && CSalePaySystemAction::GetParamValue("AUTOPAY") == "Y" && $arFields["PS_STATUS"] == "Y" && Doubleval(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) == DoubleVal($arFields["PS_SUM"]))
	{
		$payment->setField('PAID', 'Y');
	}

	if(!empty($arFields))
	{
		$result = $payment->setFields($arFields);
		if ($result->isSuccess())
			$order->save();
	}
}

$APPLICATION->RestartBuffer();

$dateISO = date("Y-m-d\TH:i:s").substr(date("O"), 0, 3).":".substr(date("O"), -2, 2);
header("Content-Type: text/xml");
header("Pragma: no-cache");
$text = "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";
if($bCorrectPayment)
{
	$text .= "<pushpaymentresult firstcode=\"0\" secondcode=\"0\">";
	$text .= "<order>";
	$text .= "<billnumber>".$billnumber."</billnumber> ";
	$text .= "<packetdate>".$packetdate."</packetdate> ";
	$text .= "</order>";
}
else
{
	$text .= "<pushpaymentresult firstcode=\"9\" secondcode=\"7\">";
}

$text .= "</pushpaymentresult>";
echo $text;
die();
?>