<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Install\Role;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;

abstract class Base
{
	/**
	 * @return array
	 */
	abstract public function getPermissions(): array;

	public function getMap(): array
	{
		$result = [];
		foreach ($this->getPermissions() as $permissionId)
		{
			foreach ($this->getPermissionValue($permissionId) as $value)
			{
				$result[] = [
					'permissionId' => $permissionId,
					'value' => $value
				];
			}
		}

		return $result;
	}

	protected function getPermissionValue($permissionId): array
	{
		return [PermissionDictionary::getDefaultPermissionValue($permissionId)];
	}
}