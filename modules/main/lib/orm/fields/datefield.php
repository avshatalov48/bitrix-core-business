<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main;
use Bitrix\Main\Type;

/**
 * Entity field class for date data type
 * @package bitrix
 * @subpackage main
 */
class DateField extends ScalarField
{
	/**
	 * DateField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws Main\SystemException
	 */
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$this->addFetchDataModifier(array($this, 'assureValueObject'));
	}

	/**
	 * @return array|\Bitrix\Main\ORM\Fields\Validators\Validator[]|callback[]
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->validation === null)
		{
			$validators[] = new Validators\DateValidator;
		}

		return $validators;
	}

	/**
	 * @param $value
	 *
	 * @return Type\Date
	 * @throws Main\ObjectException
	 */
	public function assureValueObject($value)
	{
		if ($value instanceof Type\DateTime)
		{
			// oracle sql helper returns datetime instead of date - it doesn't see the difference
			$value = new Type\Date(
				$value->format(Main\UserFieldTable::MULTIPLE_DATE_FORMAT),
				Main\UserFieldTable::MULTIPLE_DATE_FORMAT
			);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return Type\Date
	 * @throws Main\ObjectException
	 */
	public function cast($value)
	{
		if (!empty($value) && !($value instanceof Type\Date))
		{
			return new Type\Date($value);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return Type\Date
	 * @throws Main\ObjectException
	 * @throws Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbDate($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbDate($value);
	}
}