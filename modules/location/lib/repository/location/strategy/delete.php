<?php

namespace Bitrix\Location\Repository\Location\Strategy;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Repository\Location\Capability\IDelete;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Main\Result;

/**
 * Class Delete
 * @package Bitrix\Location\Repository\Location\Strategy
 */
class Delete
	extends Base
	implements IDelete
{
	/** @var \Bitrix\Location\Repository\Location\Capability\IDelete[] */
	protected $locationRepositories = [];

	/** @inheritDoc */
	public function setLocationRepositories(array $locationRepositories): Base
	{
		foreach($locationRepositories as $repository)
		{
			if($repository instanceof IDelete)
			{
				$this->locationRepositories[] = $repository;
			}
		}

		return $this;
	}

	/**
	 * @param Location $location
	 * @return Result
	 */
	public function delete(Location $location): Result
	{
		$result = new Result();

		/** @var IRepository $repository */
		foreach($this->locationRepositories as $repository)
		{
			$res = $repository->delete($location);

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}
}