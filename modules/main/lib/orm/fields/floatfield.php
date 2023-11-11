<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\DB\SqlExpression;

/**
 * Entity field class for enum data type
 *
 * @package bitrix
 * @subpackage main
 */
class FloatField extends ScalarField
{
	/** @var int|null */
	protected $precision;

	/** @var int|null */
	protected $scale;

	/**
	 * FloatField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if(isset($parameters['scale']))
		{
			$this->scale = intval($parameters['scale']);
		}

		if(isset($parameters['precision']))
		{
			$this->precision = intval($parameters['precision']);
		}
	}

	/**
	 * @param int $precision
	 *
	 * @return $this
	 */
	public function configurePrecision($precision)
	{
		$this->precision = (int) $precision;
		return $this;
	}

	/**
	 * @param $scale
	 *
	 * @return $this
	 */
	public function configureScale($scale)
	{
		$this->scale = (int) $scale;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPrecision()
	{
		return $this->precision;
	}

	/**
	 * @return int|null
	 */
	public function getScale()
	{
		return $this->scale;
	}

	/**
	 * @param mixed $value
	 *
	 * @return float|mixed
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

		$value = doubleval($value);

		if ($this->scale !== null)
		{
			$value = round($value, $this->scale);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return float|mixed
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbFloat($value);
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
			: $this->getConnection()->getSqlHelper()->convertToDbFloat($value);
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return $this->getNullableTypeHint('\\float');
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return $this->getNullableTypeHint('\\float');
	}
}