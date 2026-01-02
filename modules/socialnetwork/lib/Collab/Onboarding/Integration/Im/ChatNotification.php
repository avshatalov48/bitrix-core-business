<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Integration\IM\Message\MessageTrait;
use Bitrix\Socialnetwork\Collab\Onboarding\Notification\NotificationInterface;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class ChatNotification implements NotificationInterface
{
	use InstanceTrait;
	use MessageTrait;

	private function __construct()
	{
	}

	public function send(string $message, int $senderId, int $collabId): void
	{
		if (!Loader::includeModule('im'))
		{
			return;
		}

		$this->sendMessage($message, $senderId, $collabId);
	}
}
