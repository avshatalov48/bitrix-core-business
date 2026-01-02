<?php

namespace Bitrix\Main;

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
