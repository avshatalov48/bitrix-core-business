<?php

namespace Bitrix\Im\V2\Common;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Im\Model\EO_Chat;
use Bitrix\Im\Model\EO_Message;
use Bitrix\Im\Model\EO_MessageParam;
use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\RegistryEntry;

/**
 * Implementation of the interface @see \Bitrix\Im\V2\ActiveRecord
 */
trait ActiveRecordImplementation
{
	/** @var EntityObject|EO_Chat|EO_Message|EO_MessageParam */
	protected $dataObject;

	// Object changed flag
	protected bool $isChanged = true;

	// Object marked to drop
	protected bool $markedDrop = false;

	/**
	 * @return array<array>
	 * @throws NotImplementedException
	 */
	protected static function mirrorDataEntityFields(): array
	{
		throw new NotImplementedException;
		return [];
	}

	/**
	 * @return EntityObject
	 */
	public function getDataEntity(): EntityObject
	{
		if ($this->dataObject === null)
		{
			/**
			 * @var DataManager $dataClass
			 * @var EntityObject $entityObjectClass
			 */
			$dataClass = static::getDataClass();
			$entityObjectClass = $dataClass::getObjectClass();
			$this->dataObject = new $entityObjectClass;
		}

		return $this->dataObject;
	}

	/**
	 * @param EntityObject $dataObject
	 * @return static
	 */
	protected function setDataEntity(EntityObject $dataObject): self
	{
		$this->dataObject = $dataObject;
		return $this;
	}

