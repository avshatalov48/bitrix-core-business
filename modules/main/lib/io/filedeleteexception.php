<?php

namespace Bitrix\Main\IO;

class FileDeleteException extends IoException
{
	public function __construct($path, \Throwable $previous = null)
	{
		$message = "Error occurred during deleting the file.";
		parent::__construct($message, $path, $previous);
	}
}
