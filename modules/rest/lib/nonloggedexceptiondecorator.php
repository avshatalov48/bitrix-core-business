<?php

declare(strict_types=1);

namespace Bitrix\Rest;

use Exception;

final class NonLoggedExceptionDecorator extends Exception
{
	private Exception $originalException;

	public function __construct(Exception $exception)
	{
		$this->originalException = $exception;

		parent::__construct(
			$exception->getMessage(),
			$exception->getCode(),
			$exception->getPrevious()
		);
	}

	public function getOriginalException(): Exception
	{
		return $this->originalException;
	}
}