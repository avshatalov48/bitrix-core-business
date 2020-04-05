<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\Type\DateTime;

/**
 * Entity field class for datetime data type
 * @package bitrix
 * @subpackage main
 */
class DatetimeField extends DateField
{
	/**
	 * DatetimeField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($name, $parameters = array())
	{
		ScalarField::__construct($name, $parameters);
	}

	/**
	 * @param mixed $value
	 *
	 * @return \Bitrix\Main\Type\Date|DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function cast($value)
	{
		if (!empty($value) && !($value instanceof DateTime))
		{
			return new DateTime($value);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return \Bitrix\Main\Type\Date|DateTime
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbDateTime($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbDateTime($value);
	}
}