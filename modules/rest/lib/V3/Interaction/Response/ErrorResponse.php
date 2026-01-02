<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Main\Error;
use Bitrix\Rest\RestExceptionInterface;

class ErrorResponse extends ArrayResponse implements RestExceptionInterface
{
	protected bool $showDebugInfo = false;

	protected bool $showRawData = true;

	protected string $status = \CRestServer::STATUS_WRONG_REQUEST;

	protected int $code;

	/**
	 * @param Error[] $errors
	 */
	public function __construct(array $errors)
	{
		$error = $errors[0];
		$this->status = $error->getCode();
		$this->code = (int) $error->getCode();
		$result = ['error' => $this->getSingleErrorResponseData($error)];
		parent::__construct($result);
	}

	protected function getSingleErrorResponseData(Error $error): array
	{
		$data = [
			'code' => $error->getCode(),
			'message' => $error->getMessage(),
		];

		if ($error->getCustomData())
		{
			$data = array_merge($data, $error->getCustomData());
		}

		return $data;
	}

	public function output(): array
	{
		return [];
	}

	public function getStatus(): string
	{
		return $this->status;
	}
}