<?php

namespace Bitrix\Main\UserField\Access\Model;

use Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable;
use Bitrix\Main\UserField\Access\Role\UserFieldRoleRelationTable;

class UserModel extends \Bitrix\Main\Access\User\UserModel
	implements \Bitrix\Main\UserField\Access\UserAccessibleInterface
{
	private
		$permissions;

	public function getRoles(): array
	{
		if ($this->roles === null)
		{
			$this->roles = [];
			if ($this->userId === 0 || empty($this->getAccessCodes()))
			{
				return $this->roles;
			}

			$res = UserFieldRoleRelationTable::query()
				->addSelect('ROLE_ID')
				->whereIn('RELATION', $this->getAccessCodes())
				->exec();
			foreach ($res as $row)
			{
				$this->roles[] = (int) $row['ROLE_ID'];
			}
		}
		return $this->roles;
	}

	public function getPermission(string $permissionId, int $userFieldId = 0): ?int
	{
		$permissions = $this->getPermissions($userFieldId);
		if (array_key_exists($permissionId, $permissions))
		{
			return $permissions[$permissionId];
		}
		return null;
	}

	private function getPermissions(int $userFieldId = 0): array
	{
		if (!$this->permissions || !array_key_exists($userFieldId, $this->permissions))
		{
			$this->permissions[$userFieldId] = [];

			$res = UserFieldPermissionTable::query()
				->addSelect("PERMISSION_ID")
				->addSelect("USER_FIELD_ID")
				->addSelect("VALUE")
				->where("USER_FIELD_ID", $userFieldId)
				->where("ROLE_ID", $this->userId)
				->exec()
				->fetchAll();

			foreach ($res as $row)
			{
				$permissionId = $row["PERMISSION_ID"];
				$value = (int) $row["VALUE"];
				if (!array_key_exists($permissionId, $this->permissions[$userFieldId]))
				{
					$this->permissions[$userFieldId][$permissionId] = 0;
				}
				if ($value > $this->permissions[$userFieldId][$permissionId])
				{
					$this->permissions[$userFieldId][$permissionId] = $value;
				}
			}
		}
		return $this->permissions[$userFieldId];
	}
}