<?php

namespace Bitrix\Location\Repository\Location\Strategy;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Repository\Location\Capability\ISaveParents;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Location\Repository\Location\ICache;
use Bitrix\Location\Repository\Location\IDatabase;
use Bitrix\Location\Repository\Location\Capability\ISave;
use Bitrix\Location\Repository\Location\Strategy\Save\NewItem;
use Bitrix\Main\Result;

/**
 * Class Save
 * @package Bitrix\Location\Strategy\Save
 */
class Save
	extends Base
	implements ISave, ISaveParents
{
	/** @var \Bitrix\Location\Repository\Location\Capability\ISave[] */
	protected $locationRepositories = [];

	/** @inheritDoc */
	public function setLocationRepositories(array $locationRepositories): Base
	{
		$idx = 0;

		foreach($locationRepositories as $repository)
		{
			if($repository instanceof ISave)
			{
				$key = (string)$this->getRepoPriority($repository) . (string)($idx++);
				$this->locationRepositories[$key] = $repository;
			}
		}

		ksort($this->locationRepositories);
		return $this;
	}

	/**
	 * @param IRepository $repository
	 * @return string
	 */
	protected function getRepoPriority(IRepository $repository)
	{
		if($repository instanceof IDatabase)
		{
			$result = self::REPO_PRIORITY_A;
		}
		elseif($repository instanceof ICache)
		{
			$result = self::REPO_PRIORITY_B;
		}
		else
		{
			$result = self::REPO_PRIORITY_C;
		}

		return $result;
	}

	/**
	 * @param Location $location
	 * @return Result
	 * todo: save parents hierarchy
	 * todo: update fields or not if exist
	 * todo: if smth wrong with lang part - error + rollback
	 */
	public function save(Location $location): Result
	{
		if($location->getId() <= 0)
		{
			return (new NewItem($this->locationRepositories))
				->save($location);
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

	public function saveParents(Location\Parents $parents): Result
	{
		$result = new Result();

		foreach($this->locationRepositories as $repository)
		{
			if($repository instanceof ISaveParents)
			{
				$res = $repository->saveParents($parents);

				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}
}