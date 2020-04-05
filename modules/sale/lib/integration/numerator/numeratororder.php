<?php
namespace Bitrix\Sale\Integration\Numerator;
use Bitrix\Main\Numerator\Numerator;
use Bitrix\Sale\Registry;

/**
 * Class NumeratorOrder
 * @package Bitrix\Sale\Integration\Numerator
 */
class NumeratorOrder
{
	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function isUsedNumeratorForOrder()
	{
		return boolval(Numerator::getOneByType(Registry::ENTITY_ORDER));
	}
}