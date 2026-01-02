<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Notification;

interface NotificationInterface
{
	public function send(string $message, int $senderId, int $collabId): void;
}
