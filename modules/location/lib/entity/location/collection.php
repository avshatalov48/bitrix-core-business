<?php

namespace Bitrix\Location\Entity\Location;

use Bitrix\Location\Entity\Location;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Result;

/**
 * Class Collection
 * @package Bitrix\Location\Entity\Location
 * @internal
 */
class Collection extends \Bitrix\Location\Entity\Generic\Collection
{
	/** @var Location[]  */
	protected $items = [];

	public function addItem($location): int
	{
		if(!($location instanceof Location))
		{
			throw new ArgumentTypeException('location must be the instance of Location');
		}

		return parent::addItem($location);
	}

	/**
	 * @return Result
	 */
	public function save(): Result
	{
		$result = new Result();

		foreach($this->items as $location)
		{
			$res = $location->save();

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}
}
