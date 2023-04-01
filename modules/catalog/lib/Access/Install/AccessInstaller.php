<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Install;

use Bitrix\Catalog\Access\ShopGroupAssistant;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Install\AccessInstaller\InstallStatus;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionTable;
use Bitrix\Catalog\Access\Role\RoleDictionary;
use Bitrix\Catalog\Access\Role\RoleTable;
use Bitrix\Catalog\Access\Role\RoleUtil;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Application;
use Bitrix\Main\GroupTable;
use Bitrix\Main\GroupTaskTable;
use Bitrix\Main\TaskOperationTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlExpression;
use Throwable;

class AccessInstaller
{
	private Connection $db;

	public function __construct(Connection $db)
	{
		$this->db = $db;
	}

	public function createTables(): void
	{
		$this->db->Query("
			CREATE TABLE IF NOT EXISTS b_catalog_role (
				ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
				NAME VARCHAR(250) NOT NULL,
				PRIMARY KEY (ID)
			);
		");

		$this->db->Query("
			CREATE TABLE IF NOT EXISTS b_catalog_role_relation (
				ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
				ROLE_ID INT UNSIGNED NOT NULL,
				RELATION VARCHAR(8) NOT NULL DEFAULT '',
				PRIMARY KEY (ID),
				INDEX ROLE_ID (ROLE_ID),
				INDEX RELATION (RELATION)
			);
		");

		$this->db->Query("
			CREATE TABLE IF NOT EXISTS b_catalog_permission (
				ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
				ROLE_ID INT UNSIGNED NOT NULL,
				PERMISSION_ID VARCHAR(32) NOT NULL DEFAULT '0',
				VALUE INT NOT NULL DEFAULT '0',
				PRIMARY KEY (ID),
				INDEX ROLE_ID (ROLE_ID),
				INDEX PERMISSION_ID (PERMISSION_ID)
			);
		");
	}

	public static function installByAgent(): void
	{
		$db = Application::getConnection();

		(new self($db))->install();
	}

	public static function installClean(): void
	{
		$db = Application::getConnection();

		(new self($db))->install(false);
	}

	public function install($convertExisted = true): void
	{
		$lockName = PermissionTable::getTableName();
		if (!$this->db->lock($lockName, 600))
		{
			return;
		}

		try
		{
			InstallStatus::start();

			$this->db->startTransaction();
			if ($convertExisted)
			{
				$this->fillSystemPermissions();
			}
			else
			{
				$this->fillDefaultSystemPermissions();
			}
			$this->db->commitTransaction();

			InstallStatus::finish();
		}
		catch (Throwable $e)
		{
			$this->db->rollbackTransaction();
			throw $e;
		}
		finally
		{
			$this->db->unlock($lockName);
		}
	}

	private function fillSystemPermissions(): void
	{
		if (PermissionTable::getCount())
		{
			return;
		}

		$catalogGroupTasks = GroupTaskTable::getList([
				'filter' => [
					'TASK.MODULE_ID' => 'catalog',
					'!=TASK.LETTER' => 'D'
				],
				'select' => [
					'GROUP_ID',
					'GROUP_NAME' => 'GROUP.NAME',
					'TASK_ID',
				],
			])
			->fetchAll()
		;

		if (!$catalogGroupTasks)
		{
			$this->fillDefaultSystemPermissions();

			return;
		}

		$this->fillGroupTaskPermissions($catalogGroupTasks);
		$this->fillDefaultSystemPermissions([RoleDictionary::ROLE_STOCKMAN]);
	}

	private function fillGroupTaskPermissions(array $catalogGroupTasks): void
	{
		$taskIds = array_column($catalogGroupTasks, 'TASK_ID');
		$taskOperations = TaskOperationTable::getList([
			'filter' => [
				'TASK_ID' => $taskIds,
			],
			'select' => [
				'TASK_ID',
				'OPERATION_NAME' => 'OPERATION.NAME',
			],
		]);

		$permissionMap = ActionDictionary::getActionPermissionMap();
		$storeDocumentsInstallerMap = [
			ActionDictionary::ACTION_STORE_DOCUMENT_VIEW => PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
			ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY => PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
			ActionDictionary::ACTION_STORE_DOCUMENT_CANCEL => PermissionDictionary::CATALOG_STORE_DOCUMENT_CANCEL,
			ActionDictionary::ACTION_STORE_DOCUMENT_CONDUCT => PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT,
			ActionDictionary::ACTION_STORE_DOCUMENT_DELETE => PermissionDictionary::CATALOG_STORE_DOCUMENT_DELETE,
			ActionDictionary::ACTION_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY => PermissionDictionary::CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY,
		];
		$permissionMap = array_merge($permissionMap, $storeDocumentsInstallerMap);

		$taskPermissionMap = [];
		while ($taskOperation = $taskOperations->fetch())
		{
			$taskId = $taskOperation['TASK_ID'];
			$taskPermissionMap[$taskId] ??= [];

			$newActions = ActionDictionary::getLegacyMap()[$taskOperation['OPERATION_NAME']] ?? [];
			foreach ($newActions as $newAction)
			{
				$permission = $permissionMap[$newAction] ?? null;
				if (!$permission)
				{
					continue;
				}

				if (in_array($permission, $storeDocumentsInstallerMap, true))
				{
					$documents = null;
					if ($permission === PermissionDictionary::CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY)
					{
						$documents = [
							StoreDocumentTable::TYPE_MOVING,
							StoreDocumentTable::TYPE_DEDUCT,
							StoreDocumentTable::TYPE_SALES_ORDERS,
						];
					}
					$taskPermissionMap[$taskId] = array_merge(
						$taskPermissionMap[$taskId],
						PermissionDictionary::getStoreDocumentPermissionRules([$permission], $documents)
					);
				}
				else
				{
					$taskPermissionMap[$taskId][] = $permission;
				}
			}
		}

		$groups = [];
		foreach ($catalogGroupTasks as $groupTask)
		{
			$groups[$groupTask['GROUP_ID']] ??= [];
			$groups[$groupTask['GROUP_ID']]['NAME'] = $groupTask['GROUP_NAME'];
			$groups[$groupTask['GROUP_ID']]['PERMISSIONS'][] = $taskPermissionMap[$groupTask['TASK_ID']];
		}

		$crmAdminGroupIds = [];
		$crmAdminGroups = GroupTable::getList([
			'filter' => ['=STRING_ID' => ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE],
			'select' => ['ID'],
		]);
		while ($crmAdminGroup = $crmAdminGroups->fetch())
		{
			$crmAdminGroupIds[] = (int)$crmAdminGroup['ID'];
		}

		foreach ($groups as $groupId => &$group)
		{
			$group['PERMISSIONS'] = array_unique(array_merge(...$group['PERMISSIONS']));
			if (in_array($groupId, $crmAdminGroupIds, true))
			{
				$group['PERMISSIONS'][] = PermissionDictionary::CATALOG_SETTINGS_EDIT_RIGHTS;
			}
		}

		$groupRoleMap = $this->fillGroupPermissions($groups);
		$this->fillGroupUserRoleRelations($groupRoleMap);
	}

	private function fillGroupPermissions(array $groups): array
	{
		$query = [];
		$result = [];
		foreach ($groups as $groupId => $groupData)
		{
			if (!is_array($groupData['PERMISSIONS']) || !$groupData['PERMISSIONS'])
			{
				continue;
			}

			$role = RoleTable::add([
				'NAME' => $groupData['NAME']
			]);

			if (!$role->isSuccess())
			{
				continue;
			}

			$roleId = $role->getId();
			foreach ($groupData['PERMISSIONS'] as $permissionId)
			{
				if ($permissionId === PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE && Option::get('crm', 'enable_product_price_edit') !== 'Y')
				{
					continue;
				}

				$value = PermissionDictionary::getDefaultPermissionValue($permissionId);
				$query[] = "('{$roleId}', '{$permissionId}', '{$value}')";
			}

			$result[$groupId] = $roleId;
		}

		RoleUtil::insertPermissions($query);

		return $result;
	}

	private function fillGroupUserRoleRelations(array $groupRoleMap): void
	{
		$userGroups = UserGroupTable::getList([
			'select' => ['USER_ID', 'GROUP_ID'],
			'filter' => [
				'=GROUP_ID' => array_keys($groupRoleMap),
				'=USER.ACTIVE' => 'Y',
				'=USER.IS_REAL_USER' => 'Y',
			],
		]);

		$valuesData = [];
		while ($user = $userGroups->fetch())
		{
			$groupId = (int)($groupRoleMap[$user['GROUP_ID']] ?? 0);
			if ($groupId > 0)
			{
				$valuesData[] = new SqlExpression("(?, ?)", $groupId, "U{$user['USER_ID']}");
			}
		}

		if (!$valuesData)
		{
			return;
		}

		$query = '
			INSERT INTO b_catalog_role_relation
				(ROLE_ID, RELATION)
				VALUES ' . implode(',', $valuesData) . '
		';

		Application::getConnection()->query($query);
	}

	private function fillDefaultSystemPermissions(array $roles = null): void
	{
		$map = RoleMap::getDefaultMap();

		if ($roles !== null)
		{
			$map = array_intersect_key($map, array_flip($roles));
			if (!$map)
			{
				return;
			}
		}

		$query = [];
		$roleNameIdMap = [];
		foreach ($map as $roleName => $roleClass)
		{
			if (is_subclass_of($roleClass, Role\Base::class))
			{
				$roleMapItem = new $roleClass();
			}
			else
			{
				continue;
			}

			$role = RoleTable::add([
				'NAME' => $roleName
			]);

			if (!$role->isSuccess())
			{
				continue;
			}

			$roleId = $role->getId();
			$roleNameIdMap[$roleName] = $roleId;
			foreach ($roleMapItem->getMap() as $item)
			{
				$query[] = new SqlExpression(
					'(?i,?,?)',
					$roleId,
					$item['permissionId'],
					$item['value']
				);
			}
		}

		RoleUtil::insertPermissions($query);

		if (!array_intersect_key($map, array_flip([RoleDictionary::ROLE_DIRECTOR, RoleDictionary::ROLE_SALESMAN])))
		{
			return;
		}

		$userGroups = GroupTable::getList([
			'filter' => [
				'=STRING_ID' => [
					ShopGroupAssistant::SHOP_MANAGER_USER_GROUP_CODE,
					ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE,
				]
			],
			'select' => ['ID', 'STRING_ID']
		]);

		$defaultGroupRoleMap = [];
		while ($userGroup = $userGroups->fetch())
		{
			$role =
				$userGroup['STRING_ID'] === ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE && $map[RoleDictionary::ROLE_DIRECTOR]
					? $roleNameIdMap[RoleDictionary::ROLE_DIRECTOR]
					: $roleNameIdMap[RoleDictionary::ROLE_SALESMAN]
			;

			$defaultGroupRoleMap[$userGroup['ID']] = $role;
		}

		if (!$defaultGroupRoleMap)
		{
			return;
		}

		$this->fillGroupUserRoleRelations($defaultGroupRoleMap);
	}
}
