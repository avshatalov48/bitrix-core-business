<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Notification;

use Bitrix\Main\Result;

class NotificationService
{
	/**
	 * @param NotificationInterface[] $notifications
	 */
	public function __construct(private readonly array $notifications)
	{
	}

	public function send(string $message, int $senderId, int $collabId): void
	{
		foreach ($this->notifications as $notification)
		{
			$notification->send($message, $senderId, $collabId);
		}
	}
}
