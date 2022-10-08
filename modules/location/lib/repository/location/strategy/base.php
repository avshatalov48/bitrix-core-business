<?php

namespace Bitrix\Location\Repository\Location\Strategy;

use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Main\ArgumentOutOfRangeException;

/**
 * Interface FindStrategy
 * @package Bitrix\Location\FindStrategy
 */
abstract class Base
{
	/** @var \Bitrix\Location\Repository\Location\IRepository[] */
	protected $locationRepositories = [];

	const REPO_PRIORITY_A = 'A';
	const REPO_PRIORITY_B = 'B';
	const REPO_PRIORITY_C = 'C';

	/**
	 * FindStrategy constructor.
	 * @param \Bitrix\Location\Repository\Location\IRepository[] $locationRepositories
	 * @throws ArgumentOutOfRangeException
	 */
	public function __construct(array $locationRepositories = [])
	{
		$this->setLocationRepositories($locationRepositories);
	}

	/**
	 * @param \Bitrix\Location\Repository\Location\IRepository[] $locationRepositories
	 * @return self
	 */
	public function setLocationRepositories(array $locationRepositories): Base
	{
		foreach($locationRepositories as $repository)
		{
			if(!($repository instanceof IRepository))
			{
				throw new ArgumentOutOfRangeException('locationRepositories');
			}

			$this->locationRepositories[] = $repository;
		}

		return $this;
	}
}