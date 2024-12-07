<?php
namespace Bitrix\Main\Security;

class SecurityException extends \Bitrix\Main\SystemException
{
	public function __construct($message = "", $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, '', '', $previous);
	}
}

class OtpException extends SecurityException
{
}
