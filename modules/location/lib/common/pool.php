<?php
namespace Bitrix\Location\Common;

/**
 * Class CachePool
 * @package Bitrix\Location\Common
 */
class Pool
{
	/** @var int  */
	protected $poolSize = 0;
	/** @var array  */
	protected $items = [];

	/**
	 * CachePool constructor.
	 * @param int $poolSize
	 * @param array $items
	 */
	public function __construct(int $poolSize)
	{
		$this->poolSize = $poolSize;
	}

	/**
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	public function cleanItems(): void
	{
		$this->items = [];
	}

	/**
	 * @param array $items
	 */
	public function setItems(array $items): void
	{
		$this->items = $items;
	}

	/**
	 * @param string $index
	 * @return mixed
	 */
	public function getItem(string $index)
	{
		$result = null;

		if(isset($this->items[$index]))
		{
			$result = $this->items[$index];
			//come up used items
			unset($this->items[$index]);
			$this->items[$index] = $result;
		}

		return $result;
	}

	/**
	 * @param string $index
	 * @param mixed $value
	 */
	public function addItem(string $index, $value): void
	{
		$this->items[$index] = $value;
		$delta = count($this->items) - $this->poolSize;

		if($delta > 0)
		{
			$this->items = $this->decreaseSize($delta, $this->items);
		}
	}

	/**
	 * @return int
	 */
	public function getItemsCount(): int
	{
		return count($this->items);
	}

	/**
	 * @param string $index
	 */
	public function deleteItem(string $index): void
	{		
		if(isset($this->items[$index]))
		{
			unset($this->items[$index]);
		}
	}

	/**
	 * @param int $delta
	 * @param array $items
	 * @return array
	 */
	protected function decreaseSize(int $delta, array $items): array
	{
		if($delta <= 0 || count($items) <= 0)
		{
			return $items;
		}

		do
		{
			reset($items);
			unset($items[key($items)]);
			$delta--;
		}
		while($delta > 0);

		return $items;
	}
}