<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

/**
 * Entity field class for string data type
 * @package bitrix
 * @subpackage main
 */
class StringField extends ScalarField
{
	/**
	 * Shortcut for Regexp validator
	 * @var null|string
	 */
	protected $format = null;

	/** @var int|null  */
	protected $size = null;

	/**
	 * StringField constructor.
	 *
	 * @param       $name
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if (!empty($parameters['format']))
		{
			$this->format = $parameters['format'];
		}
		if(isset($parameters['size']) && intval($parameters['size']) > 0)
		{
			$this->size = intval($parameters['size']);
		}
	}

	/**
	 * @param $format
	 *
	 * @return $this
	 */
	public function configureFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 * Shortcut for Regexp validator
	 * @return null|string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @return array|Validators\Validator[]|callback[]
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->format !== null)
		{
			$validators[] = new Validators\RegExpValidator($this->format);
		}

		return $validators;
	}

	/**
	 * @param $size
	 *
	 * @return $this
	 */
	public function configureSize($size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * Returns the size of the field in a database (in characters).
	 * @return int|null
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function cast($value)
	{
		$value = (string) $value;

		if ($this->size !== null)
		{
			$value = mb_substr($value, 0, $this->size);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueFromDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertFromDbString($value);
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbString($value);
	}
}