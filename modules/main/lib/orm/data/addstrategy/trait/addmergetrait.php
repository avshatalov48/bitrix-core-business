<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Trait;

use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\Merge;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\AddStrategy\Internal\BaseMergeTrait;

/**
 * @mixin DataManager
 */
trait AddMergeTrait
{
	use BaseMergeTrait;

	/**
	 * Adds row to entity table, for details @see DataManager::add
	 *
	 * This method adds a new row to the DB EXCEPT there is already a row
	 * that contains the same primary/unique field value.
	 * In this case, the existing row will be updated to hold the same data. Think of it as using an 'INSERT ... ON
	 * DUPLICATE KEY UPDATE' SQL statement.
	 * Events are not supported and are never fired.
	 */
	final public static function addMerge(array $data): AddResult
	{
		return self::sysAddInternal(self::getCachedMergeStrategy(), $data);
	}

	/**
	 * Adds several rows to entity table, for details @see DataManager::addMulti
	 *
	 * This method adds a new row to the DB EXCEPT there is already a row
	 * that contains the same primary/unique field value.
	 * In this case, the existing row will be updated to hold the same data. Think of it as using an 'INSERT ... ON
	 * DUPLICATE KEY UPDATE' SQL statement.
	 * Events are not supported and are never fired.
	 */
	final public static function addMergeMulti(array $rows): AddResult
	{
		return self::sysAddMultiInternal(
			self::getCachedMergeStrategy(),
			$rows,
		);
	}
}
