<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Type\DateTime;

/**
 * Entity field class for datetime data type
 * @package bitrix
 * @subpackage main
 */
class DatetimeField extends DateField
{
	/** @var bool */
	protected $useTimezone = true;

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
	 * @param bool $use
	 * @return $this
	 */
	public function configureUseTimezone($use = true)
	{
		$this->useTimezone = (bool) $use;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getFetchDataModifiers()
	{
		$modifiers = parent::getFetchDataModifiers();

		if (!$this->useTimezone)
		{
			$modifiers[] = [__CLASS__, 'disableTimezoneFetchModifier'];
		}

		return $modifiers;
	}

	/**
	 * @param mixed $value
	 *
	 * @return SqlExpression|\Bitrix\Main\Type\Date|DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function cast($value)
	{
		if ($value instanceof SqlExpression)
		{
			return $value;
		}

		if (!empty($value) && !($value instanceof DateTime))
		{
			$value = new DateTime($value);
		}

		if ($value instanceof DateTime)
		{
			$this->useTimezone
				? $value->enableUserTime()
				: $value->disableUserTime();
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
		if ($value instanceof SqlExpression)
		{
			return $value;
		}

		try
		{
			return $value === null && $this->is_nullable
				? $value
				: $this->getConnection()->getSqlHelper()->convertToDbDateTime($value);
		}
		catch (ArgumentTypeException $e)
		{
			throw new ArgumentException(
				"Type error in `{$this->name}` of `{$this->entity->getFullName()}`: ".$e->getMessage()
			);
		}
	}

	/**
	 * @see getFetchDataModifiers()
	 *
	 * @param DateTime $time
	 * @return DateTime
	 */
	public static function disableTimezoneFetchModifier($time)
	{
		if ($time !== null)
		{
			$time->disableUserTime();
		}

		return $time;
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return '\\'.DateTime::class;
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return '\\'.DateTime::class;
	}
}