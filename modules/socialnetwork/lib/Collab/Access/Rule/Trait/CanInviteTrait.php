<?php

namespace Bitrix\SocialNetwork\Collab\Access\Rule\Trait;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Socialnetwork\Permission\User\UserModel;
use Bitrix\SocialNetwork\Collab\Access\Model\CollabModel;
use Bitrix\Socialnetwork\Helper\Workgroup;

trait CanInviteTrait
{
	protected static array $commonGroups = [];

	protected function canInvite(UserModel $user, CollabModel $item): bool
	{
		if ($user->isIntranet())
		{
			return true;
		}

		$addMembers = $item->getAddMembers();
		foreach ($addMembers as $accessCode)
		{
			$userId = (new AccessCode($accessCode))->getEntityId();
			if (!$this->isUsersHaveCommonGroups($user->getUserId(), $userId))
			{
				return false;
			}
		}

		return true;
	}

	protected function isUsersHaveCommonGroups(int $userId, int $targetUserId): bool
	{
		if ($userId <= $targetUserId)
		{
			$key = "{$userId}_{$targetUserId}";
		}
		else
		{
			$key = "{$targetUserId}_{$userId}";
		}

		if (!isset(static::$commonGroups[$key]))
		{
			static::$commonGroups[$key] = Workgroup::isUsersHaveCommonGroups($userId, $targetUserId, true);
		}

		return static::$commonGroups[$key];
	}
}