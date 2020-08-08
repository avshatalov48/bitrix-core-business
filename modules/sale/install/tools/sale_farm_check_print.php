<?php

use Bitrix\Main;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Cashbox\Logger;

define('NOT_CHECK_PERMISSIONS', true);
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("sale"))
	return;

$data = array();
$json = file_get_contents('php://input');

if ($json)
{
	Logger::addDebugInfo($json);

	$data = Main\Web\Json::decode($json);
}

if (array_key_exists('payload', $data))
{
	Cashbox\CashboxAtolFarm::applyCheckResult($data);
}