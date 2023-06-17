<?php

namespace Bitrix\Main\Engine\Response\DataType;

use Bitrix\Main\Type\Contract\Arrayable;

final class Page implements Arrayable, \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
	/** @var string */
	private $id;
	/** @var array */
	private $items = [];

	/** @var int|\Closure */
	private $totalCount;
	private $calculatedTotalCount;

	/**
	 * @param string $id Id of collection.
	 * @param array|\Traversable $items
	 * @param int|\Closure $totalCount The parameter can be Closure to prevent unnecessary actions for calculation.
	 */
	public function __construct($id, $items, $totalCount)
	{
		$data = [];
		if (!is_array($items) && $items instanceof \Traversable)
		{
			foreach ($items as $item)
			{
				$data[] = $item;
			}
		}
		else
		{
			$data = $items;
		}

		$this->id = $id;
		$this->items = $data;
		$this->totalCount = $totalCount;
	}

	/**
	 * @return int
	 */
	public function getTotalCount()
	{
		if ($this->totalCount instanceof \Closure)
		{
			$this->calculatedTotalCount = call_user_func($this->totalCount);
		}
		else
		{
			$this->calculatedTotalCount = $this->totalCount;
		}

		return $this->calculatedTotalCount;
	}


	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}

	/**
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset)
	{
		return isset($this->items[$offset]) || array_key_exists($offset, $this->items);
	}

	/**
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if (isset($this->items[$offset]) || array_key_exists($offset, $this->items))
		{
			return $this->items[$offset];
		}

		return null;
	}

	/**
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value): void
	{
		if($offset === null)
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$offset] = $value;
		}
	}

	/**
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset): void
	{
		unset($this->items[$offset]);
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	public function toArray()
	{
		return [
			$this->id => $this->items
		];
	}
}