<?php

namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Rest\LogTable;
use Bitrix\Main\Diag\Logger;

/**
 * Class DataBaseLogger
 * @package Bitrix\Rest\Tools\Diagnostics
 */
class DataBaseLogger extends Logger
{
	private ?int $logId = null;

	protected function logMessage(string $level, string $message)
	{
		if ($this->shouldLog() && LoggerManager::getInstance()->isActive())
		{
			$result = LogTable::add(
				[
					'CLIENT_ID' => $this->context['CLIENT_ID'] ?? null,
					'PASSWORD_ID' => $this->context['PASSWORD_ID'] ?? null,
					'SCOPE' => $this->context['SCOPE'] ?? null,
					'METHOD' => $this->context['METHOD'] ?? null,
					'EVENT_ID' => $this->context['EVENT_ID'] ?? null,
					'REQUEST_METHOD' => $this->context['REQUEST_METHOD'] ?? null,
					'REQUEST_URI' => $this->context['REQUEST_URI'] ?? null,
					'REQUEST_AUTH' => $this->context['REQUEST_AUTH'] ?? null,
					'REQUEST_DATA' => $this->context['REQUEST_DATA'] ?? null,
					'RESPONSE_STATUS' => $this->context['RESPONSE_STATUS'] ?? null,
					'RESPONSE_DATA' => $this->context['RESPONSE_DATA'] ?? null,
					'MESSAGE' => $this->context['MESSAGE'] ?? $message,
				]
			);
			$this->logId = $result->isSuccess() ? $result->getId() : null;
		}
	}

	public function log($level, \Stringable|string $message, array $context = []): void
	{
		LogTable::filterResponseData($context);

		parent::log($level, $message, $context);
	}

	public function getLogId(): ?int
	{
		return $this->logId;
	}

	protected function shouldLog(): bool
	{
		$logOptions = LoggerManager::getInstance()->getFilterOptions();

		return !((isset($logOptions['client_id'], $this->context['CLIENT_ID']) && ($this->context['CLIENT_ID'] !== $logOptions['client_id']))
			|| (isset($logOptions['password_id'], $this->context['PASSWORD_ID']) && ($this->context['PASSWORD_ID'] !== $logOptions['password_id']))
			|| (isset($logOptions['scope'], $this->context['SCOPE']) && ($this->context['SCOPE'] !== $logOptions['scope']))
			|| (isset($logOptions['method'], $this->context['METHOD']) && ($this->context['METHOD'] !== $logOptions['method']))
			|| (isset($logOptions['user_id']) && CurrentUser::get()->getId() !== $logOptions['user_id']));
	}
}