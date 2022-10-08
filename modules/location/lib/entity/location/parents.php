<?php

namespace Bitrix\Location\Entity\Location;

use Bitrix\Location\Entity\Location;

/**
 * Class Parents
 * @package Bitrix\Location\Entity\Location
 * @internal
 */
final class Parents extends Collection
{
	/** @var Location[]  */
	protected $items = [];
	/** @var Location|null  */
	protected $descendant;

	/**
	 * @return Location
	 */
	public function getDescendant(): ?Location
	{
		return $this->descendant;
	}

	/**
	 * @param Location $descendant
	 * @return $this
	 */
	public function setDescendant(Location $descendant): self
	{
		$this->descendant = $descendant;
		return $this;
	}

	/**
	 * Check if the Location is in the Parents chain
	 *
	 * @param Location $location
	 * @return bool
	 */
	public function isContain(Location $location): bool
	{
		foreach($this->items as $item)
		{
			if($item->isEqualTo($location))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the Parents chain and other Parents chain are equal
	 *
	 * @param Parents $parents
	 * @return bool
	 */
	public function isEqualTo(Parents $parents): bool
	{
		if($this->count() !== $parents->count())
		{
			return false;
		}

		/**
		 * @var  $idx
		 * @var Location $parent
		 */
		foreach($this as $idx => $item)
		{
			if(!$item->isEqualTo($parents[$idx]))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns Location of given type if it is exists in this Parents chain
	 *
	 * @param int $type
	 * @return Location|null
	 */
	public function getItemByType(int $type):? Location
	{
		foreach($this->items as $item)
		{
			if($item->getType() === $type)
			{
				return $item;
			}
		}

		return null;
	}
}