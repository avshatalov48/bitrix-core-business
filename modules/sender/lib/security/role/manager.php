<?php

namespace Bitrix\Sender\Security\Role;

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\Role\RoleTable;
use Bitrix\Sender\Internals\Model;

use Bitrix\Bitrix24\Feature;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 *
 * @package Bitrix\Sender\Security\Role
 */
class Manager
{
	/** @var array $userRoles User roles. */
	protected static $userRoles = []; // 'USER_ID' => 'ROLE_ID'

	/**
	 * Clean menu cache.
	 *
	 * @return void
	 */
	public static function clearMenuCache()
	{
		Application::getInstance()->getTaggedCache()->clearByTag('bitrix:menu');
	}

	/**
	 * Return true if can use role access.
	 *
	 * @return bool
	 */
	public static function canUse()
	{
		if(!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return Feature::isFeatureEnabled('sender_security');
	}

	/**
	 * Return true if can use role access.
	 *
	 * @return bool
	 */
	public static function getTrialText()
	{
		return Loc::getMessage('SENDER_SECURITY_ROLE_MANAGER_TRIAL_TEXT_NEW');
	}

	/**
	 * Get role list.
	 *
	 * @param array $parameters Parameters.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getRoleList(array $parameters = [])
	{
		return RoleTable::getList($parameters);
	}

	/**
	 * Get access list.
	 *
	 * @param array $parameters Parameters.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getAccessList(array $parameters = [])
	{
		return Model\Role\AccessTable::getList($parameters);
	}

	/**
	 * Set access codes.
	 *
	 * @param array $list List.
	 * @return AddResult
	 */
	public static function setAccessCodes(array $list = [])
	{
		self::clearMenuCache();
		Model\Role\AccessTable::truncate();
		foreach ($list as $item)
		{
			$result = Model\Role\AccessTable::add(array(
				'ROLE_ID' => $item['ROLE_ID'],
				'ACCESS_CODE' => $item['ACCESS_CODE']
			));
			if(!$result->isSuccess())
			{
				return $result;
			}
		}

		return new AddResult();
	}

	/**
	 * Get roles by user ID.
	 *
	 * @param int $userId User ID.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @internal
	 */
	public static function getRolesByUserId($userId)
	{
		if(isset(self::$userRoles[$userId]))
			return self::$userRoles[$userId];

		$result = [];
		$userAccessCodes = \CAccess::getUserCodesArray($userId);

		if(!is_array($userAccessCodes) || count($userAccessCodes) === 0)
			return [];

		$cursor = Model\Role\AccessTable::getList([
			'filter' => [
				'=ACCESS_CODE' => $userAccessCodes
			]
		]);

		while($row = $cursor->fetch())
		{
			$result[] = $row['ROLE_ID'];
		}

		self::$userRoles[$userId] = $result;
		return $result;
	}

	/**
	 * Get role permissions.
	 *
	 * @param int $roleId Role ID.
	 * @return array
	 */
	public static function getRolePermissions($roleId)
	{
		$result = [];
		$list = Model\Role\PermissionTable::getList(['filter' => ['=ROLE_ID' => $roleId]]);
		foreach ($list as $row)
		{
			$result[$row['ENTITY']][$row['ACTION']] = $row['PERMISSION'];
		}

		return Permission::normalize($result);
	}

	/**
	 * Set role permissions. Add or update role
	 *
	 * @param int|null $roleId Role ID.
	 * @param array $roleFields Role fields.
	 * @param array $permissions Permissions.
	 * @return AddResult|\Bitrix\Main\Entity\UpdateResult
	 * @throws ArgumentException
	 */
	public static function setRolePermissions($roleId = null, array $roleFields = [], array $permissions)
	{
		$roleId = (int) $roleId;
		if ($roleId <= 0 && empty($roleFields))
		{
			throw new ArgumentException('Role id should be greater than zero', 'roleId');
		}

		if(RoleTable::getRowById($roleId))
		{
			if (!empty($roleFields))
			{
				$result = RoleTable::update($roleId, $roleFields);
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
		}
		else
		{
			$result = RoleTable::add($roleFields);
			if (!$result->isSuccess())
			{
				return $result;
			}

			$roleId = $result->getId();
		}

		$normalizedPermissions = Permission::normalize($permissions);
		Model\Role\PermissionTable::deleteByRoleId($roleId);
		foreach ($normalizedPermissions as $entity => $actions)
		{
			foreach ($actions as $action => $permission)
			{
				$result = Model\Role\PermissionTable::add(array(
					'ROLE_ID' => $roleId,
					'ENTITY' => $entity,
					'ACTION' => $action,
					'PERMISSION' => $permission
				));
				if (!$result->isSuccess())
				{
					return $result;
				}
			}
		}

		self::clearMenuCache();
		$result = new AddResult();
		$result->setId($roleId);
		return $result;
	}

	/**
	 * Deletes role and all dependent records.
	 *
	 * @param int $roleId Id of the role.
	 * @return void
	 */
	public static function deleteRole($roleId)
	{
		Model\Role\PermissionTable::deleteByRoleId($roleId);
		Model\Role\AccessTable::deleteByRoleId($roleId);
		RoleTable::delete($roleId);
		self::clearMenuCache();
	}

	/**
	 * Install roles.
	 *
	 * @return string
	 */
	public static function installRolesAgent()
	{
		self::installRoles();
		return '';
	}

	/**
	 * Install roles.
	 *
	 * @return void
	 */
	public static function installRoles()
	{
		$roleRow = RoleTable::getRow([]);
		if($roleRow)
		{
			return;
		}


		$defaultRoles = array(
			'ADMIN' => array(
				'NAME' => Loc::getMessage('SENDER_SECURITY_ROLE_MANAGER_INSTALLER_ADMIN'),
				'PERMISSIONS' => array(
					Permission::ENTITY_AD => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_RC => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_LETTER => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_SEGMENT => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_BLACKLIST => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_SETTINGS => array(
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
				)
			),
			'MANAGER' => array(
				'NAME' => Loc::getMessage('SENDER_SECURITY_ROLE_MANAGER_INSTALLER_MANAGER'),
				'PERMISSIONS' => array(
					Permission::ENTITY_AD => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_RC => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_LETTER => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_SEGMENT => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_NONE,
					),
					Permission::ENTITY_BLACKLIST => array(
						Permission::ACTION_VIEW => Permission::PERMISSION_ANY,
						Permission::ACTION_MODIFY => Permission::PERMISSION_ANY,
					),
					Permission::ENTITY_SETTINGS => array(
						Permission::ACTION_MODIFY => Permission::PERMISSION_NONE,
					),
				)
			)
		);

		$roleIds = array();
		foreach ($defaultRoles as $roleCode => $role)
		{
			$addResult = RoleTable::add(array(
				'NAME' => $role['NAME'],
				'XML_ID' => $roleCode,
			));

			$roleId = $addResult->getId();
			if ($roleId)
			{
				$roleIds[$roleCode] = $roleId;
				Manager::setRolePermissions($roleId, [], $role['PERMISSIONS']);
			}
		}

		if (isset($roleIds['ADMIN']))
		{
			Model\Role\AccessTable::add(array(
				'ROLE_ID' => $roleIds['ADMIN'],
				'ACCESS_CODE' => 'G1'
			));
		}
		if (isset($roleIds['MANAGER']) && Loader::includeModule('intranet'))
		{
			$departmentTree = \CIntranetUtils::getDeparmentsTree();
			$rootDepartment = (int)$departmentTree[0][0];

			if ($rootDepartment > 0)
			{
				Model\Role\AccessTable::add(array(
					'ROLE_ID' => $roleIds['MANAGER'],
					'ACCESS_CODE' => 'DR'.$rootDepartment
				));
			}
		}
	}
}