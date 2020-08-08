<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Sale\Order;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$entityId = intval($request->get("orderNumber"));
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

die();