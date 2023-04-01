<?php

namespace Bitrix\Catalog\Access\Model;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Main;
use Bitrix\Catalog\Access\Permission\PermissionTable;
use Bitrix\Catalog\Access\Role\RoleRelationTable;

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

class UserModel extends Main\Access\User\UserModel
{
	private $permissions;

	/**
	 * get user roles in system
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
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

			$relationRows = RoleRelationTable::query()
				->addSelect('ROLE_ID')
				->whereIn('RELATION', $this->getAccessCodes())
				->exec()
				->fetchAll()
			;

			$this->roles = array_unique(
				array_column($relationRows, 'ROLE_ID')
			);
		}

		return $this->roles;
	}

	/**
	 * return permission if exists
	 *
	 * @param string $permissionId string identification
	 *
	 * @return int|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPermission(string $permissionId): ?int
	{
		$permissions = $this->getPermissions();

		$permissions[$permissionId] = $permissions[$permissionId] ?? null;

		if (is_array($permissions[$permissionId]))
		{
			$permissions[$permissionId] =
				isset($permissions[$permissionId][0])
					? (int)$permissions[$permissionId][0]
					: null
			;
		}

		return $permissions[$permissionId];
	}

	/**
	 * return multiple permission if exists
	 *
	 * @param string $permissionId string identification
	 *
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPermissionMulti(string $permissionId): ?array
	{
		if ($this->isAdmin())
		{
			return [PermissionDictionary::VALUE_VARIATION_ALL];
		}

		$permissions = $this->getPermissions();
		$permissions[$permissionId] = $permissions[$permissionId] ?? null;

		return is_array($permissions[$permissionId]) ? $permissions[$permissionId] : null;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getPermissions(): array
	{
		if ($this->permissions !== null)
		{
			return $this->permissions;
		}

		$this->permissions = [];
		$roles = $this->getRoles();

		if (empty($roles))
		{
			return $this->permissions;
		}

		$query = PermissionTable::query();

		$permissions = $query
			->addSelect("PERMISSION_ID")
			->addSelect("VALUE")
			->whereIn("ROLE_ID", $roles)
			->exec()
		;

		while ($permission = $permissions->fetch())
		{
			$permissionId = $permission["PERMISSION_ID"];
			$value = (int)$permission["VALUE"];

			$permissionDescription = PermissionDictionary::getPermission($permissionId);
			if ($permissionDescription['type'] === PermissionDictionary::TYPE_MULTIVARIABLES)
			{
				$this->permissions[$permissionId] = $this->permissions[$permissionId] ?? [];
				if (in_array(PermissionDictionary::VALUE_VARIATION_ALL, $this->permissions[$permissionId], true))
				{
					continue;
				}

				if ($value === PermissionDictionary::VALUE_VARIATION_ALL)
				{
					$this->permissions[$permissionId] = [$value];
				}
				elseif (!in_array($value, $this->permissions[$permissionId], true))
				{
					$this->permissions[$permissionId][] = $value;
				}
			}
			else
			{
				$this->permissions[$permissionId] = $this->permissions[$permissionId] ?? 0;
				if ($value > $this->permissions[$permissionId])
				{
					$this->permissions[$permissionId] = $value;
				}
			}
		}

		return $this->permissions;
	}

	/**
	 * @return array
	 */
	public function getRightGroups(): array
	{
		return \CUser::GetUserGroup($this->userId);
	}
}