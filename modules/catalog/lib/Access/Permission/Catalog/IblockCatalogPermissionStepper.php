<?php

namespace Bitrix\Catalog\Access\Permission\Catalog;

use Bitrix\Catalog\Access\ShopGroupAssistant;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\UserTable;

/**
 * Class IblockCatalogPermissionStepper
 *
 * <code>
 * \Bitrix\Main\Update\Stepper::bindClass('Bitrix\Catalog\Access\Permission\Catalog\IblockCatalogPermissionStepper', 'catalog');
 * </code>
 *
 * @package Bitrix\Catalog\Access\Permission\Catalog
 */
final class IblockCatalogPermissionStepper extends Stepper
{
	private const PORTION = 100;

	protected static $moduleId = 'catalog';

	public function execute(array &$option): bool
	{
		if (!Loader::includeModule('iblock'))
		{
			return self::FINISH_EXECUTION;
		}

		$emptyDepartmentTypeFirst = serialize([]);
		$emptyDepartmentTypeSecond = serialize([0]);
		$externalTypes = UserTable::getExternalUserTypes();
		$externalTypes[] = null;
		$filter = [
			'!=UF_DEPARTMENT' => [null, $emptyDepartmentTypeFirst, $emptyDepartmentTypeSecond],
			'!=EXTERNAL_AUTH_ID' => $externalTypes,
		];

		if (isset($option['lastId']))
		{
			$filter['>ID'] = (int)$option['lastId'];
		}

		$userData = UserTable::getList([
				'filter' => $filter,
				'select' => ['ID'],
				'limit' =>  self::PORTION,
			])
			->fetchAll()
		;

		$userIds = array_column($userData, 'ID');
		if ($userIds)
		{
			$this->updateIblockAccess($userIds);
			if (count($userIds) === self::PORTION)
			{
				$option['lastId'] = array_pop($userIds);

				return self::CONTINUE_EXECUTION;
			}
		}

		return self::FINISH_EXECUTION;
	}

	private function updateIblockAccess(array $userIds): void
	{
		$userGroupMap = [
			ShopGroupAssistant::SHOP_MANAGER_USER_GROUP_CODE => [],
			ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE => [],
		];

		foreach ($userIds as $userId)
		{
			$groupCode = ShopGroupAssistant::getShopUserGroupCode($userId);
			if ($groupCode && isset($userGroupMap[$groupCode]))
			{
				$userGroupMap[$groupCode][] = $userId;
			}
		}

		foreach ($userGroupMap as $groupCode => $groupUserIds)
		{
			IblockCatalogPermissionsSaver::updateShopAccessGroup(
				$groupUserIds,
				$userIds,
				$groupCode
			);
		}
	}
}