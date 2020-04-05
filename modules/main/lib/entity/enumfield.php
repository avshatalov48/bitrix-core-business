<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Entity;
use Bitrix\Main\SystemException;

/**
 * Entity field class for enum data type
 * @package bitrix
 * @subpackage main
 */
class EnumField extends ScalarField
{
	protected $values;

	/**
	 * EnumField constructor.
	 *
	 * @param       $name
	 * @param array $parameters
	 *
	 * @throws SystemException
	 */
	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if (empty($parameters['values']))
		{
			throw new SystemException(sprintf(
				'Required parameter "values" for %s field is not found',
				$this->name
			));
		}

		if (!is_array($parameters['values']))
		{
			throw new SystemException(sprintf(
				'Parameter "values" for %s field should be an array',
				$this->name
			));
		}


		$this->values = $parameters['values'];
	}

	/**
	 * @return array|Validator\Base[]|callback[]
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->validation === null)
		{
			$validators[] = new Validator\Enum;
		}

		return $validators;
	}

	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 * @throws SystemException
	 */
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbString($value);
	}
}