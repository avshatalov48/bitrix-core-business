<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

use Bitrix\Rest\RestException;

class ArgumentException extends RestException
{
	protected string $parameter;

	public function __construct(string $message = '', string $parameter = '', \Exception $previous = null)
	{
		parent::__construct($message, self::ERROR_ARGUMENT,
			\CRestServer::STATUS_WRONG_REQUEST,
			$previous
		);

		$this->parameter = $parameter;

		$this->setAdditional([
			'argument' => $this->getParameter(),
		]);
	}

	public function getParameter(): string
	{
		return $this->parameter;
	}
}