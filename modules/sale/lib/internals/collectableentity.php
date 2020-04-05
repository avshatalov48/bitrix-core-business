<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Result;

/**
 * Class CollectableEntity
 * @package Bitrix\Sale\Internals
 */
abstract class CollectableEntity
	extends Internals\Entity
{
	/** @var EntityCollection */
	protected $collection;

	protected $internalIndex = null;

	protected $isClone = false;

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 *
	 * @return Result
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		$collection = $this->getCollection();
		return $collection->onItemModify($this, $name, $oldValue, $value);
	}

	/**
	 * @param EntityCollection $collection
	 */
	public function setCollection(EntityCollection $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * @return EntityCollection
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function delete()
	{
		$collection = $this->getCollection();
		if (!$collection)
		{
			throw new Main\ObjectNotFoundException('Entity "CollectableEntity" not found');
		}

		/** @var Result $r */
		$collection->deleteItem($this->getInternalIndex());

		return new Result();
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @throws Main\ArgumentTypeException
	 */
	public function setInternalIndex($index)
	{
		$this->internalIndex = $index;
	}

	/**
	 * @return null|int
	 */
	public function getInternalIndex()
	{
		return $this->internalIndex;
	}

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	public function isStartField($isMeaningfulField = false)
	{
		$parent = $this->getCollection();
		if ($parent == null)
			return false;

		return $parent->isStartField($isMeaningfulField);
	}

	/**
	 * @return bool
	 */
	public function clearStartField()
	{
		$parent = $this->getCollection();
		if ($parent == null)
			return false;

		return $parent->clearStartField();
	}

	/**
	 * @return bool
	 */
	public function hasMeaningfulField()
	{
		$parent = $this->getCollection();
		if ($parent == null)
			return false;

		return $parent->hasMeaningfulField();
	}

	public function doFinalAction($hasMeaningfulField = false)
	{
		$parent = $this->getCollection();
		if ($parent == null)
		{
			return false;
		}

		return $parent->doFinalAction($hasMeaningfulField);
	}

	/**
	 * @param bool|false $value
	 * @return bool
	 */
	public function setMathActionOnly($value = false)
	{
		$parent = $this->getCollection();
		if ($parent == null)
		{
			return false;
		}

		return $parent->setMathActionOnly($value);
	}

	/**
	 * @return bool
	 */
	public function isMathActionOnly()
	{
		$parent = $this->getCollection();
		if ($parent == null)
			return false;

		return $parent->isMathActionOnly();
	}

	/**
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return CollectableEntity
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$collectableEntity = clone $this;
		$collectableEntity->isClone = true;

		/** @var Internals\Fields $fields */
		if ($fields = $this->fields)
		{
			$collectableEntity->fields = $fields->createClone($cloneEntity);
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $collectableEntity;
		}

		if ($collection = $this->getCollection())
		{
			if (!$cloneEntity->contains($collection))
			{
				$cloneEntity[$collection] = $collection->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($collection))
			{
				$collectableEntity->collection = $cloneEntity[$collection];
			}
		}

		return $collectableEntity;
	}
}