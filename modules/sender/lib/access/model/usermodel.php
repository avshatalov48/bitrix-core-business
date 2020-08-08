<?php

namespace Bitrix\Sender\Access\Model;

use Bitrix\Sender\Access\Permission\PermissionTable;
use Bitrix\Sender\Access\Role\RoleRelationTable;

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2021 Bitrix
 */

class UserModel extends \Bitrix\Main\Access\User\UserModel
{
	private $permissions;

	/**
	 * get user roles in system
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getRoles(): array
	{
		if ($this->roles === null)
		{
			$this->roles = [];
			if ($this->userId === 0 || empty($this->getAccessCodes()))
			{
				return $this->roles;
			}

			$res = RoleRelationTable::query()
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

	/**
	 * return permission if exists
	 *
	 * @param string $permissionId string identification
	 *
	 * @return int|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getPermission(string $permissionId): ?int
	{
		$permissions = $this->getPermissions();
		if (array_key_exists($permissionId, $permissions))
		{
			return $permissions[$permissionId];
		}
		return null;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getPermissions(): array
	{
		if (!$this->permissions)
		{
			$this->permissions = [];
			$roles = $this->getRoles();

			if (empty($roles))
			{
				return $this->permissions;
			}

			$query = PermissionTable::query();

			$res = $query
				->addSelect("PERMISSION_ID")
				->addSelect("VALUE")
				->whereIn("ROLE_ID", $roles)
				->exec()
				->fetchAll();

			foreach ($res as $row)
			{
				$permissionId = $row["PERMISSION_ID"];
				$value = (int) $row["VALUE"];
				if (!array_key_exists($permissionId, $this->permissions))
				{
					$this->permissions[$permissionId] = 0;
				}
				if ($value > $this->permissions[$permissionId])
				{
					$this->permissions[$permissionId] = $value;
				}
			}
		}
		return $this->permissions;
	}
}