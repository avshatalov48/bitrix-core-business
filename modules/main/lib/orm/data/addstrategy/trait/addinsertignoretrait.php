<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Trait;

use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\AddStrategy\Internal\BaseInsertIgnoreTrait;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * @mixin DataManager
 */
trait AddInsertIgnoreTrait
{
	use BaseInsertIgnoreTrait;

	/**
	 * Adds row to entity table, for details @see DataManager::add
	 *
	 * This method adds a new row to the DB EXCEPT there is already a row
	 * that contains the same primary/unique field value.
	 * In this case, the new row WILL NOT be added. Think of it as using an 'INSERT IGNORE' SQL statement.
	 * Events are not supported and are never fired.
	 */
	final public static function addInsertIgnore(array $data): AddResult
	{
		return self::sysAddInternal(self::getCachedInsertIgnoreStrategy(), $data);
	}

	/**
	 * Adds several rows to entity table, for details @see DataManager::addMulti
	 *
	 * This method adds a new row to the DB EXCEPT there is already a row
	 * that contains the same primary/unique field value.
	 * In this case, the new row WILL NOT be added. Think of it as using an 'INSERT IGNORE' SQL statement.
	 * Events are not supported and are never fired.
	 */
	final public static function addInsertIgnoreMulti(array $rows, bool $ignoreEvents = false): AddResult
	{
		return self::sysAddMultiInternal(
			self::getCachedInsertIgnoreStrategy(),
			$rows,
			$ignoreEvents
		);
	}
}
