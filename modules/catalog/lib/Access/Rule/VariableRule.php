<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Config\Feature;

/**
 * Rule for action with multiple permissions.
 *
 * Supports multiple check:
 * ```php
 * // check access only `1` store
 * VariableRule::check(['value' => 1]);
 *
 * // check access to all `1,2,3` stores
 * VariableRule::check(['value' => [1,2,3]]);
 * ```
 *
 * @property \Bitrix\Catalog\Access\Model\UserModel $user
 */
class VariableRule extends BaseRule
{
	public function getPermissionMultiValues($params): ?array
	{
		if (!Feature::isAccessControllerCheckingEnabled())
		{
			return [$this->getAllValue()];
		}

		$permissionCode = static::getPermissionCode($params);

		$values = $this->user->getPermissionMulti($permissionCode);
		return $values ? array_intersect($values, $this->getAvailableValues()): null;
	}

	protected function getAvailableValues(): array
	{
		$values = $this->loadAvailableValues();
		$values[] = $this->getAllValue();

		return $values;
	}

	protected function loadAvailableValues(): array
	{
		return [];
	}

	protected function getAllValue(): int
	{
		return PermissionDictionary::VALUE_VARIATION_ALL;
	}

	protected function check($params): bool
	{
		/** @var ?array $values */
		$values = $this->getPermissionMultiValues($params);
		if (!$values)
		{
			return false;
		}

		if (
			(!isset($params['value']) && !empty($values))
			|| in_array($this->getAllValue(), $values, true)
		)
		{
			return true;
		}

		$checkStoreIds = (array)($params['value'] ?? []);

		return empty(
			array_diff($checkStoreIds, $values)
		);
	}
}
