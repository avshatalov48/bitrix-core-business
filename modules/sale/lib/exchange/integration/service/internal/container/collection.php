<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Container;

use Traversable;

class Collection
	implements \IteratorAggregate
{
	protected $collection = [];
	static $internalIndex = 0;

	public function addItem(Item $item)
	{
		$index = $item->getInternalIndex() == '' ? 'n'.static::$internalIndex++ : $item->getInternalIndex();

		$this->collection[$index] = $item;
	}

	public function count()
	{
		return count($this->collection);
	}

	public function toArray()
	{
		$result = [];
		if(count($this->collection)>0)
		{
			/**
			 * @var Item $item
			 */
			foreach ($this->collection as $index=>$item)
			{
				$result[$index] = $item->getEntity()->getFieldsValues();
			}
		}
		return $result;
	}

	public function getIndexes()
	{
		return count($this->collection)>0 ? array_keys($this->collection):[];
	}

	/**
	 * @param $index
	 * @return Item|null
	 */
	public function getItemByIndex($index)
	{
		$result = null;
		if(count($this->collection)>0)
		{
			foreach ($this->collection as $k=>$item)
			{
				if($index == $k)
				{
					$result = $item;
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->collection);
	}
}