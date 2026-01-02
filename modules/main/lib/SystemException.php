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
