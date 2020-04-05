<?php

namespace Bitrix\Main\UserField\Internal;

use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * Class Type
 * @deprecated
 * @see TypeDataManager
 * @method string getName()
 * @method string getTableName()
 */
class Type extends EntityObject
{
	protected function getFactory(): TypeFactory
	{
		/** @var TypeDataManager $dataClass */
		$dataClass = static::$dataClass;
		return $dataClass::getFactory();
	}
}