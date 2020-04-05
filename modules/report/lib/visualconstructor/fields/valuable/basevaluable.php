<?php
namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

use Bitrix\Report\VisualConstructor\Fields\Base;

/**
 * Class BaseValuable
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
abstract class BaseValuable extends Base
{
	private $value;
	private $defaultValue;
	private $name;

	/**
	 * Constructor for valuable fields.
	 *
	 * @param string $key Unique key.
	 */
	public function __construct($key)
	{
		$this->setKey($key);
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * Defaul value setter.
	 *
	 * @param mixed $defaultValue Value which use as default.
	 * @return void
	 */
	public function setDefaultValue($defaultValue)
	{
		$this->defaultValue = $defaultValue;
	}


	/**
	 * Return value if exist, or return default value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value !== null ?  $this->value : $this->getDefaultValue();
	}

	/**
	 * Value setter.
	 *
	 * @param mixed $value Value set as value of field.
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = $this->normalise($value);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name === null ? $this->getKey() : $this->name;
	}

	/**
	 * Field name setter.
	 *
	 * @param string $name Name which use in rendered field.
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * Normalise value field before save.
	 *
	 * @param mixed $config Config which will pass to db.
	 * @return mixed
	 */
	protected function normalise($config)
	{
		return $config;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		$id = parent::getId();
		if ($id === null)
		{
			$id = str_replace('][', '_', $this->getName());
			$id = str_replace('[', '_', $id);
			$id = str_replace(']', '', $id);
		}

		return $id;
	}
}