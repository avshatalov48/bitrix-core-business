<?php
namespace Bitrix\Im;

class BasicError
{
	public $method = '';
	public $code = '';
	public $msg = '';
	public $params = Array();
	public $error = false;

	public function __construct($method, $code, $msg, $params = Array())
	{
		if ($method != null)
		{
			$this->method = $method;
			$this->code = $code;

			if(is_array($msg))
				$this->msg = implode("; ", $msg);
			else
				$this->msg = $msg;

			$this->params = $params;

			$this->error = true;
		}
	}
}