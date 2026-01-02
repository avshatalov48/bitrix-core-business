<?php

namespace Bitrix\Main\IO;

class InvalidPathException extends IoException
{
	public function __construct($path, \Throwable $previous = null)
	{
		$message = "Path is invalid.";
		parent::__construct($message, $path, $previous);
	}
}
