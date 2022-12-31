<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Install;

use Bitrix\Catalog\Access\Role\RoleDictionary;

class RoleMap
{
	/**
	 * @return array[]
	 */
	public static function getDefaultMap(): array
	{
		return [
			RoleDictionary::ROLE_DIRECTOR => Role\Director::class,
			RoleDictionary::ROLE_SALESMAN => Role\Salesman::class,
			RoleDictionary::ROLE_STOCKMAN => Role\Stockman::class,
		];
	}
}