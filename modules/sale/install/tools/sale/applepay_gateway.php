<?php
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main,
	Bitrix\Sale;

$result = [];
if (Main\Loader::includeModule("sale"))
{
	$request = Main\Application::getInstance()->getContext()->getRequest();
	$paymentId = (int)$request->get('PAYMENT_ID');
	$paySystemId = (int)$request->get('PAYSYSTEM_ID');

	if ($content = file_get_contents('php://input'))
	{
		$content = Main\Web\Json::decode($content);
		$paymentData = $content['payment']['paymentToken']['paymentData'] ?? '';
		$request->set(array_merge($request->toArray(), [
			'paymentData' => $paymentData
		]));
	}

	if ($paymentId <= 0 || $paySystemId <= 0)
	{
		$result = [
			'status' => 'STATUS_FAIL',
		];
	}
	else
	{
		$service = Sale\PaySystem\Manager::getObjectById($paySystemId);
		if ($service)
		{
			[$orderId, $paymentId] = Sale\PaySystem\Manager::getIdsByPayment($paymentId, $service->getField('ENTITY_REGISTRY_TYPE'));

			$registry = Sale\Registry::getInstance($service->getField('ENTITY_REGISTRY_TYPE'));
			/** @var Sale\Order $orderClassName */
			$orderClassName = $registry->getOrderClassName();

			$order = $orderClassName::load($orderId);
			$paymentCollection = $order->getPaymentCollection();
			/** @var Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);

			$initResult = $service->initiatePay($payment, $request, Sale\PaySystem\BaseServiceHandler::STRING);
			if ($initResult->isSuccess())
			{
				$result = [
					'status' => 'STATUS_SUCCESS',
				];
			}
			else
			{
				$result = [
					'status' => 'STATUS_FAIL',
				];
			}
		}
	}
}

if (empty($result)
	|| ($result['status'] === 'STATUS_FAIL')
)
{
	$debugInfo = http_build_query($request->toArray(), "", "\n");

	$content = file_get_contents('php://input');
	if ($content)
	{
		$debugInfo .= "\ncontent=".$content;
	}

	Sale\PaySystem\Logger::addDebugInfo('Apple Pay Gateway. Request: '.($debugInfo ? $debugInfo : "empty"));
}

/** @noinspection PhpVariableNamingConventionInspection */
global $APPLICATION;
$APPLICATION->restartBuffer();
header('Content-Type:application/json; charset=UTF-8');

echo Main\Web\Json::encode($result, JSON_UNESCAPED_UNICODE);

\CMain::FinalActions();
die();