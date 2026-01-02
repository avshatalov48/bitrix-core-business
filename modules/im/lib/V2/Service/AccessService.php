<?php

namespace Bitrix\Im\V2\Service;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Result;

class AccessService
{
	public function checkUserDialogAccess(int $targetUserId, int $userId): Result
	{
		return User::getInstance($targetUserId)->checkAccess($userId);
	}

	public function checkChatAccess(int $chatId, int $userId): Result
	{
		return Chat::getInstance($chatId)->checkAccess($userId);
	}
}