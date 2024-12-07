<?php

namespace Bitrix\Main;

/**
 * Base class for fatal exceptions
 */
class SystemException extends \Exception
{
	/**
	 * Creates new exception object.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @param \Throwable | null $previous
	 */
	public function __construct($message = "", $code = 0, $file = "", $line = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		if (!empty($file) && !empty($line))
		{
			$this->file = $file;
			$this->line = $line;
		}
	}
}

/**
 * Exception is thrown when function argument is not valid.
 */
class ArgumentException extends SystemException
{
	protected $parameter;

	public function __construct($message = "", $parameter = "", \Throwable $previous = null)
	{
		parent::__construct($message, 100, '', 0, $previous);
		$this->parameter = $parameter;
	}

	public function getParameter()
	{
		return $this->parameter;
	}
}

/**
 * Exception is thrown when "empty" value is passed to a function that does not accept it as a valid argument.
 */
class ArgumentNullException extends ArgumentException
{
	public function __construct($parameter, \Throwable $previous = null)
	{
		$message = "Argument '{$parameter}' is null or empty";
		parent::__construct($message, $parameter, $previous);
	}
}

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

/**
 * Exception is thrown when the type of argument is not accepted by function.
 */
class ArgumentTypeException extends ArgumentException
{
	protected $requiredType;

	/**
	 * Creates new exception object
	 *
	 * @param string $parameter Argument that generates exception
	 * @param string $requiredType Required type
	 * @param \Exception | null $previous
	 */
	public function __construct($parameter, $requiredType = "", \Throwable $previous = null)
	{
		if (!empty($requiredType))
		{
			$message = "The value of an argument '{$parameter}' must be of type {$requiredType}";
		}
		else
		{
			$message = "The value of an argument '{$parameter}' has an invalid type";
		}

		$this->requiredType = $requiredType;

		parent::__construct($message, $parameter, $previous);
	}

	public function getRequiredType()
	{
		return $this->requiredType;
	}
}

/**
 * Exception is thrown when operation is not implemented but should be.
 */
class NotImplementedException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 140, '', 0, $previous);
	}
}

/**
 * Exception is thrown when operation is not supported.
 */
class NotSupportedException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 150, '', 0, $previous);
	}
}

/**
 * Exception is thrown when a method call is invalid for current state of object.
 */
class InvalidOperationException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 160, '', 0, $previous);
	}
}

/**
 * Exception is thrown when object property is not valid.
 */
class ObjectPropertyException extends ArgumentException
{
	public function __construct($parameter = "", \Throwable $previous = null)
	{
		parent::__construct("Object property \"{$parameter}\" not found.", $parameter, $previous);
	}
}

/**
 * Exception is thrown when the object can't be constructed.
 */
class ObjectException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 500, '', 0, $previous);
	}
}

/**
 * Exception is thrown when an object is not present.
 */
class ObjectNotFoundException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 510, '', 0, $previous);
	}
}

/**
 * Exception is thrown when access is denied
 */
class AccessDeniedException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct(($message ?: 'Access denied.'), 403, '', 0, $previous);
	}
}

class DecodingException extends SystemException
{
}
