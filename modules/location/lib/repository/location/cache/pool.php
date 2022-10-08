<?php
namespace Bitrix\Location\Repository\Location\Cache;

use Bitrix\Location\Entity\Location;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Web\Json;

/**
 * Class CachePool
 * @package Bitrix\Location\Common
 */
class Pool extends \Bitrix\Location\Common\Pool
{
	/**
	 * @param string $index
	 * @return mixed
	 */
	public function getItem(string $index)
	{
		$result = parent::getItem($index);

		try
		{
			$result = Json::decode($result);
			$result = Location::fromArray($result);
		}
		catch (\Exception $e)
		{
			$result = null;
		}

		return $result;
	}

	public function addItem(string $index, $location): void
	{
		if(!($location instanceof Location))
		{
			throw new ArgumentTypeException('location must be type of Location');
		}

		parent::addItem(
			$index, Json::encode(
				$location->toArray()
			)
		);
	}
}