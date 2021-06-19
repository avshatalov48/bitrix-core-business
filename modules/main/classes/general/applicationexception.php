<?php

class CApplicationException
{
	var $msg, $id;

	public function __construct($msg, $id = false)
	{
		$this->msg = $msg;
		$this->id = $id;
	}

	public function GetString()
	{
		return $this->msg;
	}

	public function GetID()
	{
		return $this->id;
	}

	public function __toString()
	{
		return $this->GetString();
	}
}
