<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Sale\Order;

function liqpay_parseTag($rs, $tag)
{
	$rs = str_replace("\n", "", str_replace("\r", "", $rs));
	$tags = '<'.$tag.'>';
	$tage = '</'.$tag;
	$start = strpos($rs, $tags)+strlen($tags);
	$end = strpos($rs, $tage);

	return substr($rs, $start, ($end-$start));
}

if ($_POST['signature']=="" || $_POST['operation_xml']=="")
	die();

$insig = $_POST['signature'];
$resp = base64_decode($_POST['operation_xml']);
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$entityId = str_replace("PAYMENT_", "", liqpay_parseTag($resp, "order_id"));

list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment($entityId);

if ($orderId > 0)
{
	/** @var \Bitrix\Sale\Order $order */
	$order = \Bitrix\Sale\Order::load($orderId);
	if ($order)
	{
		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $order->getPaymentCollection();
		if ($paymentCollection && $paymentId > 0)
		{
			/** @var \Bitrix\Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);
			if ($payment)
			{
				$service = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
				if ($service)
					$service->processRequest($request);
			}
		}
	}
}
