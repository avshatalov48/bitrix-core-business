<?php
namespace Bitrix\Main\Data\LocalStorage;

final class SessionLocalStorage implements \ArrayAccess, \Countable, \IteratorAggregate
{
	private array $data = [];
	private string $uniqueName;
	private string $name;

	public function __construct(string $uniqueName, string $name)
	{
		$this->uniqueName = $uniqueName;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getUniqueName(): string
	{
		return $this->uniqueName;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $uniqueName
	 * @return SessionLocalStorage
	 */
	public function setUniqueName(string $uniqueName): self
	{
		$this->uniqueName = $uniqueName;

		return $this;
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

	public function offsetExists($offset): bool
	{
		return isset($this->data[$offset]);
	}

	#[\ReturnTypeWillChange]
	public function &offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value): void
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

	public function offsetUnset($offset): void
	{
		unset($this->data[$offset]);
	}

	public function count(): int
	{
		return count($this->data);
	}

	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->getData());
	}
}