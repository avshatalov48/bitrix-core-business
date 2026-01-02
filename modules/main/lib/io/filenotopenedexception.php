<?php

namespace Bitrix\Main\IO;

class FileNotOpenedException extends IoException
{
	public function __construct($path, \Throwable $previous = null)
	{
		$message = "The file was not opened.";
		parent::__construct($message, $path, $previous);
	}
}
