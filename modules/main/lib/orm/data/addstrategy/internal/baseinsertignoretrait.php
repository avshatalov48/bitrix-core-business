<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Internal;

use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\InsertIgnore;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * @mixin DataManager
 * @internal
 */
trait BaseInsertIgnoreTrait
{
	/**
	 * @var Array<class-string, AddStrategy>
	 */
	private static array $insertIgnoreStrategyCache = [];

	private static function getCachedInsertIgnoreStrategy(): AddStrategy
	{
		self::$insertIgnoreStrategyCache[static::class] ??= static::getInsertIgnoreStrategy();

		return self::$insertIgnoreStrategyCache[static::class];
	}

	protected static function getInsertIgnoreStrategy(): AddStrategy
	{
		return new InsertIgnore(static::getEntity());
	}
}
