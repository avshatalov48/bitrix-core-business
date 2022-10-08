<?php

namespace Bitrix\Location\Infrastructure\Service\LoggerService;

final class CEventLogger implements ILogger
{
	public function log(int $level, string $message, int $eventType = 0, array $context = [])
	{
		\CEventLog::Add(array(
			"SEVERITY" => $this->getSeverity($level),
			"AUDIT_TYPE_ID" => "LOCATION_MODULE_EVENT_".(string)$eventType,
			"MODULE_ID" => "location",
			"ITEM_ID" => "",
			"DESCRIPTION" => $message
		));
	}

	private function getSeverity($logLevel)
	{
		if($logLevel <= 400)
		{
			$result = 'ERROR';
		}
		elseif($logLevel <= 500)
		{
			$result = 'WARNING';
		}
		elseif ($logLevel <= 700)
		{
			$result = 'INFO';
		}
		else
		{
			$result = 'DEBUG';
		}
		return $result;
	}
}
