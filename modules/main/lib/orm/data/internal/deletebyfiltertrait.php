<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\ORM\Data\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM\Query;

trait DeleteByFilterTrait
{
	/**
	 * @param array $filter
	 * @return void
	 * @throws Main\ArgumentException
	 */
	public static function deleteByFilter(array $filter)
	{
		$entity = static::getEntity();
		$table = static::getTableName();

		$where = Query\Query::buildFilterSql($entity, $filter);

		if($where <> '')
		{
			$where = ' where ' . $where;
		}
		else
		{
			throw new Main\ArgumentException("Deleting by empty filter is not allowed, use truncate ({$table}).", 'filter');
		}

		static::onBeforeDeleteByFilter($where);

		$entity->getConnection()->queryExecute("delete from {$table} {$where}");

		static::cleanCache();
	}

	protected static function onBeforeDeleteByFilter(string $where)
	{
		// may be implemented in a class that uses the trait
	}
}
