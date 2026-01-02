<?php

class CAdminException extends CApplicationException
{
	public $messages;

	/**
	 * @param array $messages array("id"=>"", "text"=>""), array(...), ...
	 * @param $id
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function __construct($messages, $id = false)
	{
		$this->messages = $messages;

		$s = '';
		foreach ($this->messages as $msg)
		{
			if (!isset($msg["text"]))
			{
				throw new \Bitrix\Main\ArgumentException('Incorrect message structure: ' . print_r($msg, true));
			}
			$s .= $msg["text"] . "<br>";
		}

		parent::__construct($s, $id);
	}

	public function GetMessages()
	{
		return $this->messages;
	}

	public function AddMessage($message)
	{
		$this->messages[] = $message;
		$this->msg .= $message["text"] . "<br>";
	}
}
