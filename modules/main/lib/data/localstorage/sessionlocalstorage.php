<?php
namespace Bitrix\Main\Data\LocalStorage;

use Exception;
use Traversable;

final class SessionLocalStorage implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/** @var array */
	private $data = [];
	/** @var string */
	private $uniqueName;

	public function __construct(string $uniqueName)
	{
		$this->uniqueName = $uniqueName;
	}

	/**
	 * @return string
	 */
	public function getUniqueName(): string
	{
		return $this->uniqueName;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	public function &get($key)
	{
		return $this->data[$key];
	}

	public function set($key, $value): self
	{
		$this->data[$key] = $value;

		return $this;
	}

	public function clear(): void
	{
        $this->data = [];
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function &offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset === null)
		{
			$this->data[] = $value;
		}
		else
		{
			$this->data[$offset] = $value;
		}
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	public function count()
	{
		return count($this->data);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->getData());
	}
}