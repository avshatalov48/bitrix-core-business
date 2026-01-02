<?php

namespace Bitrix\Main;

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
