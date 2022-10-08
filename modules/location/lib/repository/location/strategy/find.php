<?php

namespace Bitrix\Location\Repository\Location\Strategy;

use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity\Generic\Collection;
use Bitrix\Location\Entity\Location\Parents;
use Bitrix\Location\Repository\Location\Capability\IFindByExternalId;
use Bitrix\Location\Repository\Location\Capability\IFindById;
use Bitrix\Location\Repository\Location\Capability\IFindByText;
use Bitrix\Location\Repository\Location\Capability\IFindParents;
use Bitrix\Location\Repository\Location\Capability\ISupportAutocomplete;
use Bitrix\Location\Repository\Location\IScope;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Location\Repository\Location\ICache;
use Bitrix\Location\Repository\Location\IDatabase;

/**
 * Class DefaultStrategy
 * @package Bitrix\Location\FindStrategy
 */
class Find	extends Base
{
	/** @inheritDoc */
	public function findById(int $id, string $languageId, int $searchScope)
	{
		return $this->find(IFindById::class, 'findById', [$id, $languageId], $searchScope);
	}

	/** @inheritDoc */
	public function findByExternalId(string $externalId, string $sourceCode, string $languageId, int $searchScope)
	{
		return $this->find(IFindByExternalId::class, 'findByExternalId', [$externalId, $sourceCode, $languageId], $searchScope);
	}

	/** @inheritDoc */
	public function findByText(string $text, string $languageId, int $searchScope)
	{
		return $this->find(IFindByText::class, 'findByText', [$text, $languageId], $searchScope) ?? new Location\Collection();
	}
	
	public function autocomplete(array $params, int $searchScope)
	{
		return $this->find(ISupportAutocomplete::class, 'autocomplete', [$params], $searchScope) ?? [];
	}

	public function findParents(Location $location, string $languageId, int $searchScope)
	{
		return $this->find(IFindParents::class, 'findParents', [$location, $languageId], $searchScope) ?? new Parents();
	}

	/** @inheritDoc */
	public function setLocationRepositories(array $locationRepositories): Base
	{
		$idx = 0;

		foreach($locationRepositories as $repository)
		{
			if($repository instanceof IFindById
				|| $repository instanceof IFindByExternalId
				|| $repository instanceof IFindByText
				|| $repository instanceof IFindParents
			)
			{
				$key = (string)$this->getRepoPriority($repository) . (string)($idx++);
				$this->locationRepositories[$key] = $repository;
			}
		}

		ksort($this->locationRepositories);
		return $this;
	}

	/**
	 * @param string $interface
	 * @param string $method
	 * @param array $params
	 * @param int $searchScope
	 * @return Location|Collection|null|bool
	 */
	protected function find(string $interface, string $method, array $params, int $searchScope)
	{
		$result = null;

		foreach($this->locationRepositories as $repository)
		{
			if($repository instanceof IScope)
			{
				if(!$repository->isScopeSatisfy($searchScope))
				{
					continue;
				}
			}

			if($repository instanceof $interface)
			{
				$result = call_user_func_array([$repository, $method], $params);

				if($result)
				{
					if(!($result instanceof Location\Collection) || $result->count() > 0)
					{
						return $result;
					}
				}
			}
		}

		return null;
	}

	/**
	 * @param IRepository $repository
	 * @return string
	 */
	protected function getRepoPriority(IRepository $repository)
	{
		if($repository instanceof ICache)
		{
			$result = self::REPO_PRIORITY_A;
		}
		elseif($repository instanceof IDatabase)
		{
			$result = self::REPO_PRIORITY_B;
		}
		else
		{
			$result = self::REPO_PRIORITY_C;
		}

		return $result;
	}



}