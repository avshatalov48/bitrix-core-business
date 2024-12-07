<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\ORM\Data\Internal;

trait MergeTrait
{
	/**
	 * @param array $insertFields
	 * @param array $updateFields
	 * @param array|null $uniqueFields
	 */
	public static function merge(array $insertFields, array $updateFields, ?array $uniqueFields = null)
	{
		$entity = static::getEntity();
		$conn = $entity->getConnection();
		$primary = ($uniqueFields ?? $entity->getPrimaryArray());

		$sql = $conn->getSqlHelper()->prepareMerge(static::getTableName(), $primary, $insertFields, $updateFields);

		$conn->queryExecute(current($sql));

		static::cleanCache();
	}
}
