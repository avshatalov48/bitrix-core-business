<?php

namespace Bitrix\Catalog\v2\Fields;

use Bitrix\Catalog\v2\Fields\TypeCasters\NullTypeCaster;
use Bitrix\Catalog\v2\Fields\TypeCasters\TypeCasterContract;

/**
 * Class FieldStorage
 *
 * @package Bitrix\Catalog\v2\Fields
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class FieldStorage implements \ArrayAccess, \Iterator, \Countable
{
	private $fields = [];
	private $originalFields = [];
	private $changedFields = [];

	private $typeCaster;

	public function __construct(TypeCasterContract $typeCaster = null)
	{
		$this->typeCaster = $typeCaster ?? new NullTypeCaster();
	}

	public function initFields(array $fields): self
	{
		foreach ($fields as $name => $value)
		{
			$this->initField($name, $value);
		}

		return $this;
	}

	public function initField($name, $value): self
	{
		if ($this->typeCaster->has($name))
		{
			$value = $this->cast($name, $value);
			$this->fields[$name] = $value;

			unset($this->originalFields[$name], $this->changedFields[$name]);
		}

		return $this;
	}

	public function setFields(array $fields): self
	{
		foreach ($fields as $name => $value)
		{
			$this->setField($name, $value);
		}

		return $this;
	}

	public function setField($name, $value): self
	{
		if ($this->typeCaster->has($name))
		{
			$value = $this->cast($name, $value);

			if ($this->markChanged($name, $value))
			{
				$this->fields[$name] = $value;
			}
		}

		return $this;
	}

	public function hasField($name): bool
	{
		return isset($this->fields[$name]);
	}

	public function getField($name)
	{
		return $this->fields[$name] ?? null;
	}

	public function toArray(): array
	{
		return $this->fields;
	}

	public function isChanged($name): bool
	{
		return isset($this->changedFields[$name]);
	}

	public function clear(): void
	{
		$this->fields = [];
		$this->clearChanged();
	}

	public function clearChanged(): void
	{
		$this->changedFields = [];
		$this->originalFields = [];
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function getOriginalFields(): array
	{
		return $this->originalFields;
	}

	public function getChangedFields(): array
	{
		return $this->changedFields;
	}

	public function hasChangedFields(): bool
	{
		return !empty($this->changedFields);
	}

	private function cast($name, $value)
	{
		return $this->typeCaster->cast($name, $value);
	}

	private function markChanged($name, $value): bool
	{
		$oldValue = $this->getField($name);

		if ($oldValue !== $value || ($oldValue === null && $value === null))
		{
			$originalValue = null;

			if (isset($this->originalFields[$name]) || array_key_exists($name, $this->originalFields))
			{
				$originalValue = $this->originalFields[$name];
			}
			elseif (isset($this->fields[$name]) || array_key_exists($name, $this->fields))
			{
				$originalValue = $this->fields[$name];
			}

			if ($originalValue === $value)
			{
				unset($this->changedFields[$name], $this->originalFields[$name]);

				return true;
			}

			if (!isset($this->originalFields[$name]) || !array_key_exists($name, $this->originalFields))
			{
				$this->originalFields[$name] = $oldValue;
			}

			$this->changedFields[$name] = true;

			return true;
		}

		return false;
	}

	/**
	 * Return the current element
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		return current($this->fields);
	}

	/**
	 * Move forward to next element
	 */
	public function next(): void
	{
		next($this->fields);
	}

	/**
	 * Return the key of the current element
	 */
	#[\ReturnTypeWillChange]
	public function key()
	{
		return key($this->fields);
	}

	/**
	 * Checks if current position is valid
	 */
	public function valid(): bool
	{
		return $this->key() !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 */
	public function rewind(): void
	{
		reset($this->fields);
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->fields[$offset]) || array_key_exists($offset, $this->fields);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 *
	 * @return null|mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		return $this->getField($offset);
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value): void
	{
		$this->setField($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset): void
	{
		unset($this->fields[$offset], $this->originalFields[$offset], $this->changedFields[$offset]);
	}

	/**
	 * Count elements of an object
	 */
	public function count(): int
	{
		return count($this->fields);
	}
}
