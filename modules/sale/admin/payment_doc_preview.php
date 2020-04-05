<?php

use Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Sale;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$request = Application::getInstance()->getContext()->getRequest();

$orderId = $request->get('ORDER_ID');
$paymentId = $request->get('PAYMENT_ID');

$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
/** @var Sale\Order $orderClassName */
$orderClassName = $registry->getOrderClassName();

/** @var Sale\Order $order */
$order = $orderClassName::load($orderId);

if ($order > 0)
{
	$paymentCollection = $order->getPaymentCollection();

	if ($paymentId > 0)
	{
		$payment = $paymentCollection->getItemById($paymentId);
	}

	if ($payment !== null)
	{
		$service = $payment->getPaySystem();
		if ($service && $service->isAffordPdf())
		{
			$result = $service->initiatePay($payment, $context->getRequest());
		}
	}
}
