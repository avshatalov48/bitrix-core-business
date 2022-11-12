<?php

namespace Bitrix\Main\Access\Filter;

use Bitrix\Main\Access\AccessibleController;

/**
 * Factory for createing access filter.
 */
interface FilterFactory
{
	/**
	 * Create access filter.
	 *
	 * @param string $action
	 * @param AccessibleController $controller
	 *
	 * @return AccessFilter|null
	 */
	public function createFromAction(string $action, AccessibleController $controller): ?AccessFilter;
}
