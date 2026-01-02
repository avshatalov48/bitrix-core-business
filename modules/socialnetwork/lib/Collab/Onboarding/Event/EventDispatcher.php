<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Collab\Onboarding\Event\Type\UserDeleteEventListener;
use Bitrix\Socialnetwork\Collab\Onboarding\Event\Type\UserUpdateEventListener;

class EventDispatcher
{
	public static function onAfterUserFired(array $data): EventResult
	{
		return UserUpdateEventListener::getInstance()->onAfterUserFired($data);
	}

	public static function onAfterUserDelete(int $userId): EventResult
	{
		return UserDeleteEventListener::getInstance()->onAfterUserDelete($userId);
	}
}
