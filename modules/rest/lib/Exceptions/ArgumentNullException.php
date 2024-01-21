<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

final class ArgumentNullException extends ArgumentException
{
	public function __construct(string $parameter = '', \Exception $previous = null)
	{
		$message = sprintf("Argument '%s' is null or empty", $parameter);
		parent::__construct($message, $parameter, $previous);

		$this->parameter = $parameter;
	}
}