<?php

namespace Bitrix\Sender\Access\Rule;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;
use Bitrix\Sender\Access\ActionDictionary;

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

		if($this->user->getPermission(ActionDictionary::getActionPermissionMap()[$params['action']]))
		{
			return true;
		}

		return false;
	}
}