<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Entity;


use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\Internals\Fields;

abstract class Base
{
	protected $fields;
	protected $relation;
	protected $client;

	public function __construct(array $values = null)
	{
		$this->fields = new Fields($values);
	}

	abstract function getType();

	public function setOwnerTypeId($value)
	{
		$this->fields->set('OWNER_TYPE_ID', $value);
		return $this;
	}

	public function getOwnerTypeId()
	{
		return $this->fields->get('OWNER_TYPE_ID');
	}

	public function setOwnerId($value)
	{
		$this->fields->set('OWNER_ID', $value);
		return $this;
	}

	public function getOwnerId()
	{
		return $this->fields->get('OWNER_ID');
	}

	public function setOriginatorId($value)
	{
		$this->fields->set('ORIGINATOR_ID', $value);
		return $this;
	}

	public function setOriginId($value)
	{
		$this->fields->set('ORIGIN_ID', $value);
		return $this;
	}

	public function getFieldsValues()
	{
		return $this->fields->getValues();
	}

	public function setRelation(Integration\Relation\Relation $relation)
	{
		$this->relation = $relation;
	}

	/**
	 * @return Integration\Relation\Relation
	 */
	public function getRelation()
	{
		return $this->relation;
	}

	public function hasRelation()
	{
		return ($this->relation instanceof Integration\Relation\Relation && $this->relation->getDestinationEntityId()>0);
	}
}