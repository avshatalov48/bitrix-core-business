<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Sale;

class Pool
{
	/** @var array */
	protected $quantities = array();

	/** @var array */
	protected $items = array();

	public function __construct()
	{
	}

	/**
	 * Returns any variable by its name. Null if variable is not set.
	 *
	 * @param $code
	 * @return float | null
	 */
	public function get($code)
	{
		if (
			isset($this->quantities[$code])
			&& is_array($this->quantities[$code])
		)
		{
			return array_sum($this->quantities[$code]);
		}

		return null;
	}

	/**
	 * Returns any variable by its name. Null if variable is not set.
	 *
	 * @param $code
	 * @return float | null
	 */
	public function getByStore($code, $storeId)
	{
		if (isset($this->quantities[$code][$storeId]))
		{
			return $this->quantities[$code][$storeId];
		}

		return 0;
	}

	/**
	 * @param $code
	 * @param $quantity
	 */
	public function set($code, $quantity)
	{
		$storeId = Sale\Configuration::getDefaultStoreId();

		if (!isset($this->quantities[$code][$storeId]))
		{
			$this->quantities[$code][$storeId] = 0;
		}

		$this->quantities[$code][$storeId] = $quantity;
	}

	/**
	 * @param $code
	 * @param $quantity
	 */
	public function setByStore($code, $storeId, $quantity)
	{
		if (!isset($this->quantities[$code][$storeId]))
		{
			$this->quantities[$code][$storeId] = 0;
		}

		$this->quantities[$code][$storeId] = $quantity;
	}

	/**
	 * @param $code
	 */
	public function delete($code)
	{
		if (isset($this->quantities[$code]))
		{
			unset($this->quantities[$code]);
		}
	}

	/**
	 * @param $code
	 */
	public function deleteByStore($code, $storeId)
	{
		if (isset($this->quantities[$code][$storeId]))
		{
			unset($this->quantities[$code][$storeId]);
		}
	}

	/**
	 * @param $code
	 * @param $item
	 */
	public function addItem($code, $item)
	{
		if (!array_key_exists($code, $this->items))
		{
			$this->items[$code] = $item;
		}
	}

	/**
	 * @return array
	 */
	public function getQuantities()
	{
		$result = [];
		foreach ($this->quantities as $code => $item)
		{
			$result[$code] = array_sum($item);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getQuantitiesWithStore()
	{
		return $this->quantities;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}
}