<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\Space\Counter;

class CounterController
{
	private int $userId;

	public function __construct(int $userId = 0)
	{
		$this->userId = $userId;
	}

	public function process(Event $event): void
	{
		if (!in_array($event->getType(), Dictionary::SUPPORTED_EVENTS, true))
		{
			return;
		}

		Counter::getInstance($this->userId)->updateLeftMenuCounter();

		(new PushSender())->createPush(
			[$this->userId],
			PushSender::COMMAND_USER_SPACES,
			Counter::getInstance($this->userId)->getMemberSpaceCounters(),
		);
	}
}