<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector;

use Bitrix\Sender\Recipient;

/**
 * Class DataCounter
 * @package Bitrix\Sender\Connector
 */
class DataCounter
{
	/** @var array $data Data. */
	public $data;

	/**
	 * DataCounter constructor.
	 *
	 * @param array $data Data.
	 */
	public function __construct(array $data)
	{
		$this->data = array();
		$types = Recipient\Type::getList();
		foreach ($data as $typeId => $count)
		{
			if (is_numeric($typeId))
			{
				$typeId = (int) $typeId;
			}
			$typeId = $typeId ?: Recipient\Type::EMAIL;
			$typeId = is_numeric($typeId) ? $typeId : Recipient\Type::getId($typeId);
			if ($typeId && !in_array($typeId, $types))
			{
				continue;
			}

			$this->data[$typeId] = (int) $count;
		}
	}

	/**
	 * Get summary.
	 *
	 * @return integer
	 */
	public function getSummary()
	{
		$summary = 0;
		foreach ($this->data as $typeId => $count)
		{
			$summary += $count;
		}

		return $summary;
	}

	/**
	 * Get count by type.
	 *
	 * @param integer $typeId Type ID.
	 * @return integer
	 */
	public function getCount($typeId)
	{
		return isset($this->data[$typeId]) ? $this->data[$typeId] : 0;
	}

	/**
	 * Leave by type ID.
	 *
	 * @param integer $leaveTypeId Type ID.
	 * @return $this
	 */
	public function leave($leaveTypeId = null)
	{
		if (!$leaveTypeId)
		{
			return $this;
		}

		foreach ($this->data as $typeId => $count)
		{
			if ($leaveTypeId == $typeId)
			{
				continue;
			}

			unset($this->data[$typeId]);
		}

		return $this;
	}

	/**
	 * Get count by type.
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->data;
	}

	/**
	 * Get array.
	 *
	 * @return array
	 */
	public function getArray()
	{
		$counters = array();
		foreach ($this->data as $typeId => $count)
		{
			$counters[] = array(
				'typeId' => $typeId,
				'typeCode' => Recipient\Type::getCode($typeId),
				'typeName' => Recipient\Type::getName($typeId),
				'count' => $count
			);
		}

		$result = self::getDefaultArray();
		$result['summary'] = $this->getSummary();
		$result['counters'] = $counters;

		return $result;
	}

	/**
	 * Get array counters.
	 *
	 * @return array
	 */
	public function getArrayCounters()
	{
		$counters = array();
		foreach ($this->data as $typeId => $count)
		{
			$counters[$typeId] = $count;
		}

		return $counters;
	}

	/**
	 * Get default array.
	 *
	 * @return array
	 */
	public static function getDefaultArray()
	{
		return array(
			'summary' => 0,
			'counters' => array()
		);
	}
}