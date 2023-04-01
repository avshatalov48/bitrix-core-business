<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Sale;
use Bitrix\Main;

/**
 * Class EntityCollection
 * @package Bitrix\Sale\Internals
 */
abstract class EntityCollection
	extends CollectionBase
{
	private $index = -1;

	protected $isClone = false;

	protected $anyItemDeleted = false;
	protected $anyItemAdded = false;

	/**
	 * EntityCollection constructor.
	 */
	protected function __construct() {}

	/**
	 * @internal
	 *
	 * @param CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Sale\Result
	 */
	public function onItemModify(CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		return new Sale\Result();
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return mixed
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function deleteItem($index)
	{
		if (!isset($this->collection[$index]))
		{
			throw new Main\ArgumentOutOfRangeException("collection item index wrong");
		}

		$oldItem = $this->collection[$index];

		$eventManager = Main\EventManager::getInstance();
		$eventsList = $eventManager->findEventHandlers('sale', 'OnBeforeCollectionDeleteItem');
		if (!empty($eventsList))
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', 'OnBeforeCollectionDeleteItem', array(
				'COLLECTION' => $this->collection,
				'ENTITY' => $oldItem,
			));
			$event->send();
		}

		unset($this->collection[$index]);
		$this->setAnyItemDeleted(true);

		return $oldItem;
	}

	/**
	 * @param CollectableEntity $item
	 * @return CollectableEntity
	 * @throws Main\ArgumentTypeException
	 */
	protected function addItem(CollectableEntity $item)
	{
		$index = $this->createIndex();
		$item->setInternalIndex($index);

		$this->collection[$index] = $item;
		$this->setAnyItemAdded(true);

		$eventManager = Main\EventManager::getInstance();
		$eventsList = $eventManager->findEventHandlers('sale', 'OnCollectionAddItem');
		if (!empty($eventsList))
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', 'OnCollectionAddItem', array(
				'COLLECTION' => $this->collection,
				'ENTITY' => $item,
			));
			$event->send();
		}

		return $item;
	}

	/**
	 * @return int
	 */
	protected function createIndex()
	{
		$this->index++;
		return $this->index;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function clearCollection()
	{
		$this->callEventOnBeforeCollectionClear();

		/** @var CollectableEntity $item */
		foreach ($this->getDeletableItems() as $item)
		{
			$item->delete();
		}
	}

	/**
	 * @return array
	 */
	protected function getDeletableItems()
	{
		return $this->collection;
	}


	/**
	 * @return void
	 */
	protected function callEventOnBeforeCollectionClear()
	{
		$eventManager = Main\EventManager::getInstance();

		$eventsList = $eventManager->findEventHandlers('sale', 'OnBeforeCollectionClear');
		if (!empty($eventsList))
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', 'OnBeforeCollectionClear', array(
				'COLLECTION' => $this->collection,
			));
			$event->send();
		}
	}

	/**
	 * @param $id
	 *
	 * @return CollectableEntity|null
	 * @throws Main\ArgumentNullException
	 */
	public function getItemById($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException('id');
		}

		$index = $this->getIndexById($id);
		if ($index === null)
		{
			return null;
		}

		if (isset($this->collection[$index]))
		{
			return $this->collection[$index];
		}

		return null;
	}


	/**
	 * @param $id
	 *
	 * @return bool|int|null
	 * @throws Main\ArgumentNullException
	 */
	public function getIndexById($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException('id');
		}

		/** @var CollectableEntity $item */
		foreach ($this->collection as $item)
		{
			if ($item->getId() > 0 && $id == $item->getId())
			{
				return $item->getInternalIndex();
			}
		}
		return null;
	}

	/**
	 * @param $index
	 *
	 * @return CollectableEntity|null
	 * @throws Main\ArgumentNullException
	 */
	public function getItemByIndex($index)
	{
		if (intval($index) < 0)
		{
			throw new Main\ArgumentNullException('id');
		}

		/** @var CollectableEntity $item */
		foreach ($this->collection as $item)
		{
			if ($item->getInternalIndex() == $index)
			{
				return $item;
			}
		}
		return null;
	}

	/**
	 * @return Entity
	 */
	abstract protected function getEntityParent();

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	public function isStartField($isMeaningfulField = false)
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
		{
			return false;
		}

		return $parent->isStartField($isMeaningfulField);
	}

	/**
	 * @return bool
	 */
	public function clearStartField()
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
		{
			return false;
		}

		return $parent->clearStartField();
	}

	/**
	 * @return bool
	 */
	public function hasMeaningfulField()
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
		{
			return false;
		}

		return $parent->hasMeaningfulField();
	}

	/**
	 * @param bool $hasMeaningfulField
	 * @return Sale\Result
	 */
	public function doFinalAction($hasMeaningfulField = false)
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
		{
			return new Sale\Result();
		}

		return $parent->doFinalAction($hasMeaningfulField);
	}

	/**
	 * @return bool
	 */
	public function isMathActionOnly()
	{
		$parent = $this->getEntityParent();
		if ($parent === null)
		{
			return false;
		}

		return $parent->isMathActionOnly();
	}

	/**
	 * @param bool|false $value
	 * @return bool
	 */
	public function setMathActionOnly($value = false)
	{
		$parent = $this->getEntityParent();
		if ($parent == null)
		{
			return false;
		}

		return $parent->setMathActionOnly($value);
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		if (count($this->collection) > 0)
		{
			/** @var Entity $item */
			foreach ($this->collection as $item)
			{
				if ($item->isChanged())
				{
					return true;
				}
			}
		}

		return $this->isAnyItemDeleted() || $this->isAnyItemAdded();
	}

	/**
	 * @return Sale\Result
	 */
	public function verify()
	{
		return new Sale\Result();
	}

	/**
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}


	/**
	 * @return bool
	 */
	public function isAnyItemDeleted()
	{
		return $this->anyItemDeleted;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function setAnyItemDeleted($value)
	{
		return $this->anyItemDeleted = ($value === true);
	}

	/**
	 * @return bool
	 */
	public function isAnyItemAdded()
	{
		return $this->anyItemAdded;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function setAnyItemAdded($value)
	{
		return $this->anyItemAdded = ($value === true);
	}

	/**
	 * @internal
	 */
	public function clearChanged()
	{
		if (!empty($this->collection))
		{
			foreach ($this->collection as $entityItem)
			{
				if ($entityItem instanceof Entity)
				{
					$entityItem->clearChanged();
				}
			}
		}
	}

	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return EntityCollection
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$entityClone = clone $this;
		$entityClone->isClone = true;

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $entityClone;
		}

		/**
		 * @var int key
		 * @var CollectableEntity $entity
		 */
		foreach ($entityClone->collection as $key => $entity)
		{
			if (!$cloneEntity->contains($entity))
			{
				$cloneEntity[$entity] = $entity->createClone($cloneEntity);
			}

			$entityClone->collection[$key] = $cloneEntity[$entity];
		}

		return $entityClone;
	}
}
