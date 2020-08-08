<?php

namespace Bitrix\Main\UserField\Access;

use Bitrix\Main\Access\User\AccessibleUser;

interface UserAccessibleInterface
	extends AccessibleUser
{
	public function getPermission(string $permissionId, int $groupId = 0): ?int;
}