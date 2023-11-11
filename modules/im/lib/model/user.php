<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\ExpressionField;

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

	public static function getMap()
	{
		$emptyValue = serialize([]);
		$emptyValue2 = serialize([0]);

		$additionalFields = [
			(new ExpressionField(
				'IS_INTRANET_USER',
				'CASE WHEN
					((%s IS NOT NULL AND %s != \'' . $emptyValue . '\' AND %s != \'' . $emptyValue2 . '\') AND
					(%s IS NULL OR %s NOT IN (\'' . implode('\', \'', self::filterExternalUserTypes(['bot'])) . '\')))
					OR (%s = \'bot\')
					THEN \'Y\'
					ELSE \'N\'
				END',
				['UF_DEPARTMENT', 'UF_DEPARTMENT', 'UF_DEPARTMENT', 'EXTERNAL_AUTH_ID', 'EXTERNAL_AUTH_ID', 'EXTERNAL_AUTH_ID'],
				['values' => ['N', 'Y']]
			))->configureValueType(BooleanField::class)
		];

		return array_merge(parent::getMap(), $additionalFields);
	}
}
