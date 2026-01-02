<?php

namespace Bitrix\Main\IO;

class FileOpenException extends IoException
{
	public function __construct($path, \Throwable $previous = null)
	{
		$message = "Cannot open the file.";
		parent::__construct($message, $path, $previous);
	}
}
