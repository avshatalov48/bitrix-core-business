<?php

class CAdminException extends CApplicationException
{
	var $messages;

	public function __construct($messages, $id = false)
	{
		//array("id"=>"", "text"=>""), array(...), ...
		$this->messages = $messages;
		$s = "";
		foreach($this->messages as $msg)
			$s .= $msg["text"]."<br>";
		parent::__construct($s, $id);
	}

	public function GetMessages()
	{
		return $this->messages;
	}

	public function AddMessage($message)
	{
		$this->messages[]=$message;
		$this->msg.=$message["text"]."<br>";
	}
}
