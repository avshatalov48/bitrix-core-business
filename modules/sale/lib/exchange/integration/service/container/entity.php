<?php


namespace Bitrix\Sale\Exchange\Integration\Service\Container;


use Bitrix\Sale\Internals\Fields;

abstract class Entity
{
	protected $fields;

	public function __construct(array $values = null)
	{
		$this->fields = new Fields($values);
	}

	public function getId()
	{
		return $this->fields->get('ID');
	}
	public function setId($value)
	{
		$this->fields->set('ID', $value);
		return $this;
	}

	public function getFieldsValues()
	{
		return $this->fields->getValues();
	}

	abstract static public function createFromArray(array $fields);
}