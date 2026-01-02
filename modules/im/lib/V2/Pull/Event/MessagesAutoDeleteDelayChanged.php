<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\EventType;

class MessagesAutoDeleteDelayChanged extends BaseChatEvent
{
	use ContextCustomer;
	use DialogIdFiller;

	protected function getBasePullParamsInternal(): array
	{
		return [
			'chatId' => $this->chat->getId(),
			'dialogId' => $this->getBaseDialogId(),
			'delay' => $this->chat->getMessagesAutoDeleteDelay() ?? 0,
		];
	}

	protected function getType(): EventType
	{
		return EventType::MessagesAutoDeleteDelayChanged;
	}
}
