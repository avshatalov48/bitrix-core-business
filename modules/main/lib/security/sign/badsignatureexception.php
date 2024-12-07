<?php
namespace Bitrix\Main\Security\Sign;

use Bitrix\Main\SystemException;

/**
 * Class BadSignatureException
 * @since 14.0.7
 * @package Bitrix\Main\Security\Sign
 */
class BadSignatureException
	extends SystemException
{
	/**
	 * Creates new exception object for signing purposes.
	 *
	 * @param string $message Message.
	 * @param \Exception | null $previous Previous exception.
	 */
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 140, '', 0, $previous);
	}
}