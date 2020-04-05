<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

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
	 * @param array $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws SystemException
	 */
	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if (isset($parameters['values']))
		{
			$this->values = $parameters['values'];
		}
	}

	public function postInitialize()
	{
		if (!is_array($this->values))
		{
			throw new SystemException(sprintf(
				'Parameter "values" for %s field in `%s` entity should be an array',
				$this->name, $this->entity->getDataClass()
			));
		}

		if (empty($this->values))
		{
			throw new SystemException(sprintf(
				'Required parameter "values" for %s field in `%s` entity is not found',
				$this->name, $this->entity->getDataClass()
			));
		}
	}

	/**
	 * @param $values
	 *
	 * @return $this
	 */
	public function configureValues($values)
	{
		$this->values = $values;
		return $this;
	}

	/**
	 * @return array|Validators\Validator[]|callback[]
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->validation === null)
		{
			$validators[] = new Validators\EnumValidator;
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
	 * @return mixed
	 */
	public function cast($value)
	{
		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function convertValueFromDb($value)
	{
		return $value;
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