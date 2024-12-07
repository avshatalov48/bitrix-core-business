<?php

namespace Bitrix\Catalog\Access\Permission\Catalog;

use Bitrix\Catalog\Access\ShopGroupAssistant;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\IblockSiteTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Context;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Main\TaskTable;
use Bitrix\Main\UserGroupTable;
use CIBlock;
use CIBlockRights;
use Throwable;

/**
 * The object for applying the rights of the catalog to the rights of the iblocks.
 *
 * Example:
 * ```php
 * 	$catalogPermission = new IblockCatalogPermissions([
 * 		'U1',
 * 		'G2',
 * 	]);
 * 	$catalogPermission->setRights([
 * 		[
 * 			'id' => \Bitrix\Catalog\Access\Permission\PermissionDictionary::CATALOG_PRODUCT_READ,
 * 			'value' => 0,
 * 		],
 * 		[
 * 			'id' => \Bitrix\Catalog\Access\Permission\PermissionDictionary::CATALOG_PRODUCT_ADD,
 * 			'value' => 1,
 * 		],
 * 	]);
 *
 *  $catalogPermissionSaver = new IblockCatalogPermissionsSaver();
 *  $catalogPermissionSaver->add($catalogPermission);
 *  $catalogPermissionSaver->save();
 * ```
 */
class IblockCatalogPermissionsSaver
{
	/**
	 * Use 'S' not 'R' because public component is admin list.
	 */
	private const READ_LETTER = 'S';
	private const WRITE_LETTER = 'W';
	private const FULL_LETTER = 'X';

	private ?string $siteId;
	private array $defaultRights;
	/**
	 * @var IblockCatalogPermissions[]
	 */
	private array $permissions = [];

	/**
	 * @param string|null $siteId
	 */
	public function __construct(string $siteId = null)
	{
		Loader::requireModule('iblock');

		$this->siteId = $siteId ?? Context::getCurrent()->getSite();
	}

	/**
	 * Append permissions object for saving.
	 *
	 * @param IblockCatalogPermissions $permission
	 *
	 * @return void
	 */
	public function add(IblockCatalogPermissions $permission): void
	{
		$this->permissions[] = $permission;
	}

	public static function convertRightsModeByAgent(string $mode): void
	{
		if ($mode !== IblockTable::RIGHTS_EXTENDED && $mode !== IblockTable::RIGHTS_SIMPLE)
		{
			return;
		}

		$saver = new self();
		$db = Application::getConnection();
		try
		{
			$db->startTransaction();

			foreach ($saver->getIblockIds() as $iblockId)
			{
				$saver->convertRightsMode($iblockId, $mode);
			}

			$db->commitTransaction();
		}
		catch (Throwable $e)
		{
			$db->rollbackTransaction();
			throw $e;
		}
	}

	/**
	 * Save iblock permissions.
	 *
	 * @return void
	 */
	public function save(): void
	{
		$deleteAccessCodes = $this->getDeleteAccessCodes();
		$actualAccessCodes = $this->getActualAccessCodesMap();
		$iblockCatalogIds = $this->getIblockIds();

		foreach ($iblockCatalogIds as $iblockId)
		{
			if (empty($actualAccessCodes))
			{
				$this->saveIblockRight($iblockId, null, [], $deleteAccessCodes);
			}
			foreach ($actualAccessCodes as $taskId => $accessCodes)
			{
				$this->saveIblockRight(
					$iblockId,
					$taskId,
					$accessCodes,
					$deleteAccessCodes
				);
			}
		}
	}

	private function getDeleteAccessCodes(): array
	{
		$result = [];

		foreach ($this->permissions as $permission)
		{
			foreach ($permission->getDeleteAccessCodes() as $accessCode)
			{
				$result[$accessCode] = true;
			}
		}

		return array_keys($result);
	}

	private function getActualAccessCodesMap(): array
	{
		$result = [];

		foreach ($this->permissions as $permission)
		{
			$taskId = $this->getIblockRightTaskId($permission);
			foreach ($permission->getAccessCodes() as $accessCode)
			{
				$result[$taskId][$accessCode] = true;
			}
		}

		foreach ($result as $taskId => & $accessCodes)
		{
			$accessCodes = array_keys($accessCodes);
		}
		unset($accessCodes);

		return $result;
	}

