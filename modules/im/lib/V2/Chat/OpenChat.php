<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Entity\User\User;

class OpenChat extends GroupChat
{
	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN;
	}

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		return !User::getInstance($userId)->isExtranet();
	}
}
