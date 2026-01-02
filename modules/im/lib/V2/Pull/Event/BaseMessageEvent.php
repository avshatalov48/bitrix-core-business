<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Message;

abstract class BaseMessageEvent extends BaseChatEvent
{
	protected Message $message;

	public function __construct(Message $message)
	{
		parent::__construct($message->getChat());
		$this->message = $message;
	}
}
