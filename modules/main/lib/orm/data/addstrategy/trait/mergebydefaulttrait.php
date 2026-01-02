<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Trait;

use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\Internal\BaseMergeTrait;
use Bitrix\Main\ORM\Data\AddStrategy\Merge;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * @mixin DataManager
 */
trait MergeByDefaultTrait
{
	use BaseMergeTrait;

	protected static function getAddStrategy(): AddStrategy
	{
		return self::getCachedMergeStrategy();
	}
}
