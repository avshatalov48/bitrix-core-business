<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Type;
use Bitrix\Main\Type\Date;

/**
 * Entity field class for date data type
 * @package bitrix
 * @subpackage main
 */
class DateField extends ScalarField
{
	protected $format = null;

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

	public function configureFormat($format)
	{
		$this->format = $format;

		return $this;
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
	 * @return SqlExpression|Date
	 * @throws Main\ObjectException
	 */
	public function cast($value)
	{
		if ($value instanceof SqlExpression)
		{
			return $value;
		}

		if (!empty($value) && !($value instanceof Type\Date))
		{
			return new Type\Date($value, $this->format);
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
		if ($value instanceof SqlExpression)
		{
			return $value;
		}

		try
		{
			return $value === null && $this->is_nullable
				? $value
				: $this->getConnection()->getSqlHelper()->convertToDbDate($value);
		}
		catch (ArgumentTypeException $e)
		{
			throw new ArgumentException(
				"Type error in `{$this->name}` of `{$this->entity->getFullName()}`: ".$e->getMessage()
			);
		}
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return '\\'.Date::class;
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return '\\'.Date::class;
	}
}