	/**
	 * @param int|array|EntityObject $source
	 */
	public function load($source): Result
	{
		$result = new Result;

		if (is_numeric($source))
		{
			$source = (int)$source;
			/**
			 * @var DataManager $dataClass
			 * @var EntityObject $dataObject
			 */
			$dataClass = static::getDataClass();
			$dataObject = $dataClass::getByPrimary($source)->fetchObject();

			if (
				$dataObject instanceof EntityObject
				&& $dataObject->hasId()
			)
			{
				$result = $this->initByDataEntity($dataObject);
			}
			else
			{
				$result->addError(new Error(Error::NOT_FOUND));
			}
		}

		elseif ($source instanceof EntityObject)
		{
			$result = $this->initByDataEntity($source);
		}

		elseif (is_array($source))
		{
			$result = $this->initByArray($source);
		}
		else
		{
			$result->addError(new Error(Error::NOT_FOUND));
		}

		if ($result->isSuccess() && $this->getPrimaryId())
		{
			$this->markChanged(false);

			if (
				$this instanceof RegistryEntry
				&& $this->getRegistry()
			)
			{
				$this->getRegistry()[$this->getPrimaryId()] = $this;
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function initByDefault(): void
	{
		foreach (static::mirrorDataEntityFields() as $field)
		{
			if (
				!isset($field['primary'])
				&& !isset($field['alias'])
				&& isset($field['field'], $field['default'])
				&& !isset($this->{$field['field']})
				&& ($default = $field['default'])
				&& is_string($default)
				&& is_callable([$this, $default])
			)
			{
				$this->{$field['field']} = $this->$default();
			}
		}
	}

	/**
	 * @param EntityObject $dataObject
	 * @return Result
	 */
	protected function initByDataEntity(EntityObject $dataObject): Result
	{
		$result = new Result;

		$this->setDataEntity($dataObject);

		foreach (static::mirrorDataEntityFields() as $offset => $field)
		{
			if (isset($field['alias']) || !isset($field['field']))
			{
				continue;
			}
			if ($this->getDataEntity()->has($offset))
			{
				if (
					isset($field['loadFilter'])
					&& ($loadFilter = $field['loadFilter'])
					&& is_string($loadFilter)
					&& is_callable([$this, $loadFilter])
				)
				{
					$this->{$field['field']} = $this->$loadFilter($this->getDataEntity()->get($offset));
				}
				else
				{
					$this->{$field['field']} = $this->getDataEntity()->get($offset);
				}
			}
		}

		if ($result->isSuccess() && $this->getPrimaryId())
		{
			$this->markChanged(false);
		}

		return $result;
	}

	/**
	 * @param array $source
	 * @return Result
	 */
	protected function initByArray(array $source): Result
	{
		$result = new Result;
		$fields = static::mirrorDataEntityFields();

		foreach ($fields as $offset => $field)
		{
			if (isset($field['primary']))
			{
				if (isset($source[$offset]))
				{
					/**
					 * @var DataManager $dataClass
					 * @var EntityObject $entityObjectClass
					 */
					$dataClass = static::getDataClass();
					$entityObjectClass = $dataClass::getObjectClass();

					return $this->initByDataEntity($entityObjectClass::wakeUp($source));
				}
				break;
			}
		}

		$this->fill($source);

		if ($result->isSuccess() && $this->getPrimaryId())
		{
			$this->markChanged(false);
		}

		return $result;
	}

	public function prepareFields(): Result
	{
		$result = new Result;

		foreach (static::mirrorDataEntityFields() as $offset => $field)
		{
			if (isset($field['primary']) || isset($field['alias']))
			{
				continue;
			}

			if (
				isset($field['beforeSave'])
				&& ($beforeSave = $field['beforeSave'])
				&& is_string($beforeSave)
				&& is_callable([$this, $beforeSave])
			)
			{
				/** @var Result $check */
				$check = $this->$beforeSave();
				if (!$check->isSuccess())
				{
					$result->addErrors($check->getErrors());
					continue;
				}
			}

			if (isset($field['field'], $this->{$field['field']}))
			{
				if (
					isset($field['saveFilter'])
					&& ($saveFilter = $field['saveFilter'])
					&& is_string($saveFilter)
					&& is_callable([$this, $saveFilter])
				)
				{
					$this->getDataEntity()->set($offset, $this->$saveFilter($this->{$field['field']}));
				}
				else
				{
					$this->getDataEntity()->set($offset, $this->{$field['field']});
				}

				$this->markChanged($this->isChanged || $this->getDataEntity()->isChanged($offset));
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function save(): Result
	{
		$result = $this->prepareFields();
		if (!$result->isSuccess())
		{
			return $result;
		}
		if (!$this->isChanged())
		{
			return $result;
		}

		$saveResult = $this->getDataEntity()->save();
		if ($saveResult->isSuccess())
		{
			$this->updateState();

			$this->setPrimaryId((int)$saveResult->getId());

			if (
				$this instanceof RegistryEntry
				&& $this->getRegistry()
			)
			{
				$this->getRegistry()[$this->getPrimaryId()] = $this;
			}

			$this->markChanged(false);
		}
		else
		{
			$result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	protected function updateState(): Result
	{
		return $this->initByDataEntity($this->getDataEntity());
	}

	/**
	 * @return Result
	 */
	public function delete(): Result
	{
		$this->markDrop();
		$result = new Result;
		if ($this->getDataEntity()->hasId())
		{
			$deleteResult = $this->getDataEntity()->delete();
			if (!$deleteResult->isSuccess())
			{
				return $result->addErrors($deleteResult->getErrors());
			}
		}

		if (
			$this instanceof RegistryEntry
			&& $this->getRegistry()
		)
		{
			unset($this->getRegistry()[$this->getPrimaryId()]);
		}

		return $result;
	}

	/**
	 * Marks object changed.
	 * @return static
	 */
	public function markChanged(?bool $state = null): self
	{
		if ($state === null)
		{
			$this->isChanged = true;
		}
		else
		{
			$this->isChanged = $state;
		}
		return $this;
	}

	/**
	 * Tells true if object has been changed.
	 * @return bool
	 */
	public function isChanged(): bool
	{
		return $this->isChanged;
	}

	/**
	 * Marks object to drop on save.
	 * @return static
	 */
	public function markDrop(): self
	{
		$this->isChanged = false;
		$this->markedDrop = true;
		return $this;
	}

	/**
	 * Tells true if object marked to drop.
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->markedDrop;
	}

	/**
	 * Fills object's fields with provided values.
	 * @param array $source
	 * @return static
	 */
	public function fill(array $source): self
	{
		$fields = static::mirrorDataEntityFields();

		foreach ($fields as $offset => $field)
		{
			if (isset($source[$offset]))
			{
				if (isset($field['primary']))
				{
					continue;
				}
				if (isset($field['alias']))
				{
					$field = $fields[$field['alias']];
				}
				if (
					isset($field['set'])
					&& ($setter = $field['set'])
					&& is_string($setter)
					&& is_callable([$this, $setter])
				)
				{
					$this->$setter($source[$offset]);
				}
				elseif (isset($field['field']))
				{
					$this->{$field['field']} = $source[$offset];
				}
			}
		}

		return $this;
	}

	/**
	 * Returns object state as array.
	 * @return array
	 */
	public function toArray(): array
	{
		$result = [];
		$fields = static::mirrorDataEntityFields();

		foreach ($fields as $offset => $field)
		{
			if (isset($field['alias']))
			{
				continue;
			}
			if (
				isset($field['get'])
				&& ($getter = $field['get'])
				&& is_string($getter)
				&& is_callable([$this, $getter])
			)
			{
				$value = $this->$getter();
			}
			else
			{
				$value = $this->{$field['field']};
			}

			if (is_object($value))
			{
				if (method_exists($value, 'toArray'))
				{
					$value = $value->toArray();
				}
				else
				{
					continue;
				}
			}

			if ($value !== null)
			{
				$result[$offset] = $value;
			}
		}

		return $result;
	}
}