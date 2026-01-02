<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup\Dto;

class UserGroupDto
{
	public function __construct(
		public readonly int $groupId,
		public readonly int $userId
	) {}
}