	/**
	 * Catalog iblock ids.
	 *
	 * @return array
	 */
	private function getIblockIds(): array
	{
		static $iblockIds;

		if (!isset($iblockIds))
		{
			$rows = CatalogIblockTable::getList([
				'select' => [
					'IBLOCK_ID',
				],
				'filter' => [
					'=IBLOCK.IBLOCK_TYPE_ID' => 'CRM_PRODUCT_CATALOG',
				],
			]);
			$iblockIds = array_column($rows->fetchAll(), 'IBLOCK_ID');

			// filter by site
			if ($iblockIds && isset($this->siteId))
			{
				$rows = IblockSiteTable::getList([
					'select' => [
						'IBLOCK_ID',
					],
					'filter' => [
						'=SITE_ID' => $this->siteId,
						'=IBLOCK_ID' => $iblockIds,
					],
				]);
				$iblockIds = array_column($rows->fetchAll(), 'IBLOCK_ID');
			}
		}

		return $iblockIds;
	}

	/**
	 * Save iblock rights.
	 *
	 * @param int $iblockId
	 * @param int|null $taskId
	 * @param array $accessCodes
	 * @param array $deleteAccessCodes
	 *
	 * @return void
	 */
	private function saveIblockRight(int $iblockId, ?int $taskId, array $accessCodes, array $deleteAccessCodes): void
	{
		if (empty($accessCodes) && empty($deleteAccessCodes))
		{
			return;
		}

		$this->convertRightsMode($iblockId);

		$usedAccessCodes = [];
		$iblockRights = new CIBlockRights($iblockId);
		$isNeedResetIblockRights = false;

		$rights = $iblockRights->GetRights();
		foreach ($rights as $id => &$right)
		{
			$rightAccessCode = $right['GROUP_CODE'];
			if (in_array($rightAccessCode, $deleteAccessCodes, true))
			{
				unset($rights[$id]);
				$isNeedResetIblockRights = true;

				continue;
			}

			if (!in_array($rightAccessCode, $accessCodes, true))
			{
				continue;
			}
			$usedAccessCodes[] = $rightAccessCode;

			$rightTaskId = (int)$right['TASK_ID'];
			if ($rightTaskId !== $taskId)
			{
				if (empty($taskId))
				{
					unset($rights[$id]);
				}
				else
				{
					$right['TASK_ID'] = $taskId;
				}

				$isNeedResetIblockRights = true;
			}
		}
		unset($right);

		if (!empty($taskId))
		{
			$i = 0;
			$newAccessCodes = array_diff($accessCodes, $usedAccessCodes);
			foreach ($newAccessCodes as $accessCode)
			{
				$rights["n{$i}"] = [
					'GROUP_CODE' => $accessCode,
					'TASK_ID' => $taskId,
				];
				$i++;

				$isNeedResetIblockRights = true;
			}
		}

		if (!$isNeedResetIblockRights)
		{
			return;
		}

		$rights = $this->appendDefaultRights($rights);
		$rights = array_slice($rights, 0, 300, true);

		$iblockRights->SetRights($rights);
	}

	/**
	 * Iblock all rights.
	 *
	 * @return array in format ['LETTER' => 'TASK_ID']
	 */
	private static function getIblockRightsLetterToTaskId(): array
	{
		static $iblockTasks;

		if (!isset($iblockTasks))
		{
			$rows = TaskTable::getList([
				'select' => [
					'ID',
					'LETTER',
				],
				'filter' => [
					'=MODULE_ID' => 'iblock',
				],
			]);
			$iblockTasks = array_column($rows->fetchAll(), 'ID', 'LETTER');
		}

		return $iblockTasks;
	}

	/**
	 * Get iblock task id for current permissions.
	 *
	 * @return int
	 */
	private function getIblockRightTaskId(IblockCatalogPermissions $permissions): int
	{
		$iblockTasks = self::getIblockRightsLetterToTaskId();

		if ($permissions->getCanFullAccess())
		{
			return (int)$iblockTasks[self::FULL_LETTER];
		}

		if ($permissions->getCanRead() && $permissions->getCanWrite())
		{
			return (int)$iblockTasks[self::WRITE_LETTER];
		}

		if ($permissions->getCanRead())
		{
			return (int)$iblockTasks[self::READ_LETTER];
		}

		return 0;
	}

