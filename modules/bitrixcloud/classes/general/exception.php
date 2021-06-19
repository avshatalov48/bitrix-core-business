<?php

class CBitrixCloudException extends Exception
{
	protected $error_code = '';
	protected $debug_info = '';

	public function __construct($message = '', $error_code = '', $debug_info = '')
	{
		parent::__construct($message);
		$this->error_code = $error_code;
		$this->debug_info = $debug_info;
	}

	final public function getErrorCode()
	{
		return $this->error_code;
	}

	final public function getDebugInfo()
	{
		return $this->debug_info;
	}
}
