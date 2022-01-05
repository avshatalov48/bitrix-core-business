<?php

namespace Bitrix\Main\UserField\Access\Model;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\UserField\Access\Permission\UserFieldPermissionTable;
use Bitrix\Main\UserField\Access\UserAccessibleInterface;

class UserFieldModel implements \Bitrix\Main\Access\AccessibleItem
{
	private static $permissions;
	private $id = 0;

	public static function createNew(): self
	{
		return new self();
	}

	public static function createFromId(int $userFieldId): AccessibleItem
	{
		$model = new self();
		$model->setId($userFieldId);
		return $model;
	}

	private function __construct()
	{
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @param UserAccessibleInterface $user
	 * @param $permissionId
	 * @return int
	 */
	public function getPermission(UserAccessibleInterface $user, $permissionId): int
	{
		$permissions = $this->loadPermissions();

		$value = 0;
		foreach ($user->getAccessCodes() as $ac)
		{
			if (!array_key_exists($ac, $permissions))
			{
				continue;
			}
			$value = ($permissions[$ac][$permissionId] > $value) ? $permissions[$ac][$permissionId] : $value;
		}

		return $value;
	}

	/**
	 * @param UserAccessibleInterface $user
	 * @param int $permissionId
	 * @return array
	 */
	public function getPermissions(UserAccessibleInterface $user, int $permissionId): array
	{
		$permissions = $this->loadPermissions();

		$values = [];
		foreach ($user->getAccessCodes() as $ac)
		{
			foreach ($permissions as $userFieldId => $userFieldPermission)
			{
				if (!isset($values[$userFieldId]))
				{
					$values[$userFieldId] = 0;
				}

				/**
				 * if there are no access codes for the specified field action for a particular field,
				 * then set the field to allowed
				 */
				if(!isset($userFieldPermission[$permissionId]) && !isset($userFieldPermission[$permissionId][$ac]))
				{
					$userFieldPermission[$permissionId][$ac] = true;
				}
				// if the field has other codes but no specific user codes, then the field is not allowed
				else if(!isset($userFieldPermission[$permissionId][$ac]))
				{
					$userFieldPermission[$permissionId][$ac] = false;
				}

				$values[$userFieldId] = (
				($userFieldPermission[$permissionId][$ac] > $values[$userFieldId])
					? $userFieldPermission[$permissionId][$ac]
					: $values[$userFieldId]
				);

			}
		}

		return $values;
	}

	/**
	 * @return array
	 */
	public function loadPermissions(): array
	{
		if (static::$permissions === null)
		{
			static::$permissions = [];

			$res = UserFieldPermissionTable::query()
				->addSelect('*')
				//->where('ENTITY_TYPE_ID', $entityTypeId)
				->exec()
				->fetchAll();

			foreach ($res as $row)
			{
				static::$permissions[$row['USER_FIELD_ID']][$row['PERMISSION_ID']][$row['ACCESS_CODE']] = (int) $row['VALUE'];
			}

		}
		return static::$permissions;
	}
}
