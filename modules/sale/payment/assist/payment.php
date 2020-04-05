<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$entityId = (strlen(CSalePaySystemAction::GetParamValue("PAYMENT_ID")) > 0) ? CSalePaySystemAction::GetParamValue("PAYMENT_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["PAYMENT"]["ID"];
list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment($entityId);

/** @var \Bitrix\Sale\Order $order */
$order = \Bitrix\Sale\Order::load($orderId);

/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
$paymentCollection = $order->getPaymentCollection();

/** @var \Bitrix\Sale\Payment $payment */
$payment = $paymentCollection->getItemById($paymentId);

$data = \Bitrix\Sale\PaySystem\Manager::getById($payment->getPaymentSystemId());

$service = new \Bitrix\Sale\PaySystem\Service($data);
$service->initiatePay($payment);
