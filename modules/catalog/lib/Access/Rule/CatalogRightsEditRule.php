<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Config\Feature;
use Bitrix\Main\Access\AccessibleItem;

class CatalogRightsEditRule extends BaseRule
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
		if (!Feature::isAccessControllerCheckingEnabled())
		{
			return false;
		}

		return parent::execute($item, $params);
	}

	public function getPermissionValue($params): ?int
	{
		if ($this->user->isAdmin())
		{
			return 1;
		}

		return parent::getPermissionValue($params);
	}
}
