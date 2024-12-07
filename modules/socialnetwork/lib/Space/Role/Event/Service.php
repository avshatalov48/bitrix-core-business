<?php

namespace Bitrix\Socialnetwork\Space\Role\Event;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;

final class Service
{
	public const SUPPORTED_EVENTS = [
		EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE,
	];

	public function processEvent(Event $event, Recepient $recipient): void
	{
		if (!ModuleManager::isModuleInstalled('pull') || !Loader::includeModule('pull'))
		{
			return;
		}

		if (in_array($event->getType(), self::SUPPORTED_EVENTS) && $recipient->isOnline())
		{
			$recipients = [$recipient->getId()];

			$userId = $event->getUserId();
			$spaceId = $event->getGroupId();

			PushService::addEvent($recipients, [
				'module_id' => PushService::MODULE_NAME,
				'command' => PushEventDictionary::getPushEventType($event->getType()),
				'params' => [
					'USER_ID' => $userId,
					'GROUP_ID' => $spaceId,
				],
			]);
		}
	}
}