	/**
	 * Converts (if needed) rights mode of iblock.
	 *
	 * @param int $iblockId
	 *
	 * @return void
	 *
	 * @throws SystemException if cannot change rights mode for iblock
	 */
	private function convertRightsMode(int $iblockId, string $mode = IblockTable::RIGHTS_EXTENDED): void
	{
		$mode = ($mode === IblockTable::RIGHTS_EXTENDED) ? IblockTable::RIGHTS_EXTENDED : IblockTable::RIGHTS_SIMPLE;
		$currentRightsMode = CIBlock::GetArrayByID($iblockId, 'RIGHTS_MODE');
		if ($currentRightsMode === $mode)
		{
			return;
		}

		if ($mode === IblockTable::RIGHTS_SIMPLE)
		{
			$currentGroup = new CIBlockRights($iblockId);
			$convertGroupRights = [];
			$iblockTaskIds = array_flip(self::getIblockRightsLetterToTaskId());
			foreach ($currentGroup->GetRights() as $group)
			{
				if (!empty($group['GROUP_CODE']) && $group['GROUP_CODE'][0] === 'G')
				{
					$code = (int)mb_substr($group['GROUP_CODE'], 1);
					$isAdminGroup = ($code === 1);
					if ($code && !$isAdminGroup)
					{
						$convertGroupRights[$code] = $iblockTaskIds[$group['TASK_ID']];
					}
				}
			}
		}

		$iblock = new CIBlock();
		$result = $iblock->Update(
			$iblockId,
			[
				'RIGHTS_MODE' => $mode,
				'GROUP_ID' => CIBlock::GetGroupPermissions($iblockId),
			]
		);

		if (!$result)
		{
			throw new SystemException("Cannot change iblock '{$iblockId}' rights mode");
		}

		if ($mode === IblockTable::RIGHTS_SIMPLE && $convertGroupRights)
		{
			\CIBlock::SetPermission($iblockId, $convertGroupRights);
		}
	}

	/**
	 * Default rights for catalog iblock.
	 *
	 * @return array
	 */
	private function getDefaultIblockRights(): array
	{
		if (!isset($this->defaultRights))
		{
			$iblockTasks = self::getIblockRightsLetterToTaskId();

			$this->defaultRights = [
				'G2' => $iblockTasks['R'],
			];

			$rows = GroupTable::getList([
				'select' => [
					'ID',
					'STRING_ID',
				],
				'filter' => [
					'@STRING_ID' => [
						'CRM_SHOP_ADMIN',
						'CRM_SHOP_MANAGER',
					],
				],
			]);
			$crmGroups = array_column($rows->fetchAll(), 'ID', 'STRING_ID');
			$crmGroupsRights = [
				'CRM_SHOP_ADMIN' => $iblockTasks['X'],
				'CRM_SHOP_MANAGER' => $iblockTasks['W'],
			];

			foreach ($crmGroupsRights as $groupCode => $rightCode)
			{
				if (isset($crmGroups[$groupCode]))
				{
					$this->defaultRights['G' . $crmGroups[$groupCode]] = $rightCode;
				}
			}
		}

		return $this->defaultRights;
	}

	/**
	 * Append default rights for catalog iblock.
	 *
	 * @param array $rights
	 *
	 * @return array
	 */
	private function appendDefaultRights(array $rights): array
	{
		$defaultRights = $this->getDefaultIblockRights();
		foreach ($rights as $item)
		{
			$accessCode = $item['GROUP_CODE'];
			$defaultTaskId = $defaultRights[$accessCode] ?? null;
			if (isset($defaultTaskId))
			{
				$item['TASK_ID'] = $defaultTaskId;
				unset($defaultRights[$accessCode]);

				if (empty($defaultRights))
				{
					break;
				}
			}
		}

		if (!empty($defaultRights))
		{
			$i = count($rights);
			foreach ($defaultRights as $accessCode => $taskId)
			{
				$rights["n{$i}"] = [
					'GROUP_CODE' => $accessCode,
					'TASK_ID' => $taskId,
				];
				$i++;
			}
		}

		return $rights;
	}

	public static function updateShopAccessGroup(array $userIds, array $allUserIds, string $groupType): void
	{
		$shopIblockGroups = [
			ShopGroupAssistant::SHOP_MANAGER_USER_GROUP_CODE,
			ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE
		];

		if (!in_array($groupType, $shopIblockGroups, true))
		{
			return;
		}

		$groupId = self::getShopGroupIdByType($groupType);
		if (!$groupId)
		{
			return;
		}

		$currentGroupUserIds = \CGroup::getGroupUser($groupId);
		$nonGroupUsers = array_diff($allUserIds, $userIds);
		if ($nonGroupUsers)
		{
			$removeFromGroup = array_intersect($nonGroupUsers, $currentGroupUserIds);
			if ($removeFromGroup)
			{
				$userGroupCollection = UserGroupTable::query()
					->where('GROUP_ID', $groupId)
					->whereIn('USER_ID', $removeFromGroup)
					->fetchCollection()
				;

				foreach ($userGroupCollection as $userGroup)
				{
					$userId = $userGroup->getUserId();
					$userGroup->delete();
					\CUser::clearUserGroupCache($userId);
				}
			}
		}

		$addToGroup = array_diff($userIds, $currentGroupUserIds);
		foreach ($addToGroup as $userId)
		{
			\CUser::appendUserGroup($userId, [$groupId]);
		}
	}

	private static function getShopGroupIdByType(string $type): ?int
	{
		$group = GroupTable::getRow([
			'filter' => ['=STRING_ID' => $type],
			'select' => ['ID']
		]);

		if ($group)
		{
			return (int)$group['ID'];
		}

		return null;
	}
}
