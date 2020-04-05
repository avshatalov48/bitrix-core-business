<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

/**
 * Entity field class for enum data type
 * @package bitrix
 * @subpackage main
 */
class FloatField extends ScalarField
{
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
		return $this->getConnection()->getSqlHelper()->convertToDbFloat($value);
	}

	/**
	 * @return string
	 */
	public function getGetterTypeHint()
	{
		return '\\float';
	}

	/**
	 * @return string
	 */
	public function getSetterTypeHint()
	{
		return '\\float';
	}
}