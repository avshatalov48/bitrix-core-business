<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Sms;

use Bitrix\Main\Config;

class Message
{
	protected $sender;
	protected $receiver;
	protected $text;
	protected $template;

	public function __construct()
	{
	}

	public static function createFromTemplate(Template $template, array $fields)
	{
		$message = new static();
		$message->template = $template;

		if(!isset($fields["SITE_NAME"]))
		{
			$fields["SITE_NAME"] = $template->getSites()->getAll()[0]->getSiteName();
			if($fields["SITE_NAME"] == '')
			{
				$fields["SITE_NAME"] = Config\Option::get("main", "site_name");
			}
		}
		if(!isset($fields["SERVER_NAME"]))
		{
			$fields["SERVER_NAME"] = $template->getSites()->getAll()[0]->getServerName();
			if($fields["SERVER_NAME"] == '')
			{
				$fields["SERVER_NAME"] = Config\Option::get("main", "server_name");
			}
		}
		if(!isset($fields["DEFAULT_SENDER"]))
		{
			$fields["DEFAULT_SENDER"] = Config\Option::get("main", "sms_default_sender");
		}

		$message
			->setSender(static::compile($template->getSender(), $fields))
			->setReceiver(static::compile($template->getReceiver(), $fields))
			->setText(static::compile($template->getMessage(), $fields));

		return $message;
	}

	protected static function compile($string, array $fields)
	{
		foreach($fields as $field => $value)
		{
			$string = str_replace("#".$field."#", $value, $string);
		}
		return $string;
	}

	public function getSender()
	{
		return $this->sender;
	}

	public function getReceiver()
	{
		return $this->receiver;
	}

	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $sender
	 * @return $this
	 */
	public function setSender($sender)
	{
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @param string $receiver
	 * @return $this
	 */
	public function setReceiver($receiver)
	{
		$this->receiver = $receiver;
		return $this;
	}

	/**
	 * @param string $text
	 * @return $this
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * @return ?Template
	 */
	public function getTemplate(): ?Template
	{
		return $this->template;
	}
}