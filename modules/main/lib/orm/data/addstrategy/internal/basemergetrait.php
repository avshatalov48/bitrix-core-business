<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Internal;

use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\Merge;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * @mixin DataManager
 * @internal
 */
trait BaseMergeTrait
{
	/**
	 * @var Array<class-string, AddStrategy>
	 */
	private static array $mergeStrategyCache = [];

	private static function getCachedMergeStrategy(): AddStrategy
	{
		self::$mergeStrategyCache[static::class] ??= static::getMergeStrategy();

		return self::$mergeStrategyCache[static::class];
	}

	protected static function getMergeStrategy(): AddStrategy
	{
		return new Merge(static::getEntity());
	}
}
