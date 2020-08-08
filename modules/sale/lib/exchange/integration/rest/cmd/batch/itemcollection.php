<?php


namespace Bitrix\Sale\Exchange\Integration\Rest\Cmd\Batch;


class ItemCollection
{
	protected $collection;
	static $internalIndex=0;

	public function addItem(Item $item)
	{
		$index = $item->getInternalIndex() == '' ? 'n'.static::$internalIndex++ : $item->getInternalIndex();

		$this->collection[$index] = $item;
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
				$result[$index] = $item->getCmd()->build();
			}
		}
		return $result;
	}

	public function toArrayRaw()
	{
		$result = [];
		if(count($this->collection)>0)
		{
			/**
			 * @var Item $item
			 */
			foreach ($this->collection as $index=>$item)
			{
				$result[$index] = $item->getCmd()->getFieldsValues();
			}
		}
		return $result;
	}
}