<?php

namespace Bitrix\Main\IO;

class FileNotFoundException extends IoException
{
	public function __construct($path, \Throwable $previous = null)
	{
		$message = "Path was not found.";
		parent::__construct($message, $path, $previous);
	}
}
