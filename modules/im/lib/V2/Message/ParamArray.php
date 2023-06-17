<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Main\ORM;
use Bitrix\Im\Model\EO_MessageParam;
use Bitrix\Im\Model\EO_MessageParam_Collection;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\V2\Collection;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\RegistryEntry;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;

/**
 * Message Parameter Class.
 *
 * @method MessageParameter|Param next()
 * @method MessageParameter|Param current()
 * @method MessageParameter|Param offsetGet($offset)
 */
class ParamArray extends Collection implements MessageParameter, RegistryEntry
{
	use RegistryEntryImplementation;

	protected ?string $type = null;

	protected ?string $name = null;

	protected ?int $messageId = null;

	// Object changed flag
	protected bool $isChanged = true;

	// Object marked to drop
	protected bool $markedDrop = false;

	/**
	 * @param array|EO_MessageParam_Collection|ORM\Objectify\Collection $source
	 */
	public function load($source): Result
	{
		$result = parent::load($source);
		if ($result->isSuccess())
		{
			foreach ($this as $param)
			{
				$this
					->setName($param->getName())
					->setMessageId($param->getMessageId())
				;
				break;
			}

			$this->markChanged(false);
		}

		return $result;
	}

	//region Param value

	/**
	 * @param int[]|string[] $values
	 * @return static
	 */
	public function setValue($values): self
	{
		if (!is_array($values))
		{
			$values = [$values];
		}
		switch ($this->type)
		{
			case Param::TYPE_INT_ARRAY:
				$values = array_map('intVal', $values);
				break;

			case Param::TYPE_STRING:
				$values = array_map('strVal', $values);
		}

		foreach ($this as $param)
		{
			if (!$param->isDeleted() && in_array($param->getValue(), $values, true))
			{
				$inx = array_search($param->getValue(), $values, true);
				if ($inx !== false)
				{
					unset($values[$inx]);
				}
			}
			else
			{
				$param->markDrop();
				$this->markChanged();
			}
		}

		if (!empty($values))
		{
			foreach ($values as $value)
			{
				$this->addValue($value);
			}

			$this->markChanged();
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getDefaultValue(): array
	{
		$value = [];
		$type = Params::getType($this->name);
		if (isset($type['default']))
		{
			$value = $type['default'];
		}

		return $value;
	}

	/**
	 * @return bool
	 */
	public function hasValue(): bool
	{
		return $this->count() > 0;
	}

	/**
	 * @return int[]|string[]
	 */
	public function getValue(): array
	{
		$values = $this->getDefaultValue() ?: [];
		foreach ($this as $param)
		{
			if ($param->isDeleted())
			{
				continue;
			}
			switch ($this->type)
			{
				case Param::TYPE_INT_ARRAY:
					$values[] = (int)$param->getValue();
					break;

				case Param::TYPE_STRING_ARRAY:
					$values[] = (string)$param->getValue();
					break;

				default:
					$values[] = $param->getValue();
			}
		}

		return $values;
	}

	/**
	 * @param int|string $value
	 * @return static
	 */
	public function addValue($value): self
	{
		switch ($this->type)
		{
			case Param::TYPE_INT_ARRAY:
				$value = (int)$value;
				break;

			case Param::TYPE_STRING_ARRAY:
				$value = (string)$value;
		}

		foreach ($this as $param)
		{
			if ($param->getValue() === $value)
			{
				return $this;
			}
		}

		$param = new Param;
		$param
			->setName($this->getName())
			->setType($this->type == Param::TYPE_INT_ARRAY ? Param::TYPE_INT : Param::TYPE_STRING)
			->setValue($value)
		;

		if ($this->getMessageId())
		{
			$param->setMessageId($this->getMessageId());
		}

		if ($param->getPrimaryId())
		{
			$param->setRegistry($this);
		}
		else
		{
			$this['~'. $this->count()] = $param;
		}

		$this->markChanged();

		return $this;
	}

	/**
	 * @param int[]|string[] $values
	 * @return static
	 */
	public function unsetValue($values = []): self
	{
		if (!empty($values))
		{
			if (!is_array($values))
			{
				$values = [$values];
			}
			switch ($this->type)
			{
				case Param::TYPE_INT_ARRAY:
					$values = array_map('intVal', $values);
					break;

				case Param::TYPE_STRING:
					$values = array_map('strval', $values);
			}

			foreach ($this as $param)
			{
				if (in_array($param->getValue(), $values, true))
				{
					$param->markDrop();
				}
			}

			$this->markChanged();
		}
		else
		{
			foreach ($this as $param)
			{
				$param->markDrop();
			}

			if ($this->getRegistry())
			{
				unset($this->getRegistry()[$this->getName()]);
			}

			$this->markDrop();
		}

		return $this;
	}

	/**
	 * @return string[]|null
	 */
	public function toRestFormat(): ?array
	{
		return array_map('strval', $this->getValue());
	}

	/**
	 * @return mixed
	 */
	public function toPullFormat(): ?array
	{
		return $this->toRestFormat();
	}


	//endregion

	//region Setters & Getters

	public function setMessageId(int $messageId): self
	{
		$this->messageId = $messageId;
		foreach ($this as $value)
		{
			$value->setMessageId($this->messageId);
		}

		$this->markChanged();

		return $this;
	}

	public function getMessageId(): ?int
	{
		return $this->messageId;
	}

	/**
	 * @param string $name
	 * @return self
	 */
	public function setName(string $name): self
	{
		if ($this->name = $name)
		{
			$this->markChanged();
		}
		$this->name = $name;
		$this->detectType();
		foreach ($this as $param)
		{
			$param->setName($this->name);
		}

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setType(string $type): self
	{
		if ($this->type != $type)
		{
			$this->markChanged();
		}
		$this->type = $type;
		foreach ($this as $param)
		{
			switch ($this->type)
			{
				case Param::TYPE_INT_ARRAY:
					$param->setType(Param::TYPE_INT);
					break;

				case Param::TYPE_STRING_ARRAY:
					$param->setType(Param::TYPE_STRING);
			}
		}

		return $this;
	}

	public function getType(): string
	{
		return $this->type ?? Param::TYPE_STRING_ARRAY;
	}

	public function detectType(): self
	{
		if (!empty($this->name))
		{
			$type = Params::getType($this->name);
			$this->setType($type['type'] ?? Param::TYPE_STRING_ARRAY);
		}

		return $this;
	}

	//endregion

	//region Data storage

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


	public static function getCollectionElementClass(): string
	{
		return Param::class;
	}

	public static function find(array $filter, array $order, ?int $limit = null, ?Context $context = null): Collection
	{
		$query = MessageParamTable::query()
			->setFilter($filter)
			->setOrder($order)
		;

		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		return new static($query->fetchCollection());
	}

	//endregion
}
