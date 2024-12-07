<?php

use Bitrix\Main;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Cashbox\Logger;
use Bitrix\Sale\Cashbox\ReportManager;

define('NOT_CHECK_PERMISSIONS', true);
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!Main\Loader::includeModule('sale'))
	return;

global $CACHE_MANAGER, $APPLICATION;
$startExecTime = time();
$timeLimit = 60;
$maxExecTime = intval(intval(ini_get("max_execution_time")) * 0.75);
$maxExecTime = ($maxExecTime === 0 || $maxExecTime > $timeLimit) ? $timeLimit : $maxExecTime;

$request = Main\Application::getInstance()->getContext()->getRequest();
$accessDenied = true;
$hash = $request->get('hash');
if ($hash)
{
	$hash = trim($hash);
	$dbRes = Cashbox\Internals\CashboxConnectTable::getById($hash);
	if ($data = $dbRes->fetch())
		$accessDenied = ($data['ACTIVE'] !== 'Y');
}

if ($accessDenied)
{
	Logger::addDebugInfo("403 Forbidden");

	CHTTP::SetStatus("403 Forbidden");
	$APPLICATION->FinalActions();
	die();
}

$result = new stdClass();
$data = array();
$error = false;
$json = file_get_contents('php://input');

if ($json)
{
	Logger::addDebugInfo($json);

	$data = Main\Web\Json::decode($json);
}

if (isset($data['kkm']) && count($data['kkm']) > 0)
{
	if (isset($data['api_version']) && (string)$data['api_version'] === '3')
	{
		/** @var Cashbox\CashboxBitrixV3 $cashboxHandler */
		$cashboxHandler = Cashbox\CashboxBitrixV3::class;
	}
	elseif (isset($data['api_version']) && (string)$data['api_version'] === '2')
	{
		/** @var Cashbox\CashboxBitrix $cashboxHandler */
		$cashboxHandler = Cashbox\CashboxBitrixV2::class;
	}
	else
	{
		/** @var Cashbox\CashboxBitrix $cashboxHandler */
		$cashboxHandler = Cashbox\CashboxBitrix::class;
	}

	$processedCheckIds = $cashboxHandler::applyPrintResult($data);
	if ($processedCheckIds)
	{
		$result->ack = $processedCheckIds;
	}
	else
	{
		$cashboxList = $cashboxHandler::getCashboxList($data);
		foreach ($cashboxList as $item)
		{
			$cashboxHandler::saveCashbox($item);
		}

		$enabledCashbox = array();
		foreach ($cashboxList as $item)
		{
			if ($item['PRESENTLY_ENABLED'] === 'Y'
				&& $item['ACTIVE'] === 'Y'
			)
			{
				$enabledCashbox[$item['ID']] = $item;
			}
		}

		if ($enabledCashbox)
		{
			$cashboxIds = array_keys($enabledCashbox);

			$reports = array();
			foreach ($cashboxIds as $id)
			{
				$reportId = Cashbox\ReportManager::getPrintableZReport($id);
				if ($reportId > 0)
				{
					$reportQuery = Cashbox\Manager::buildZReportQuery($id, $reportId);
					if ($reportQuery)
						$reports[] = $reportQuery;
				}
			}

			if ($reports)
				$result->reports = $reports;

			$buildResult = Cashbox\Manager::buildChecksQuery($cashboxIds);
			$printed = !empty($buildResult) || !empty($reports);

			while (!$printed)
			{
				$ready = false;
				foreach ($cashboxIds as $id)
				{
					if ($CACHE_MANAGER->GetImmediate(CACHED_b_sale_order, "sale_checks_".$id))
					{
						$ready = true;
						break;
					}
				}

				if ($ready)
				{
					foreach ($cashboxIds as $id)
						$CACHE_MANAGER->Clean("sale_checks_".$id);

					$buildResult = Cashbox\Manager::buildChecksQuery($cashboxIds);
					$printed = !empty($buildResult);
				}

				usleep(500000);
				if (time() - $startExecTime > $maxExecTime)
					break;
			}

			if ($buildResult)
			{
				$result->print = $buildResult;
			}
		}
		else
		{
			Logger::addDebugInfo("enabled cashbox was not found");
			$error = true;
		}
	}
}
else
{
	Logger::addDebugInfo("empty kkm list");
	$error = true;
}

if ($error)
{
	while (true)
	{
		sleep(5);
		if (time() - $startExecTime > $maxExecTime)
			break;
	}
}

$APPLICATION->RestartBuffer();
header('Content-Type: application/json');

echo Main\Web\Json::encode($result);

$APPLICATION->FinalActions();
