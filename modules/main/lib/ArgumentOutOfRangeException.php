<?php

namespace Bitrix\Main;

/**
 * Exception is thrown when the value of an argument is outside the allowable range of values.
 */
class ArgumentOutOfRangeException extends ArgumentException
{
	protected $lowerLimit;
	protected $upperLimit;

	/**
	 * Creates new exception object.
	 *
	 * @param string $parameter Argument that generates exception
	 * @param null $lowerLimit Either lower limit of the allowable range of values or an array of allowable values
	 * @param null $upperLimit Upper limit of the allowable values
	 * @param \Throwable | null $previous
	 */
	public function __construct($parameter, $lowerLimit = null, $upperLimit = null, \Throwable $previous = null)
	{
		if (is_array($lowerLimit))
		{
			$message = "The value of an argument '{$parameter}' is outside the allowable range of values: " . implode(", ", $lowerLimit);
		}
		elseif (($lowerLimit !== null) && ($upperLimit !== null))
		{
			$message = "The value of an argument '{$parameter}' is outside the allowable range of values: from {$lowerLimit} to {$upperLimit}";
		}
		elseif (($lowerLimit === null) && ($upperLimit !== null))
		{
			$message = "The value of an argument '{$parameter}' is outside the allowable range of values: not greater than {$upperLimit}";
		}
		elseif (($lowerLimit !== null) && ($upperLimit === null))
		{
			$message = "The value of an argument '{$parameter}' is outside the allowable range of values: not less than {$lowerLimit}";
		}
		else
		{
			$message = "The value of an argument '{$parameter}' is outside the allowable range of values";
		}

		$this->lowerLimit = $lowerLimit;
		$this->upperLimit = $upperLimit;

		parent::__construct($message, $parameter, $previous);
	}

	public function getLowerLimitType()
	{
		return $this->lowerLimit;
	}

	public function getUpperType()
	{
		return $this->upperLimit;
	}
}
