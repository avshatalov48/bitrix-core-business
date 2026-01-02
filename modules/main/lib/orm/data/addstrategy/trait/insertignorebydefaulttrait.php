<?php

namespace Bitrix\Main\ORM\Data\AddStrategy\Trait;

use Bitrix\Main\ORM\Data\AddStrategy\Contract\AddStrategy;
use Bitrix\Main\ORM\Data\AddStrategy\Internal\BaseInsertIgnoreTrait;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * @mixin DataManager
 */
trait InsertIgnoreByDefaultTrait
{
	use BaseInsertIgnoreTrait;

	protected static function getAddStrategy(): AddStrategy
	{
		return self::getCachedInsertIgnoreStrategy();
	}
}
