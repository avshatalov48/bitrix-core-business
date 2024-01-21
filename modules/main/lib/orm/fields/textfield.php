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
	/** @var bool */
	protected $long = false;

	/**
	 * TextField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$this->long = isset($parameters['long']) && (bool)$parameters['long'];
	}

	/**
	 * @param $long
	 *
	 * @return $this
	 */
	public function configureLong($long = true)
	{
		$this->long = (bool)$long;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isLong()
	{
		return $this->long;
	}

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

	public function isValueEmpty($value)
	{
		if ($this->isSerialized() && is_array($value))
		{
			return false;
		}

		return parent::isValueEmpty($value);
	}
}