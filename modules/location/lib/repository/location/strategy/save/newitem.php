<?php

namespace Bitrix\Location\Repository\Location\Strategy\Save;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Repository\Location\Strategy\Save;
use Bitrix\Main\Result;
use \Bitrix\Location\Service;

/**
 * Class DefaultStrategy
 * @package Bitrix\Location\FindStrategy
 */
final class NewItem extends Save
{
	/**
	 * @param Location $location
	 * @return Result
	 */
	public function save(Location $location): Result
	{
		if($existedLocation = $this->findLocalSavedLocation($location))
		{
			$location->setId($existedLocation->getId());
			$location->setCode($existedLocation->getCode());
		}

		$result = new Result();

		foreach($this->locationRepositories as $repository)
		{
			$res = $repository->save($location);

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		if($parents = $location->getParents())
		{
			$res = $this->saveParents($parents);

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param Location $location
	 * @return Location|bool|null
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function findLocalSavedLocation(Location $location)
	{
		return Service\LocationService::getInstance()->findByExternalId(
			$location->getExternalId(),
			$location->getSourceCode(),
			$location->getLanguageId(),
			LOCATION_SEARCH_SCOPE_INTERNAL
		);
	}
}