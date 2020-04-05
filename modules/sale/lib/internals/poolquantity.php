<?php


namespace Bitrix\Sale\Internals;


use Bitrix\Sale;

/**
 * Class PoolQuantity
 * @package Bitrix\Sale\Internals
 */
class PoolQuantity
{
	const POOL_RESERVE_TYPE = 'R';
	const POOL_QUANTITY_TYPE = 'Q';

	private static $poolList = array();
	private $typeList = array();

	/**
	 * @param $key
	 *
	 * @return PoolQuantity
	 */
	public static function getInstance($key)
	{
		if (!isset(static::$poolList[$key]))
		{
			$pool = new static();
			static::$poolList[$key] = $pool;
		}

		return static::$poolList[$key];
	}

	/**
	 * @param $type
	 * @param $code
	 *
	 * @return float|null
	 */
	public function get($type, $code)
	{
		$pool = $this->getByType($type);
		return $pool->get($code);
	}

	/**
	 * @internal
	 * @param $type
	 *
	 * @return Pool
	 */
	public function getByType($type)
	{
		if (empty($this->typeList[$type]))
		{
			$this->typeList[$type] = new Pool();
		}

		return $this->typeList[$type];
	}

	/**
	 * @param $type
	 *
	 * @return array
	 */
	public function getQuantities($type)
	{
		/** @var Pool $pool */
		$pool = $this->getByType($type);
		return $pool->getQuantities();
	}

	/**
	 * @param $type
	 * @param $code
	 * @param $value
	 */
	public function add($type, $code, $value)
	{
		$pool = $this->getByType($type);
		$currentValue = floatval($pool->get($code));
		$pool->set($code, $currentValue + $value);
	}

	/**
	 * @param $type
	 * @param $code
	 * @param $value
	 */
	public function set($type, $code, $value)
	{
		$pool = $this->getByType($type);
		$pool->set($code, $value);
	}


	/**
	 * @param $type
	 * @param $code
	 */
	public function delete($type, $code)
	{
		$pool = $this->getByType($type);
		$pool->delete($code);
	}

	/**
	 * @param $type
	 */
	public function reset($type)
	{
		$pool = $this->getByType($type);
		$list = $pool->getQuantities();

		if (!empty($list))
		{
			foreach($list as $itemKey => $itemValue)
			{
				$pool->delete($itemKey);
			}
		}
	}

}