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
 * Entity field class for boolean data type
 *
 * @package bitrix
 * @subpackage main
 */
class BooleanField extends ScalarField
{
	/**
	 * Value (false, true) equivalent map
	 * @var array
	 */
	protected $values;

	/**
	 * BooleanField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if (empty($parameters['values']))
		{
			$this->values = array(false, true);
		}
		else
		{
			$this->values = $parameters['values'];
		}

		$this->addSaveDataModifier(array($this, 'normalizeValue'));
	}

	/**
	 * @param $falseValue
	 * @param $trueValue
	 *
	 * @return $this
	 */
	public function configureStorageValues($falseValue, $trueValue)
	{
		$this->values = [$falseValue, $trueValue];
		return $this;
	}

	/**
	 * Short alias for configureStorageValues
	 *
	 * @param $falseValue
	 * @param $trueValue
	 *
	 * @return BooleanField
	 */
	public function configureValues($falseValue, $trueValue)
	{
		return $this->configureStorageValues($falseValue, $trueValue);
	}

	/**
	 * Convert true/false values to actual field values
	 * @param boolean|integer|string $value
	 * @return mixed
	 */
	public function normalizeValue($value)
	{
		if (
			(is_string($value) && ($value == '1' || $value == '0'))
			||
			(is_bool($value))
		)
		{
			$value = (int) $value;
		}
		elseif (is_string($value) && $value == 'true')
		{
			$value = 1;
		}
		elseif (is_string($value) && $value == 'false')
		{
			$value = 0;
		}

		if (is_integer($value) && ($value == 1 || $value == 0))
		{
			$value = $this->values[$value];
		}

		return $value;
	}

	/**
	 * Converts any possible value to strict boolean.
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function booleanizeValue($value)
	{
		if (is_bool($value))
		{
			return $value;
		}

		$normalizedValue = $this->normalizeValue($value);
		return (bool) array_search($normalizedValue, $this->values, true);
	}

	/**
	 * @return array|\Bitrix\Main\ORM\Fields\Validators\Validator[]|callback[]
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->validation === null)
		{
			$validators[] = new Validators\BooleanValidator;
		}

		return $validators;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function isValueEmpty($value)
	{
		return (strval($value) === '' && $value !== false);
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function cast($value)
	{
		if ($this->is_nullable && $value === null)
		{
			return $value;
		}

		if ($value instanceof SqlExpression)
		{
			return $value;
		}

		return $this->booleanizeValue($value);
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function convertValueFromDb($value)
	{
		return $this->booleanizeValue($value);
	}

	/**
	 * @param mixed $value
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
			: $this->getConnection()->getSqlHelper()->convertToDbString(
				$this->normalizeValue($value)
			);
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return '\\boolean';
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return '\\boolean';
	}
}
