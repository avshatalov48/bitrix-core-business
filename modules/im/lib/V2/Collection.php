<?php

namespace Bitrix\Im\V2;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Im\V2\Service\Context;

/**
 * @template T
 * @extends Registry<T>
 */
abstract class Collection extends Registry implements ActiveRecordCollection
{
	protected ?ORM\Objectify\Collection $dataEntityCollection = null;

	// temporary entity id in registry
	protected int $newEntityTmpId = 0;

	public function __construct($source = null)
	{
		parent::__construct();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	/**
	 * Returns ORM tablet class name.
	 * @return string|DataManager
	 */
	final public static function getDataClass(): string
	{
		return static::getCollectionElementClass()::getDataClass();
	}

	/**
	 * Returns collection item's  class name.
	 * @return string|ActiveRecord
	 */
	abstract public static function getCollectionElementClass(): string;

	/**
	 * @param array $filter
	 * @param array $order
	 * @param int|null $limit
	 * @param Context|null $context
	 * @return static
	 */
	abstract public static function find(array $filter, array $order, ?int $limit = null, ?Context $context = null): self;

	/**
	 * Append collection with new item.
	 * @param ActiveRecord $entry
	 * @return static
	 * @throws ArgumentTypeException
	 */
	public function add(ActiveRecord $entry): self
	{
		$collectionElementClass = static::getCollectionElementClass();

		if (!($entry instanceof $collectionElementClass))
		{
			$entryClass = \get_class($entry);
			throw new ArgumentTypeException("Entry is instance of {$entryClass}, but collection support {$collectionElementClass}");
		}

		if ($entry instanceof RegistryEntry)
		{
			$entry->setRegistry($this);
		}

		if ($entry->getPrimaryId())
		{
			parent::offsetSet($entry->getPrimaryId(), $entry);
		}
		else
		{
			$this->newEntityTmpId --;
			parent::offsetSet($this->newEntityTmpId, $entry);
		}

		return $this;
	}

	/**
	 * Alias to add method.
	 * @param $offset
	 * @param $entry
	 * @return void
	 */
	public function offsetSet($offset, $entry): void
	{
		$collectionElementClass = static::getCollectionElementClass();

		if (!($entry instanceof $collectionElementClass))
		{
			$entryClass = \get_class($entry);
			throw new ArgumentTypeException("Entry is instance of {$entryClass}, but collection support {$collectionElementClass}");
		}

		if ($offset === null)
		{
			if ($entry->getPrimaryId())
			{
				$offset = $entry->getPrimaryId();
			}
			else
			{
				$this->newEntityTmpId --;
				$offset = $this->newEntityTmpId;
			}
		}

		parent::offsetSet($offset, $entry);
	}

	/**
	 * @return Result
	 */
	protected function prepareFields(): Result
	{
		$result = new Result;

		$this->dataEntityCollection = null; //Resetting the collection of entities before filling it
		$dataEntity = $this->getDataEntityCollection();

		/** @var ActiveRecord $entity */
		foreach ($this as $entity)
		{
			if ($entity->isDeleted())
			{
				continue;
			}

			$resultFill = $entity->prepareFields();

			if (!$resultFill->isSuccess())
			{
				$result->addErrors($resultFill->getErrors());
				continue;
			}

			$dataEntity->add($entity->getDataEntity());
		}

		return $result;
	}

	/**
	 * @return int[]
	 */
	public function getPrimaryIds(): array
	{
		$ids = [];

		/** @var ActiveRecord $item */
		foreach ($this as $item)
		{
			if ($item->getPrimaryId())
			{
				$ids[] = $item->getPrimaryId();
			}
		}

		return $ids;
	}

	/**
	 * @param int[]|array|ORM\Objectify\Collection $source
	 * @return Result
	 */
	public function load($source): Result
	{
		if (is_array($source))
		{
			if ($this->isArrayOfIds($source))
			{
				return $this->initByArrayOfPrimary($source);
			}

			return $this->initByArray($source);
		}

		if ($source instanceof ORM\Objectify\Collection)
		{
			return $this->initByEntitiesCollection($source);
		}

		return (new Result())->addError(new Error(Error::NOT_FOUND));
	}


	/**
	 * @param ORM\Objectify\Collection $entityCollection
	 * @return self
	 */
	protected function setDataEntityCollection(ORM\Objectify\Collection $entityCollection): self
	{
		$this->dataEntityCollection = $entityCollection;
		return $this;
	}

	/**
	 * Before external call, call prepare method to update the state of the entity @see BaseLinkCollection::prepareFields()
	 * @return ORM\Objectify\Collection
	 */
	public function getDataEntityCollection(): ORM\Objectify\Collection
	{
		if ($this->dataEntityCollection === null)
		{
			/** @var DataManager $dataClass */
			$dataClass = static::getDataClass();
			/** @var ORM\Objectify\Collection $entityCollectionClass */
			$entityCollectionClass = $dataClass::getCollectionClass();
			$this->dataEntityCollection = new $entityCollectionClass;
		}

		return $this->dataEntityCollection;
	}

	public function save(bool $isGroupSave = false): Result
	{
		$result = $this->prepareFields();

		if ($result->isSuccess())
		{
			if ($isGroupSave)
			{
				$resultSave = $this->getDataEntityCollection()->save(true);
				if (!$resultSave->isSuccess())
				{
					$result->addErrors($resultSave->getErrors());
				}
			}
			else
			{
				$index = [];
				foreach ($this as $inx => $entity)
				{
					$index[] = $inx;
				}

				/** @var ActiveRecord $entity */
				foreach ($index as $inx)
				{
					$entity = $this[$inx];
					if ($entity instanceof RegistryEntry)
					{
						$entity->setRegistry($this);
					}
					$resultSave = $entity->save();
					if (!$resultSave->isSuccess())
					{
						$result->addErrors($resultSave->getErrors());
					}
					elseif ($inx < 0 && isset($this[$entity->getPrimaryId()]))
					{
						unset($this[$inx]);// remove temporary object link from registry
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Delete from the database all entities that are in the registry and have an id
	 * @return Result
	 */
	public function delete(): Result
	{
		$result = new Result();

		$idsToDelete = $this->getPrimaryIds();
		if (method_exists(static::getDataClass(), 'deleteByFilter'))
		{
			if (empty($idsToDelete))
			{
				return $result;
			}

			$primaryField = static::getPrimaryFieldName();
			static::getDataClass()::deleteByFilter(
				[
					"={$primaryField}" => $idsToDelete
				]
			);

			return $result;
		}

		foreach ($idsToDelete as $idToDelete)
		{
			$deleteResult = static::getDataClass()::delete($idToDelete);
			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function hasUnsaved(): bool
	{
		/** @var ActiveRecord $entity */
		foreach ($this as $entity)
		{
			if ($entity->getPrimaryId() === null)
			{
				return true;
			}
			if ($entity->isChanged())
			{
				return true;
			}
			if ($entity->isDeleted())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param ORM\Objectify\Collection $entitiesCollection
	 * @return Result
	 * @throws ArgumentTypeException
	 */
	protected function initByEntitiesCollection(ORM\Objectify\Collection $entitiesCollection): Result
	{
		$collectionClass = static::getDataClass()::getCollectionClass();

		if (!($entitiesCollection instanceof $collectionClass))
		{
			$entryClass = \get_class($entitiesCollection);
			throw new ArgumentTypeException("Entry is instance of {$entryClass}, but collection support {$collectionClass}");
		}

		$this->setDataEntityCollection($entitiesCollection);

		return $this->initForEach($entitiesCollection);
	}

	/**
	 * @param array<array> $items
	 * @return Result
	 */
	protected function initByArray(array $items): Result
	{
		return $this->initForEach($items);
	}

	/**
	 * @param int[] $ids
	 * @return Result
	 */
	protected function initByArrayOfPrimary(array $ids): Result
	{
		$primaryField = static::getPrimaryFieldName();

		if (empty($ids))
		{
			return new Result();
		}

		/** @var ORM\Objectify\Collection $entitiesCollection */
		$entitiesCollection = static::getDataClass()::query()
			->setSelect(['*'])
			->whereIn($primaryField, $ids)
			->fetchCollection()
		;

		return $this->initByEntitiesCollection($entitiesCollection);
	}

	/**
	 * @param array|ORM\Objectify\Collection $entitiesCollection
	 * @return Result
	 */
	private function initForEach($entitiesCollection): Result
	{
		$result = new Result();
		$itemClass = static::getCollectionElementClass();

		foreach ($entitiesCollection as $entity)
		{
			$item = new $itemClass;
			$loadResult = $item->load($entity);

			if (!$loadResult->isSuccess())
			{
				$result->addErrors($loadResult->getErrors());
			}
			elseif ($item instanceof RegistryEntry)
			{
				$item->setRegistry($this);
			}
		}

		return $result;
	}

	private function isArrayOfIds(array $array): bool
	{
		foreach ($array as $key => $value)
		{
			if (!is_int($key) || !is_int($value))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getPrimaryFieldName(): string
	{
		$primaryField = static::getDataClass()::getEntity()->getPrimary();

		if (!is_scalar($primaryField))
		{
			throw new \Bitrix\Main\SystemException('Do not support composite primary keys');
		}

		return $primaryField;
	}
}
