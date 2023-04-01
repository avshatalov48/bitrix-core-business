<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access;

use Bitrix\Catalog\Config\Feature;
use Bitrix\Main\GroupTable;

class ShopGroupAssistant
{
	public const SHOP_ADMIN_USER_GROUP_CODE = 'CRM_SHOP_ADMIN';
	public const SHOP_MANAGER_USER_GROUP_CODE = 'CRM_SHOP_MANAGER';

	/**
	 * @param int $userId
	 * @return string|null
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 */
	public static function getShopUserGroupCode(int $userId): ?string
	{
		/** @var AccessController $controller */
		$controller = AccessController::getInstance($userId);

		if (!Feature::isAccessControllerCheckingEnabled())
		{
			if ($controller->isAdmin())
			{
				return self::SHOP_ADMIN_USER_GROUP_CODE;
			}

			return self::SHOP_MANAGER_USER_GROUP_CODE;
		}

		if ($controller->check( ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS))
		{
			return self::SHOP_ADMIN_USER_GROUP_CODE;
		}

		if ($controller->check( ActionDictionary::ACTION_CATALOG_READ))
		{
			return self::SHOP_MANAGER_USER_GROUP_CODE;
		}

		return null;
	}

	/**
	 * Append user to shop group
	 *
	 * @param $userId
	 *
	 * @return bool
	 * @throws \Bitrix\Main\Access\Exception\UnknownActionException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function addShopAccess($userId): bool
	{
		$groupCode = self::getShopUserGroupCode($userId);
		if (!$groupCode)
		{
			return false;
		}

		$group = GroupTable::getRow([
			'filter' => ['STRING_ID' => $groupCode],
			'select' => ['ID']
		]);

		if (!$group)
		{
			return false;
		}

		\CUser::appendUserGroup($userId, [$group['ID']]);

		return true;
	}
}
