<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

final class ArgumentOutOfRangeException extends ArgumentException
{
	protected mixed $lowerLimit;
	protected mixed $upperLimit;

	public function __construct(string $parameter, mixed $lowerLimit = null, mixed $upperLimit = null, \Exception $previous = null)
	{
		if (\is_array($lowerLimit))
		{
			$message = sprintf("The value of an argument '%s' is outside the allowable range of values: %s", $parameter, implode(", ", $lowerLimit));
		}
		elseif (($lowerLimit !== null) && ($upperLimit !== null))
		{
			$message = sprintf("The value of an argument '%s' is outside the allowable range of values: from %s to %s", $parameter, $lowerLimit, $upperLimit);
		}
		elseif (($lowerLimit === null) && ($upperLimit !== null))
		{
			$message = sprintf("The value of an argument '%s' is outside the allowable range of values: not greater than %s", $parameter, $upperLimit);
		}
		elseif (($lowerLimit !== null) && ($upperLimit === null))
		{
			$message = sprintf("The value of an argument '%s' is outside the allowable range of values: not less than %s", $parameter, $lowerLimit);
		} else
		{
			$message = sprintf("The value of an argument '%s' is outside the allowable range of values", $parameter);
		}

		$this->lowerLimit = $lowerLimit;
		$this->upperLimit = $upperLimit;

		parent::__construct($message, $parameter, $previous);
	}

	public function getLowerLimitType(): mixed
	{
		return $this->lowerLimit;
	}

	public function getUpperType(): mixed
	{
		return $this->upperLimit;
	}
}