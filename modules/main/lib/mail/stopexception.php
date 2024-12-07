<?php
namespace Bitrix\Main\Mail;

class StopException
	extends \Bitrix\Main\SystemException
{
	public function __construct($message = "", $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, '', '', $previous);
	}
}
