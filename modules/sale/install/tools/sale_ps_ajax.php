<?php
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

Bitrix\Main\Localization\Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/install/tools/sale_ps_ajax.php');

$result = [];
if (Bitrix\Main\Loader::includeModule("sale"))
{
	$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	$paymentId = (int)$request->get('PAYMENT_ID');
	$paySystemId = (int)$request->get('PAYSYSTEM_ID');

	if ($paymentId <= 0 || $paySystemId <= 0)
	{
		$result = [
			'status' => 'error',
			'errors' => [\Bitrix\Main\Localization\Loc::getMessage('SALE_PS_AJAX_PARAMS_ERROR')]
		];
	}
	else
	{
		$service = Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId);
		if ($service)
		{
			list($orderId, $paymentId) = Bitrix\Sale\PaySystem\Manager::getIdsByPayment($paymentId, $service->getField('ENTITY_REGISTRY_TYPE'));

			$registry = Bitrix\Sale\Registry::getInstance($service->getField('ENTITY_REGISTRY_TYPE'));
			/** @var Bitrix\Sale\Order $orderClassName */
			$orderClassName = $registry->getOrderClassName();

			$order = $orderClassName::load($orderId);
			if (!Bitrix\Sale\OrderStatus::isAllowPay($order->getField('STATUS_ID')))
			{
				$result = [
					'status' => 'error',
					'errors' => [\Bitrix\Main\Localization\Loc::getMessage('SALE_PS_AJAX_ORDER_PAID_ERROR')]
				];
			}
			else
			{
				$paymentCollection = $order->getPaymentCollection();
				/** @var Bitrix\Sale\Payment $payment */
				$payment = $paymentCollection->getItemById($paymentId);

				if ($returnUrl = $request->get("RETURN_URL"))
				{
					$service->getContext()->setUrl($returnUrl);
				}

				$initResult = $service->initiatePay($payment, $request, Bitrix\Sale\PaySystem\BaseServiceHandler::STRING);
				if ($initResult->isSuccess())
				{
					$result = [
						'status' => 'success',
						'data' => $initResult->getData(),
						'template' => $initResult->getTemplate(),
					];
				}
				else
				{
					$result = [
						'status' => 'error',
						'errors' => $initResult->getErrorMessages(),
						'buyerErrors' => $initResult->getBuyerErrorMessages(),
					];
				}
			}
		}
	}
}

/** @noinspection PhpVariableNamingConventionInspection */
global $APPLICATION;
$APPLICATION->restartBuffer();
header('Content-Type:application/json; charset=UTF-8');

echo Bitrix\Main\Web\Json::encode($result, JSON_UNESCAPED_UNICODE);

\CMain::FinalActions();
die();