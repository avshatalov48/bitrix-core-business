<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor;

final class Service
{
	public function preProcessEvent(Event $event): void
	{
		// TODO add new common space logic support (we need to delete activity from common space when needed)
		// Possible steps:
		// 1. Get all codes without group codes
		// 2. Get user diff between codes members
		// 3. Delete common space activity for deleted from non-group codes users
		// Part of the logic (about concrete user) can be moved to processors if event queue is implemented
		$processor = PreProcessor\Factory::getInstance()->createProcessor($event);
		$processor->processEvent();
	}

	public function processEvent(Event $event, Recepient $recipient): void
	{
		$processor = Processor\Factory::getInstance()->createProcessor($event, $recipient);
		$processor->processEvent();
	}
}
