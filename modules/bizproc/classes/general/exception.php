<?php

class CBPArgumentException extends Exception
{
	private $paramName = "";

	public function __construct($message, $paramName = "")
	{
		parent::__construct($message, 10001);
		$this->paramName = $paramName;
	}

	public function getParamName()
	{
		return $this->paramName;
	}
}

class CBPArgumentNullException
	extends CBPArgumentException
{
	public function __construct($paramName, $message = "")
	{
		if ($message == '')
			$message = str_replace("#PARAM#", htmlspecialcharsbx($paramName), GetMessage("BPCGERR_NULL_ARG"));

		parent::__construct($message, $paramName);

		$this->code = "10002";
	}
}

class CBPArgumentOutOfRangeException
	extends CBPArgumentException
{
	private $actualValue = null;

	public function __construct($paramName, $actualValue = null, $message = "")
	{
		if ($message == '')
		{
			if ($actualValue === null)
				$message = str_replace("#PARAM#", htmlspecialcharsbx($paramName), GetMessage("BPCGERR_INVALID_ARG"));
			else
				$message = str_replace(array("#PARAM#", "#VALUE#"), array(htmlspecialcharsbx($paramName), htmlspecialcharsbx($actualValue)), GetMessage("BPCGERR_INVALID_ARG1"));
		}

		parent::__construct($message, $paramName);

		$this->code = "10003";
		$this->actualValue = $actualValue;
	}

	public function getActualValue()
	{
		return $this->actualValue;
	}
}

class CBPArgumentTypeException
	extends CBPArgumentException
{
	private $correctType = null;

	public function __construct($paramName, $correctType = null, $message = "")
	{
		if ($message == '')
		{
			if ($correctType === null)
				$message = str_replace("#PARAM#", htmlspecialcharsbx($paramName), GetMessage("BPCGERR_INVALID_TYPE"));
			else
				$message = str_replace(array("#PARAM#", "#VALUE#"), array(htmlspecialcharsbx($paramName), htmlspecialcharsbx($correctType)), GetMessage("BPCGERR_INVALID_TYPE1"));
		}

		parent::__construct($message, $paramName);

		$this->code = "10005";
		$this->correctType = $correctType;
	}

	public function getCorrectType()
	{
		return $this->correctType;
	}
}

class CBPInvalidOperationException
	extends Exception
{
	public function __construct($message = "")
	{
		parent::__construct($message, 10006);
	}
}

class CBPNotSupportedException
	extends Exception
{
	public function __construct($message = "")
	{
		parent::__construct($message, 10004);
	}
}
