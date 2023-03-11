<?php

use Bitrix\Main;
use Bitrix\Sale;

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define('DisableEventsCheck', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (Main\Loader::includeModule('sale'))
{
	$request = Main\Application::getInstance()->getContext()->getRequest();
	$stream = file_get_contents('php://input');
	if ($stream)
	{
		try
		{
			$data = Main\Web\Json::decode($stream);
		}
		catch (Main\ArgumentException $exception)
		{
			$data = [];
		}

		foreach ($data as $key => $value)
		{
			$request->set($key, $value);
		}
	}

	$debugInfo = http_build_query($request->toArray(), '', "\n");
	Sale\PaySystem\Logger::addDebugInfo('Robokassa register callback. Request: ' . ($debugInfo ?: 'empty'));

	$registerService = new Sale\PaySystem\Robokassa\RegisterService();
	$processRequestResult = $registerService->processRequest($request);
	if (!$processRequestResult->isSuccess())
	{
		Sale\PaySystem\Logger::addError(
			'Robokassa register callback. Error: ' . implode("\n", $processRequestResult->getErrorMessages())
		);
	}
}

Main\Application::getInstance()->terminate();