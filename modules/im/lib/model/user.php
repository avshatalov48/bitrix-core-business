<?php

namespace Bitrix\Im\Model;

/**
 * Class UserTable
 */
class UserTable extends \Bitrix\Main\UserTable
{
	/**
	 * Returns filtered list of external user types.
	 * @param string[] $skipTypes Type list to skip out.
	 * @return string[]
	 */
	public static function filterExternalUserTypes(array $skipTypes = []): array
	{
		$types = \Bitrix\Main\UserTable::getExternalUserTypes();
		if (empty($skipTypes))
		{
			return $types;
		}

		$types = array_filter($types, function($authId) use ($skipTypes) {
			return !in_array($authId, $skipTypes, true);
		});

		return $types;
	}
}
