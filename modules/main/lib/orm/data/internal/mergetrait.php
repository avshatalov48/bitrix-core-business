<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\ORM\Data\Internal;

trait MergeTrait
{
	/**
	 * @param array $insertFields
	 * @param array $updateFields
	 */
	public static function merge(array $insertFields, array $updateFields)
	{
		$entity = static::getEntity();
		$conn = $entity->getConnection();
		$primary = $entity->getPrimaryArray();

		$sql = $conn->getSqlHelper()->prepareMerge(static::getTableName(), $primary, $insertFields, $updateFields);

		$conn->queryExecute(current($sql));

		static::cleanCache();
	}
}
