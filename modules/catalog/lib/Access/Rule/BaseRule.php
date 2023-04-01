<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Config\Feature;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\Model\UserModel;

class BaseRule extends AbstractRule
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
		if ($this->controller->isAdmin())
		{
			return true;
		}

		if (!Feature::isAccessControllerCheckingEnabled())
		{
			$userDepartments = $this->user->getUserDepartments();

			if (empty($userDepartments))
			{
				return false;
			}

			return count($userDepartments) > 1 || $userDepartments[0] !== 0;
		}

		if (!$params)
		{
			return false;
		}

		$params['item'] = $item;

		return $this->check($params);
	}

	public function getPermissionValue($params): ?int
	{
		if (!Feature::isAccessControllerCheckingEnabled())
		{
			return 1;
		}

		$permissionCode = static::getPermissionCode($params);

		if (!$permissionCode)
		{
			return null;
		}

		return $this->user->getPermission($permissionCode);
	}

	/**
	 *
	 */
	protected function check($params): bool
	{
		return (bool)$this->getPermissionValue($params);
	}

	/**
	 * @param array $params
	 * @return string | null
	 */
	protected static function getPermissionCode(array $params): ?string
	{
		$permissionCode = ActionDictionary::getActionPermissionMap()[$params['action']];

		if (!$permissionCode)
		{
			return null;
		}

		return (string)$permissionCode;
	}
}
