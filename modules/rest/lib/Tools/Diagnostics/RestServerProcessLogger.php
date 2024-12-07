<?php

namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main;
use Bitrix\Rest\LogTable;
use Exception;

final class RestServerProcessLogger
{
	private ?int $requestLogId;

	public function __construct(private readonly \CRestServer $restServer)
	{
	}

	public function logRequest(): void
	{
		if (!is_null($this->restServer))
		{
			$request = Main\Context::getCurrent()?->getRequest();
			$logger = LoggerManager::getInstance()->getLogger();
			$logger?->info('Start', [
				'CLIENT_ID' => $this->restServer->getClientId(),
				'PASSWORD_ID' => $this->restServer->getPasswordId(),
				'SCOPE' => $this->restServer->getScope(),
				'METHOD' => $this->restServer->getMethod(),
				'REQUEST_METHOD' => $request->getRequestMethod(),
				'REQUEST_URI' => $request->getRequestUri(),
				'REQUEST_AUTH' => $this->restServer->getAuth(),
				'REQUEST_DATA' => $this->restServer->getQuery(),
			]);
			$this->requestLogId = $logger instanceof DataBaseLogger ? $logger->getLogId() : null;
		}
	}

	/**
	 * @throws Exception
	 */
	public function logResponse(mixed $responseData): bool
	{
		if (is_null($this->requestLogId))
		{
			return false;
		}

		LogTable::filterResponseData($responseData);
		$this->restServer->sendHeaders();
		$fields = [
			'RESPONSE_STATUS' => \CHTTP::GetLastStatus(),
			'RESPONSE_DATA' => $responseData,
			'MESSAGE' => 'Successful response',
		];

		return LogTable::update($this->requestLogId, $fields)->isSuccess();
	}
}