<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Container;



use Bitrix\Main\Error;

class Item
{
	protected $internalIndex;
	protected $entity;
	protected $error;
	protected $hasError;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public static function create(Entity $entity)
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

	/**
	 * @return Error
	 */
	public function getError()
	{
		return $this->error;
	}

	public function setError(Error $error)
	{
		$this->hasError = true;
		$this->error = $error;
	}

	public function hasError()
	{
		return $this->hasError;
	}
}