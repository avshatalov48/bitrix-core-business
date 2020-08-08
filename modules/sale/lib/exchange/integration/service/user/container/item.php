<?php
namespace Bitrix\Sale\Exchange\Integration\Service\User\Container;


use Bitrix\Sale\Exchange\Integration\Service\User\Entity;

class Item
{
	protected $internalIndex;
	protected $entity;

	public function __construct(Entity\Base $entity)
	{
		$this->entity = $entity;
	}

	public static function create(Entity\Base $entity)
	{
		return new static($entity);
	}

	public function setInternalIndex($internalIndex)
	{
		$this->internalIndex = $internalIndex;
		return $this;
	}

	public function getInternalIndex()
	{
		return $this->internalIndex;
	}

	public function getEntity()
	{
		return $this->entity;
	}
}