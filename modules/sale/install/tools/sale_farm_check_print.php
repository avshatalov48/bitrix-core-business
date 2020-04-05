<?php

use Bitrix\Main;
use Bitrix\Sale\Cashbox;

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
	if (Cashbox\Manager::DEBUG_MODE === true)
		Cashbox\Internals\CashboxErrLogTable::add(array('MESSAGE' => $json, 'DATE_INSERT' => new Main\Type\DateTime()));

	$data = Main\Web\Json::decode($json);
}

if (array_key_exists('payload', $data))
{
	Cashbox\CashboxAtolFarm::applyCheckResult($data);
}