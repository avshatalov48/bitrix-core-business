<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\InheritedProperty;

class ValuesQueue
{
	/** var array[intger][integer]false|array */
	protected $db_values = array();

	/** @var array[string]ValuesQueue */
	protected static $queues = array();

	/**
	 * Returns a queue instance by the key provided.
	 * Creates new instance if first time called for the given key.
	 *
	 * @param string $key Queue identifier.
	 *
	 * @return ValuesQueue
	 */
	public static function getInstance($key)
	{
		if (!isset(self::$queues[$key]))
			self::$queues[$key] = new ValuesQueue();
		return self::$queues[$key];
	}

	/**
	 * Clears all the queues.
	 *
	 * @return void
	 */
	public static function deleteAll()
	{
		foreach (self::$queues as $queue)
		{
			$queue->db_values = array();
		}
	}

	/**
	 * Returns the queue elements for the given iblock
	 * which were not queried from the database.
	 *
	 * @param integer $iblockId Iblock identifier.
	 *
	 * @return array[integer]integer
	 */
	public function get($iblockId)
	{
		$ids = array();
		foreach ($this->db_values[$iblockId] as $id => $loaded)
		{
			if ($loaded === false)
				$ids[$id] = intval($id);
		}
		return $ids;
	}

	/**
	 * Stores the values for queue elements for future reference.
	 * Missing elements are assigned empty arrays.
	 *
	 * @param integer $iblockId Iblock identifier.
	 * @param array[integer]array $values Values from the database.
	 *
	 * @return void
	 */
	public function set($iblockId, $values)
	{
		foreach ($this->db_values[$iblockId] as $id => $loaded)
		{
			if (isset($values[$id]))
				$this->db_values[$iblockId][$id] = $values[$id];
			else
				$this->db_values[$iblockId][$id] = array();
		}
	}

	/**
	 * Puts an element into the queue.
	 *
	 * @param integer $iblockId Iblock identifier.
	 * @param integer $id Element identifier.
	 *
	 * @return void
	 */
	public function addElement($iblockId, $id)
	{
		if (!isset($this->db_values[$iblockId]))
		{
			$this->db_values[$iblockId] = array();
		}

		if (!isset($this->db_values[$iblockId][$id]))
		{
			$this->db_values[$iblockId][$id] = false;
		}
	}

	/**
	 * Removes an element from the queue.
	 *
	 * @param integer $iblockId Iblock identifier.
	 * @param integer $id Element identifier.
	 *
	 * @return void
	 */
	public function deleteElement($iblockId, $id)
	{
		unset($this->db_values[$iblockId][$id]);
	}

	/**
	 * Returns data of the element from the queue.
	 * False when element was not queried from the database.
	 * And null when element was not queued.
	 *
	 * @param integer $iblockId Iblock identifier.
	 * @param integer $id Element identifier.
	 *
	 * @return false|null|array
	 */
	public function getElement($iblockId, $id)
	{
		return $this->db_values[$iblockId][$id];
	}

	/**
	 * Stores the data for the element in the queue.
	 * Rewrites any existing value.
	 *
	 * @param integer $iblockId Iblock identifier.
	 * @param integer $id Element identifier.
	 * @param array   $value A Value to be stored.
	 *
	 * @return void
	 */
	public function setElement($iblockId, $id, $value)
	{
		$this->db_values[$iblockId][$id] = $value;
	}
}
