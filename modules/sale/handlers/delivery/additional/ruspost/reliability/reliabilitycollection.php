<?php

namespace Sale\Handlers\Delivery\Additional\RusPost\Reliability;


use Bitrix\Sale\Internals\EO_Reliability_Collection;

/**
 * Class ReliabilityCollection
 * @package Sale\Handlers\Delivery\Additional\RusPost\Reliability
 */
class ReliabilityCollection extends EO_Reliability_Collection
{
	/**
	 * @param array $hashes
	 * @return ReliabilityCollection
	 */
	public function filterByHashes(array $hashes)
	{
		if(empty($hashes))
		{
			return $this;
		}

		$result = new self();

		/** @var Reliability $reliability */
		foreach ($this as $reliability)
		{

			if(in_array($reliability->getHash(), $hashes))
			{
				$result->add($reliability);
			}
		}

		return $result;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveItems()
	{
		$storedCollection = static::$dataClass::query()
			->addFilter('HASH', $this->getHashList())
			->addSelect('*')
			->fetchCollection();

		/** @var Reliability $item */
		foreach ($storedCollection as $stored)
		{
			if($item = $this->getByPrimary($stored->getHash()))
			{
				$stored->setReliability($item->getReliability());
				$this->removeByPrimary($item->getHash());
				$this->add($stored);
			}
		}

		$this->save();
	}

	/**
	 * @param Reliability[] $items
	 */
	public function setItems(array $items)
	{
		foreach ($items as $item)
		{
			if($exist = $this->getByPrimary($item->getHash()))
			{
				$this->removeByPrimary($item->getHash());
			}

			$this->add($item);
		}
	}
}