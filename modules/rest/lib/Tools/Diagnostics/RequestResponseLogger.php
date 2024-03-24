<?php
namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main;
use Bitrix\Intranet\CurrentUser;
use Bitrix\Main\Config\Option;
use Bitrix\Rest\LogTable;

final class RequestResponseLogger
{
	private static self $instance;

	private ?\CRestServer $restServer = null;
	private ?int $requestId = null;

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function setServer(\CRestServer $server): self
	{
		$this->restServer = $server;

		return $this;
	}

	public function logRequest(): void
	{
		if (!is_null($this->restServer) && $this->shouldLog())
		{
			$this->addRequestEntry();
		}
	}

	public function logResponse(mixed $responseData): bool
	{
		if (is_null($this->requestId))
		{
			return false;
		}

		LogTable::filterResponseData($responseData);
		$fields = [
			'RESPONSE_STATUS' => \CHTTP::GetLastStatus(),
			'RESPONSE_DATA' => $responseData,
		];

		$result = LogTable::update($this->requestId, $fields);

		return $result->isSuccess();
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
			$logOptions = array();
		}

		if (
			isset($logOptions['client_id']) && $this->restServer->getClientId() !== $logOptions['client_id']
			|| isset($logOptions['password_id']) && $this->restServer->getPasswordId() !== $logOptions['password_id']
			|| isset($logOptions['scope']) && $this->restServer->getScope() !== $logOptions['scope']
			|| isset($logOptions['method']) && $this->restServer->getMethod() !== $logOptions['method']
			|| isset($logOptions['user_id']) && CurrentUser::get()->getId() !== $logOptions['user_id']
		)
		{
			return false;
		}

		return true;
	}

	private function addRequestEntry(): void
	{
		$request = Main\Context::getCurrent()->getRequest();
		$this->requestId = LogTable::addEntry($this->restServer, $request);
	}
}