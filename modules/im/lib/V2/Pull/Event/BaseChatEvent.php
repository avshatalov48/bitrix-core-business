<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Pull\BaseEvent;

abstract class BaseChatEvent extends BaseEvent
{
	protected Chat $chat;

	public function __construct(Chat $chat)
	{
		parent::__construct();
		$this->chat = $chat;
	}

	public function getTarget(): Chat
	{
		return $this->chat;
	}

	protected function getRecipients(): array
	{
		return $this->chat->getRelations()->filterActive()->getUserIds();
	}

	protected function getBaseDialogId(): string
	{
		return 'chat' . $this->chat->getId();
	}
}
