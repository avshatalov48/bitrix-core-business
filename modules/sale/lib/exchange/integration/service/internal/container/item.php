<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Container;

use Bitrix\Main\Error;
use Bitrix\Sale\Exchange\Integration\Service\Internal\Entity\Base;

class Item
{
	protected $internalIndex;
	protected $entity;
	protected $error;
	protected $hasError;

	public function __construct(Base $entity)
	{
		$this->entity = $entity;
		$this->hasError = false;
	}

	public static function create(Base $entity)
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