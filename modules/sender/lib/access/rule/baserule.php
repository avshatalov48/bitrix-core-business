<?php

namespace Bitrix\Sender\Access\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Role\RoleUtil;
use Bitrix\Sender\Integration\Bitrix24\Service;
use Bitrix\Sender\Security\User;

class BaseRule extends AbstractRule
{
	/**
	 * check access permission
	 * @param AccessibleItem|null $item
	 * @param null $params
	 *
	 * @return bool
	 */
	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if($this->user->isAdmin())
		{
			return true;
		}

		$action = ActionDictionary::getActionPermissionMap()[$params['action']];
		if (Service::isCloud() && !Service::isPermissionEnabled())
		{
			$user = User::get($this->user->getUserId());
			return $user->isPortalAdmin() || in_array($action, RoleUtil::preparedRoleMap()['MANAGER']);
		}

		if($this->user->getPermission($action))
		{
			return true;
		}

		return false;
	}
}