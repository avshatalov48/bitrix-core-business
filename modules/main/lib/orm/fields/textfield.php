<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\DB\SqlExpression;

/**
 * Entity field class for text data type
 *
 * @package bitrix
 * @subpackage main
 */
class TextField extends StringField
{
	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbText($value);
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		if ($value instanceof SqlExpression)
		{
			return $value;
		}
		
		return $value === null && $this->is_nullable
			? $value
			: $this->getConnection()->getSqlHelper()->convertToDbText($value);
	}
}