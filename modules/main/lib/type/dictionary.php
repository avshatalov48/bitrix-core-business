<?php
namespace Bitrix\Main\Type;

class Dictionary
	implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{
	/**
	 * @var array
	 */
	protected $values = [];

	/**
	 * Creates object.
	 *
	 * @param array | null $values
	 */
	public function __construct(array $values = null)
	{
		if($values !== null)
		{
			$this->values = $values;
		}
	}

	/**
	 * Returns any variable by its name. Null if variable is not set.
	 *
	 * @param string $name
	 * @return string | array | null
	 */
	public function get($name)
	{
		// this condition a bit faster
		// it is possible to omit array_key_exists here, but for uniformity...
		if (isset($this->values[$name]) || \array_key_exists($name, $this->values))
		{
			return $this->values[$name];
		}

		return null;
	}

	public function set($name, $value = null)
	{
		if (\is_array($name))
		{
			$this->values = $name;
		}
		else
		{
			$this[$name] = $value;
		}
	}

	/**
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @param $values
	 */
	public function setValues($values)
	{
		$this->values = $values;
	}

	public function clear()
	{
		$this->values = [];
	}

	/**
	 * Return the current element
	 * @internal
	 * @deprecated
	 */
	#[\ReturnTypeWillChange]
	public function current()
	{
		return current($this->values);
	}

	/**
	 * Move forward to next element
	 * @internal
	 * @deprecated
	 */
	public function next(): void
	{
		next($this->values);
	}

	/**
	 * Return the key of the current element
	 * @internal
	 * @deprecated
	 */
	#[\ReturnTypeWillChange]
	public function key()
	{
		return key($this->values);
	}

	/**
	 * Checks if current position is valid
	 * @internal
	 * @deprecated
	 */
	public function valid(): bool
	{
		return ($this->key() !== null);
	}

	/**
	 * Rewind the Iterator to the first element
	 * @internal
	 * @deprecated
	 */
	public function rewind(): void
	{
		reset($this->values);
	}

	/**
	 * Whether a offset exists
	 * @internal
	 * @deprecated
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->values[$offset]) || \array_key_exists($offset, $this->values);
	}

	/**
	 * Offset to retrieve
	 * @internal
	 * @deprecated
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if (isset($this->values[$offset]) || \array_key_exists($offset, $this->values))
		{
			return $this->values[$offset];
		}

		return null;
	}

	/**
	 * Offset to set
	 * @internal
	 * @deprecated
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		if($offset === null)
		{
			$this->values[] = $value;
		}
		else
		{
			$this->values[$offset] = $value;
		}
	}

	/**
	 * Offset to unset
	 * @internal
	 * @deprecated
	 */
	public function offsetUnset($offset): void
	{
		unset($this->values[$offset]);
	}

	/**
	 * Count elements of an object
	 * @internal
	 * @deprecated
	 */
	public function count(): int
	{
		return \count($this->values);
	}

	/**
	 * Returns the values as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->values;
	}

	/**
	 * Returns true if the dictionary is empty.
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->values);
	}

	/**
	 * JsonSerializable::jsonSerialize — Specify data which should be serialized to JSON
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->values;
    }
}
