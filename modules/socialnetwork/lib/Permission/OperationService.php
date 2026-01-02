<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission;

use Bitrix\Socialnetwork\Integration\Intranet\User;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use COption;
use CSocNetAllowed;
use CSocNetFeaturesPerms;

class OperationService
{
	public function filterUsersWithAccess(
		int    $groupId,
		array  $users,
		string $type,
		string $feature,
		string $operation,
		bool   $isAdmin = false
	): array
	{
		$result = [];
		foreach ($users as $user)
		{
			$userId = array_key_first($user);
			$role = $user[$userId];

			if ($this->canPerform($groupId, $userId, $role, $type, $feature, $operation, $isAdmin))
			{
				$result[] = $userId;
			}
		}

		return $result;
	}

	public function canPerform(
		int         $groupId,
		int         $userId,
		bool|string $role,
		string      $type,
		string      $feature,
		string      $operation,
		bool        $isAdmin = false
	): bool
	{
		$permissions = $this->getPermissions($groupId, $type, $feature, $operation);

		if ($permissions === false)
		{
			return false;
		}

		if ($role === SONET_ROLES_BAN)
		{
			return false;
		}

		$group = $this->getGroup($groupId);
		if (empty($group))
		{
			return false;
		}

		if ($this->isClosedGroup($group, $feature, $operation))
		{
			if (!$this->isWorkWithClosedGroupEnabled())
			{
				return false;
			}

			$permissions = SONET_ROLES_OWNER;
		}

		if ($isAdmin)
		{
			return true;
		}

		if ($permissions === SONET_ROLES_ALL)
		{
			if (!$this->isHiddenGroup($group))
			{
				return true;
			}

			$permissions = SONET_ROLES_USER;
		}

		if ($permissions === SONET_ROLES_AUTHORIZED)
		{
			return $userId > 0;
		}

		if ($permissions === SONET_ROLES_EMPLOYEE)
		{
			return $this->checkIsEmployee($userId, $role);
		}

		if ($role === false)
		{
			return false;
		}

		if ($permissions === SONET_ROLES_MODERATOR)
		{
			return $this->checkIsModerator($role);
		}

		if ($permissions === SONET_ROLES_USER)
		{
			return $this->checkIsMember($role);
		}

		if ($permissions === SONET_ROLES_OWNER)
		{
			return ($role === SONET_ROLES_OWNER);
		}

		return false;
	}

	private function getGroup(int $groupId): array
	{
		$group = WorkgroupTable::getList([
			                                 'select' => ['CLOSED', 'VISIBLE'],
			                                 'filter' => ['ID' => $groupId],
		                                 ])->fetch();

		if (!$group)
		{
			return [];
		}

		return $group;
	}

	private function isClosedGroup(array $group, string $feature, string $operation): bool
	{
		$featureSettings = CSocNetAllowed::GetAllowedFeatures();

		return $group["CLOSED"] === "Y"
			&& !in_array($operation, $featureSettings[$feature]["minoperation"], true);
	}

	private function isHiddenGroup(array $group): bool
	{
		return $group["VISIBLE"] === "N";
	}

	private function isWorkWithClosedGroupEnabled(): bool
	{
		return COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") === "Y";
	}

	private function checkIsEmployee(int $userId, bool|string $role): bool
	{
		return in_array($role, UserToGroupTable::getRolesMember(), true)
			&& User::isIntranet($userId);
	}

	private function checkIsModerator(string $role): bool
	{
		return in_array($role, [SONET_ROLES_MODERATOR, SONET_ROLES_OWNER], true);
	}

	private function checkIsMember(string $role): bool
	{
		return in_array($role, UserToGroupTable::getRolesMember(), true);
	}

	private function getPermissions(
		int    $groupId,
		string $type,
		string $feature,
		string $operation,
	): array|string|false
	{
		return CSocNetFeaturesPerms::GetOperationPerm($type, $groupId, $feature, $operation);
	}
}