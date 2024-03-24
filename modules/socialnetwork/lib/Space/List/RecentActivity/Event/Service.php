<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\Factory;
use Bitrix\Socialnetwork\Space\List\RecentActivity;

final class Service
{
	public function processEvent(Event $event, Recepient $recipient): void
	{
		$processor = (new Factory())->createProcessor($event, $recipient);
		$processor->processEvent();
	}
}
