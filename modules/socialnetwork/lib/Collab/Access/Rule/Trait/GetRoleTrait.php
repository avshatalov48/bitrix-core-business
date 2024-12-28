<?php

namespace Bitrix\SocialNetwork\Collab\Access\Rule\Trait;

use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;

trait GetRoleTrait
{
	protected static array $roles = [];

	protected function getUserRole(int $collabId, int $userId): string
	{
		if (!isset(static::$roles[$collabId]))
		{
			static::$roles[$collabId] = CollabRegistry::getInstance()->get($collabId)?->getMemberIdsWithRole();
		}

		return static::$roles[$collabId][$userId] ?? '';
	}
}