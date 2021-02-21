<?php

namespace Bitrix\Main\ORM\Fields;

/**
 * Entity field class for decimal data type
 * @package bitrix
 * @subpackage main
 */
class DecimalField extends FloatField
{
	/** @var int|null */
	protected $precision;

	/**
	 * DecimalField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($name, $parameters = [])
	{
		parent::__construct($name, $parameters);

		if(isset($parameters['precision']))
		{
			$this->precision = intval($parameters['precision']);
		}
	}

	/**
	 * @param $precision
	 *
	 * @return $this
	 */
	public function configurePrecision($precision)
	{
		$this->precision = (int) $precision;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPrecision()
	{
		return $this->precision;
	}
}