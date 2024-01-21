<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

final class ArgumentTypeException extends ArgumentException
{
	protected mixed $requiredType;

	public function __construct(string $parameter, mixed $requiredType = '', \Exception $previous = null)
	{
		if (!empty($requiredType))
		{
			$message = sprintf("The value of an argument '%s' must be of type %s", $parameter, $requiredType);
		}
		else
		{
			$message = sprintf("The value of an argument '%s' has an invalid type", $parameter);
		}

		$this->requiredType = $requiredType;

		parent::__construct($message, $parameter, $previous);
	}

	public function getRequiredType(): mixed
	{
		return $this->requiredType;
	}
}