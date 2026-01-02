<?php

namespace Bitrix\Catalog\Access\IblockRule;

use Bitrix\Catalog\Access\ShopGroupAssistant;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\Model\UserModel;
use Bitrix\Main\GroupTable;

class BaseIblockRule extends AbstractRule
{
	/* @var AccessController $controller */
	/* @var UserModel $user */

	/**
	 * check access permission
	 * @param AccessibleItem|null $item
	 * @param null $params
	 *
	 * @return bool
	 */
	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if ($this->user->isAdmin())
		{
			return true;
		}

		return $this->check($params);
	}

	/**
	 * @param $params
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function check($params): bool
	{
		$groups = $this->user->getRightGroups();

		$shopGroupIds = [];
		foreach ($this->getShopIblockTypes() as $groupCode)
		{
			$groupId = \CGroup::GetIDByCode($groupCode);
			if ($groupId)
			{
				$shopGroupIds[] = $groupId;
			}
		}

		if (empty($shopGroupIds))
		{
			return false;
		}

		return !empty(array_intersect($groups, $shopGroupIds));
	}

	/**
	 * @return array
	 */
	protected function getShopIblockTypes(): array
	{
		return [ShopGroupAssistant::SHOP_ADMIN_USER_GROUP_CODE, ShopGroupAssistant::SHOP_MANAGER_USER_GROUP_CODE];
	}
}
