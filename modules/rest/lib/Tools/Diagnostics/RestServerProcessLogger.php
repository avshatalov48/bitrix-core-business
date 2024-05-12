<?php

namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main;
use Bitrix\Intranet\CurrentUser;
use Bitrix\Main\Config\Option;
use Bitrix\Rest\LogTable;
use Exception;

final class RestServerProcessLogger
{
	private ?int $requestId = null;

	public function __construct(private \CRestServer $restServer)
	{
	}

	/**
	 * @throws Exception
	 */
	public function logRequest(): void
	{
		if (!is_null($this->restServer) && $this->shouldLog())
		{
			$this->addRequestEntry();
		}
	}

	/**
	 * @throws Exception
	 */
	public function logResponse(mixed $responseData): bool
	{
		if (is_null($this->requestId))
		{
			return false;
		}

		LogTable::filterResponseData($responseData);
		$this->restServer->sendHeaders();
		$fields = [
			'RESPONSE_STATUS' => \CHTTP::GetLastStatus(),
			'RESPONSE_DATA' => $responseData,
		];

		return LogTable::update($this->requestId, $fields)->isSuccess();
	}

	private function shouldLog(): bool
	{
		$logEndTime = (int)Option::get('rest', 'log_end_time', 0);

		if ($logEndTime < time())
		{
			return false;
		}

		$logOptions = @unserialize(
			Option::get('rest', 'log_filters', ''),
			[
				'allowed_classes' => false
			]
		);

		if (!is_array($logOptions))
		{
			$logOptions = [];
		}

		return !((isset($logOptions['client_id']) && $this->restServer->getClientId() !== $logOptions['client_id'])
			|| (isset($logOptions['password_id']) && $this->restServer->getPasswordId() !== $logOptions['password_id'])
			|| (isset($logOptions['scope']) && $this->restServer->getScope() !== $logOptions['scope'])
			|| (isset($logOptions['method']) && $this->restServer->getMethod() !== $logOptions['method'])
			|| (isset($logOptions['user_id']) && CurrentUser::get()->getId() !== $logOptions['user_id']));
	}

	/**
	 * @throws Exception
	 */
	private function addRequestEntry(): void
	{
		$request = Main\Context::getCurrent()?->getRequest();
		$this->requestId = LogTable::addEntry($this->restServer, $request);
	}
}