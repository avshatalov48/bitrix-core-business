<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Push;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;

final class SpaceListSender
{
	public const EVENT_MAP = [
		EventDictionary::EVENT_WORKGROUP_USER_ADD => EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE,
		EventDictionary::EVENT_WORKGROUP_USER_UPDATE => EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE,
		EventDictionary::EVENT_WORKGROUP_USER_DELETE => EventDictionary::EVENT_SPACE_USER_ROLE_CHANGE,
	];

	public function send(Event $event): void
	{
		if (!ModuleManager::isModuleInstalled('pull') || !Loader::includeModule('pull'))
		{
			return;
		}


		if (key_exists($event->getType(), self::EVENT_MAP))
		{
			$recipients = [$event->getUserId()];

			$userId = $event->getUserId();
			$spaceId = $event->getGroupId();

			PushService::addEvent($recipients, [
				'module_id' => PushService::MODULE_NAME,
				'command' => PushEventDictionary::getPushEventType(self::EVENT_MAP[$event->getType()]),
				'params' => [
					'USER_ID' => $userId,
					'GROUP_ID' => $spaceId,
				],
			]);
		}
	}
}