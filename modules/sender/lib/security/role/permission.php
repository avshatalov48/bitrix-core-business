<?php

namespace Bitrix\Sender\Security\Role;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Security\User;

use Bitrix\Sender\Internals\Model;

Loc::loadMessages(__FILE__);

class Permission
{
	const ENTITY_AD = 'AD';
	const ENTITY_RC = 'RC';
	const ENTITY_LETTER = 'LETTER';
	const ENTITY_SEGMENT = 'SEGMENT';
	const ENTITY_BLACKLIST = 'BLACKLIST';
	const ENTITY_SETTINGS = 'SETTINGS';

	const ACTION_VIEW = 'VIEW';
	const ACTION_MODIFY = 'MODIFY';

	const PERMISSION_NONE = '';
	const PERMISSION_SELF = 'A';
	const PERMISSION_DEPARTMENT = 'D';
	const PERMISSION_ANY = 'X';

	/**
	 * Returns permission code according to the user's Permission.
	 *
	 * @param string $entityCode Code of the entity.
	 * @param string $actionCode Code of the action.
	 * @return string
	 * @throws ArgumentException
	 * @deprecated
	 */
	/*
	public function getPermission($entityCode, $actionCode)
	{

		$permissionMap = $this->getMap();
		if(!isset($permissionMap[$entityCode][$actionCode]))
			throw new ArgumentException('Unknown entity or action code');

		return (isset($this->Permission[$entityCode][$actionCode]) ? $this->Permission[$entityCode][$actionCode] : self::PERMISSION_NONE);

	}
	*/

	/**
	 * Returns true if user can perform specified action on the entity.
	 *
	 * @param array $permissions Permissions.
	 * @param string $entityCode Code of the entity.
	 * @param string $actionCode Code of the action.
	 * @param string $minPerm Code of minimal permission.
	 * @return bool
	 * @throws ArgumentException
	 */
	public static function check(array $permissions, $entityCode, $actionCode, $minPerm = null)
	{
		$map = self::getMap();
		if(!isset($map[$entityCode][$actionCode]))
		{
			throw new ArgumentException('Unknown entity or action code.');
		}

		if (!isset($permissions[$entityCode][$actionCode]))
		{
			return false;
		}

		$perm = $permissions[$entityCode][$actionCode];
		$minPerm = $minPerm ?: self::PERMISSION_NONE;


		if ($minPerm === self::PERMISSION_NONE)
		{
			return $perm > $minPerm;
		}
		else
		{
			return $perm >= $minPerm;
		}
	}

	/**
	 * Get permissions by user ID.
	 *
	 * @param int $userId User ID.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getByUserId($userId)
	{
		$user = User::get($userId);
		if($user->isPortalAdmin() || $user->isAdmin())
		{
			return self::getAdminPermissions();
		}

		//everybody else's permissions are defined by their role
		$result = [];
		$userAccessCodes = \CAccess::getUserCodesArray($user->getId());

		if(!is_array($userAccessCodes) || count($userAccessCodes) === 0)
		{
			return [];
		}

		$list = Model\Role\PermissionTable::getList(array(
			'filter' => array(
				'=ROLE_ACCESS.ACCESS_CODE' => $userAccessCodes
			)
		));

		foreach ($list as $row)
		{
			if (   !isset($result[$row['ENTITY']][$row['ACTION']])
				|| $result[$row['ENTITY']][$row['ACTION']] < $row['PERMISSION'])
			{
				$result[$row['ENTITY']][$row['ACTION']] = $row['PERMISSION'];
			}
		}

		return $result;
	}

	/**
	 * Returns Permission map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			self::ENTITY_LETTER => [
				self::ACTION_VIEW => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
				self::ACTION_MODIFY => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
			],
			self::ENTITY_AD => [
				self::ACTION_VIEW => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
				self::ACTION_MODIFY => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
			],
			self::ENTITY_RC => [
				self::ACTION_VIEW => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
				self::ACTION_MODIFY => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
			],
			self::ENTITY_SEGMENT => [
				self::ACTION_VIEW => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
				self::ACTION_MODIFY => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
			],
			self::ENTITY_BLACKLIST => [
				self::ACTION_VIEW => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				],
				self::ACTION_MODIFY => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				]
			],
			self::ENTITY_SETTINGS => [
				self::ACTION_MODIFY => [
					self::PERMISSION_NONE,
					self::PERMISSION_ANY
				]
			],
		];
	}

	/**
	 * Returns normalized permission array.
	 *
	 * @param array $source Some not normalized permission array.
	 * @return array
	 */
	public static function normalize(array $source)
	{
		$map = self::getMap();
		$result = [];

		foreach ($map as $entity => $actions)
		{
			foreach ($actions as $action => $permission)
			{
				if(isset($source[$entity][$action]))
				{
					$result[$entity][$action] = $source[$entity][$action];
				}
				else
				{
					$result[$entity][$action] = self::PERMISSION_NONE;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns name of the entity by its code.
	 *
	 * @param string $entity Entity code.
	 * @return string
	 */
	public static function getEntityName($entity)
	{
		return Loc::getMessage('SENDER_SECURITY_ROLE_ENTITY_'.$entity);
	}

	/**
	 * Returns name of the action by its code.
	 *
	 * @param string $action Action code.
	 * @return string
	 */
	public static function getActionName($action)
	{
		return Loc::getMessage('SENDER_SECURITY_ROLE_ACTION_'.$action);
	}

	/**
	 * Returns name of the permission by its code.
	 *
	 * @param string $permission Permission code.
	 * @return string
	 */
	public static function getPermissionName($permission)
	{
		switch ($permission)
		{
			case self::PERMISSION_NONE:
				$result = Loc::getMessage('SENDER_SECURITY_ROLE_PERMISSION_NONE');
				break;
			case self::PERMISSION_SELF:
				$result = Loc::getMessage('SENDER_SECURITY_ROLE_PERMISSION_SELF');
				break;
			case self::PERMISSION_DEPARTMENT:
				$result = Loc::getMessage('SENDER_SECURITY_ROLE_PERMISSION_DEPARTMENT');
				break;
			case self::PERMISSION_ANY:
				$result = Loc::getMessage('SENDER_SECURITY_ROLE_PERMISSION_ANY');
				break;
			default:
				$result = '';
				break;
		}
		return $result;
	}

	/**
	 * Returns maximum available permissions.
	 *
	 * @return array
	 */
	protected static function getAdminPermissions()
	{
		$result = array();
		$permissionMap = self::getMap();

		foreach ($permissionMap as $entity => $actions)
		{
			foreach ($actions as $action => $permissions)
			{
				foreach ($permissions as $permission)
				{
					if(!isset($result[$entity][$action]) || $result[$entity][$action] < $permission)
					{
						$result[$entity][$action] = $permission;
					}
				}
			}
		}

		return $result;
	}
}