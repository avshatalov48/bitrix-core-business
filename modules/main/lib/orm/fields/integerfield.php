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
 * Entity field class for integer data type
 *
 * @package bitrix
 * @subpackage main
 */
class IntegerField extends ScalarField
{
	/** @var int */
	protected $size = 4;

	/**
	 * IntegerField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if(isset($parameters['size']) && intval($parameters['size']) > 0)
		{
			$this->size = intval($parameters['size']);
		}
	}

	/**
	 * @param $size
	 *
	 * @return $this
	 */
	public function configureSize($size)
	{
		$this->size = (int)$size;
		return $this;
	}

	/**
	 * Returns the size of the field in a database (in bits).
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param mixed $value
	 *
	 * @return SqlExpression|int
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
		if ($value instanceof SqlExpression)
		{
			return $value;
		}

		return $value === null && $this->is_nullable
			? $value
			: $this->getConnection()->getSqlHelper()->convertToDbInteger($value, $this->size);
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return $this->getNullableTypeHint('\\int');
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return $this->getNullableTypeHint('\\int');
	}
}