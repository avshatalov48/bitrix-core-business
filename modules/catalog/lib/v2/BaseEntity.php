<?php

namespace Bitrix\Catalog\v2;

use Bitrix\Catalog\v2\Fields\FieldStorage;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;
use Bitrix\Catalog\v2\Fields\TypeCasters\NullTypeCaster;
use Bitrix\Main\Result;

/**
 * Class BaseEntity
 *
 * @package Bitrix\Catalog\v2
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseEntity
{
	/** @var \Bitrix\Catalog\v2\RepositoryContract */
	protected $entityRepository;

	/** @var \Bitrix\Catalog\v2\Fields\FieldStorage */
	private $fieldStorage;
	/** @var \Bitrix\Catalog\v2\BaseCollection */
	private $parentCollection;

	// ToDo do we need $repository for every base entity?
	public function __construct(RepositoryContract $repository = null)
	{
		$this->entityRepository = $repository;
	}

	protected function getFieldStorage(): FieldStorage
	{
		if ($this->fieldStorage === null)
		{
			$this->fieldStorage = $this->createFieldStorage();
		}

		return $this->fieldStorage;
	}

	protected function createFieldStorage(): FieldStorage
	{
		$fieldMap = $this->getFieldsMap();

		if ($fieldMap === null)
		{
			$typeCaster = new NullTypeCaster();
		}
		else
		{
			$typeCaster = new MapTypeCaster($fieldMap);
		}

		return new FieldStorage($typeCaster);
	}

	public function initFields(array $fields): self
	{
		$this->getFieldStorage()->initFields($fields);

		return $this;
	}

	public function setParentCollection(?BaseCollection $collection): self
	{
		$this->parentCollection = $collection;

		return $this;
	}

	public function getParentCollection(): ?BaseCollection
	{
		return $this->parentCollection;
	}

	public function getParent(): ?self
	{
		$collection = $this->getParentCollection();

		if ($collection)
		{
			return $collection->getParent();
		}

		return null;
	}

	public function getHash(): string
	{
		return spl_object_hash($this);
	}

	public function setField(string $name, $value): self
	{
		$this->getFieldStorage()->setField($name, $value);

		return $this;
	}

	public function getField(string $name)
	{
		return $this->getFieldStorage()->getField($name);
	}

	// ToDo make map to execute set{$name} instead of setField($name) to check each field limitations? e.g. price with setFields is string instead of float
	public function setFields(array $fields): self
	{
		foreach ($fields as $name => $value)
		{
			$this->setField($name, $value);
		}

		return $this;
	}

	public function getFields(): array
	{
		return $this->getFieldStorage()->toArray();
	}

	public function getChangedFields(): array
	{
		return array_intersect_key($this->getFields(), $this->getFieldStorage()->getChangedFields());
	}

	public function hasChangedFields(): bool
	{
		return $this->getFieldStorage()->hasChangedFields();
	}

	public function isChanged(): bool
	{
		if ($this->hasChangedFields())
		{
			return true;
		}

		foreach ($this->getChildCollections() as $childCollection)
		{
			if ($childCollection->isChanged())
			{
				return true;
			}
		}

		return false;
	}

	public function isNew(): bool
	{
		return $this->getId() === null;
	}

	public function getId()
	{
		return (int)$this->getField('ID') ?: null;
	}

	public function setId(int $id): self
	{
		return $this->setField('ID', $id);
	}

	public function remove(): self
	{
		$collection = $this->getParentCollection();

		if ($collection)
		{
			$collection->remove($this);
		}

		return $this;
	}

	public function save(): Result
	{
		if ($parent = $this->getParent())
		{
			return $parent->save();
		}

		return $this->saveInternal();
	}

	protected function getFieldsMap(): ?array
	{
		return null;
	}

	/**
	 * ToDo is it a BaseEntity method? do all children need it? e.g. Property - doesn't
	 *
	 * @return \Bitrix\Main\Result
	 * @internal
	 */
	public function saveInternal(): Result
	{
		$result = new Result();

		if ($this->hasChangedFields())
		{
			$res = $this->saveInternalEntity();

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		if ($result->isSuccess())
		{
			foreach ($this->getChildCollections() as $childCollection)
			{
				$res = $childCollection->saveInternal();

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	protected function saveInternalEntity(): Result
	{
		$result = $this->entityRepository->save($this);

		if ($result->isSuccess())
		{
			$this->clearChangedFields();
		}

		return $result;
	}

	/**
	 * @internal
	 */
	public function deleteInternal(): Result
	{
		$result = $this->entityRepository->delete($this);

		if ($result->isSuccess())
		{
			foreach ($this->getChildCollections(true) as $childCollection)
			{
				$res = $childCollection->deleteInternal();

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param bool $initCollections
	 * @return \Generator|\Bitrix\Catalog\v2\BaseCollection[]
	 */
	final protected function getChildCollections(bool $initCollections = false): \Generator
	{
		$collectionPostfix = 'Collection';
		$collectionPostfixLength = mb_strlen($collectionPostfix);
		$parentCollection = "parent{$collectionPostfix}";

		foreach ((new \ReflectionObject($this))->getProperties() as $property)
		{
			$propertyName = $property->getName();

			if (
				$propertyName !== $parentCollection
				&& mb_substr($propertyName, -$collectionPostfixLength) === $collectionPostfix
			)
			{
				$property->setAccessible(true);
				$value = $property->getValue($this);

				if ($value === null && $initCollections)
				{
					$propertyGetter = "get{$propertyName}";

					if (is_callable([$this, $propertyGetter]))
					{
						$value = $this->$propertyGetter();
					}
				}

				if ($value instanceof BaseCollection)
				{
					yield $value;
				}
			}
		}
	}

	/**
	 * @return $this
	 * @internal
	 */
	public function clearChangedFields(): self
	{
		$this->getFieldStorage()->clearChanged();

		return $this;
	}
}