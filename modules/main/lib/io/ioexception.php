<?php
namespace Bitrix\Main\IO;

/**
 * This exception is thrown when an I/O error occurs.
 */
class IoException extends \Bitrix\Main\SystemException
{
	protected $path;

	/**
	 * Creates new exception object.
	 *
	 * @param string $message Exception message
	 * @param string $path Path that generated exception.
	 * @param \Exception $previous
	 */
	public function __construct($message = "", $path = "", \Exception $previous = null)
	{
		parent::__construct($message, 120, '', 0, $previous);
		$this->path = $path;
	}

	/**
	 * Path that generated exception.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
}

class InvalidPathException extends IoException
{
	public function __construct($path, \Exception $previous = null)
	{
		$message = "Path is invalid.";
		parent::__construct($message, $path, $previous);
	}
}

class FileNotFoundException extends IoException
{
	public function __construct($path, \Exception $previous = null)
	{
		$message = "Path was not found.";
		parent::__construct($message, $path, $previous);
	}
}

class FileDeleteException extends IoException
{
	public function __construct($path, \Exception $previous = null)
	{
		$message = "Error occurred during deleting the file.";
		parent::__construct($message, $path, $previous);
	}
}

class FileOpenException extends IoException
{
	public function __construct($path, \Exception $previous = null)
	{
		$message = "Cannot open the file.";
		parent::__construct($message, $path, $previous);
	}
}

class FileNotOpenedException extends IoException
{
	public function __construct($path, \Exception $previous = null)
	{
		$message = "The file was not opened.";
		parent::__construct($message, $path, $previous);
	}
}
