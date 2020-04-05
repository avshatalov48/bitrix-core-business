<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

/**
 * Entity field class for integer data type
 * @package bitrix
 * @subpackage main
 */
class IntegerField extends ScalarField
{
	/**
	 * @param mixed $value
	 *
	 * @return int
	 */
	public function cast($value)
	{
		return (int) $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return int
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbInteger($value);
	}

	/**
	 * @param int $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbInteger($value);
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return '\\int';
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return '\\int';
	}
}