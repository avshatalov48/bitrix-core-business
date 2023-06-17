<?php

namespace Bitrix\Location\Repository\Location;

use Bitrix\Location\Common\Pool;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Repository\Location\Capability\IDelete;
use Bitrix\Location\Repository\Location\Capability\IFindByExternalId;
use Bitrix\Location\Repository\Location\Capability\IFindById;
use Bitrix\Location\Repository\Location\Capability\ISave;
use Bitrix\Main\EventManager;
use Bitrix\Main\Result;

/**
 * Class Cache
 * @package Bitrix\Location\Repository
 */
class Cache extends \Bitrix\Location\Common\Cache
	implements IRepository, ICache, IFindById, IFindByExternalId, ISave, IDelete, IScope
{
	/** @var \Bitrix\Location\Common\Pool  */
	protected $pool;
	/** @var \Bitrix\Main\Data\Cache  */
	protected $cache;
	/** @var array  */
	protected $idMap = [];
	/** @var array  */
	protected $externalIdMap = [];
	/** @var bool  */
	protected $isItemChanged = false;

	public function __construct(Pool $pool, int $ttl, string $cacheId, \Bitrix\Main\Data\Cache $cache, EventManager $eventManager)
	{
		$this->pool = $pool;
		parent::__construct($ttl, $cacheId, $cache, $eventManager);
	}

	/**
	 * @inheritDoc
	 */
	public function isScopeSatisfy(int $scope): bool
	{
		return $scope === LOCATION_SEARCH_SCOPE_ALL || $scope === LOCATION_SEARCH_SCOPE_INTERNAL;
	}

	protected function loadDataFromCache(): void
	{
		$items = $this->cache->getVars()['items'];

		if(!is_array($items))
		{
			return;
		}

		$this->pool->setItems($items);
		$this->idMap = $this->cache->getVars()['idMap'];
		$this->externalIdMap = $this->cache->getVars()['externalIdMap'];
	}

	public function saveDataToCache(): void
	{
		if($this->isItemChanged)
		{
			$this->cache->forceRewriting(true);
			$this->cache->startDataCache();
			$this->cache->endDataCache([
				'items' => $this->pool->getItems(),
				'idMap' => $this->idMap,
				'externalIdMap' => $this->externalIdMap
			]);
		}
	}

	/**
	 * @param int $locationId
	 * @param string $languageId
	 * @return string
	 */
	protected function createIdIndex(int $locationId, string $languageId): string
	{
		return (string)$locationId.'_'.$languageId;
	}

	protected function createExternalIdIndex(string $externalId, string $sourceCode, string $languageId): string
	{
		return $externalId.'_'.$sourceCode.'_'.$languageId;
	}

	/** @inheritDoc */
	public function findById(int $locationId, string $languageId)
	{
		$result = null;
		$externalIndex = $this->createIdIndex($locationId, $languageId);

		if(isset($this->idMap[$externalIndex]))
		{
			$result = $this->pool->getItem($this->idMap[$externalIndex]);
		}

		return $result;
	}

	/** @inheritDoc */
	public function findByExternalId(string $externalId, string $sourceCode, string $languageId)
	{
		$result = null;

		$externalIndex = $this->createExternalIdIndex($externalId, $sourceCode, $languageId);

		if(isset($this->externalIdMap[$externalIndex]))
		{
			$result = $this->pool->getItem($this->externalIdMap[$externalIndex]);
		}

		return $result;
	}

	/** @inheritDoc */
	public function save(Location $location): Result
	{
		$index = $this->pool->getItemsCount();
		$this->pool->addItem($index, $location);
		$languageId = $location->getLanguageId();

		if($locationId = $location->getId())
		{
			$tmpIndex = $this->createIdIndex($locationId, $languageId);
			$this->idMap[$tmpIndex] = $index;
		}

		if($externalId = $location->getExternalId())
		{
			$sourceCode = $location->getSourceCode();
			$tmpIndex = $this->createExternalIdIndex($externalId, $sourceCode, $languageId);
			$this->externalIdMap[$tmpIndex] = $index;
		}

		$this->isItemChanged = true;
		return new Result();
	}

	/**
	 * @param Location $location
	 * @return Result
	 */
	public function delete(Location $location): Result
	{
		$index = null;
		$languageId = $location->getLanguageId();

		if($locationId = $location->getId())
		{
			$tmpIndex = $this->createIdIndex($locationId, $languageId);

			if(isset($this->idMap[$tmpIndex]))
			{
				$index = $this->idMap[$tmpIndex];
				unset($this->idMap[$tmpIndex]);
			}
		}

		if($externalId = $location->getExternalId())
		{
			$sourceCode = $location->getSourceCode();
			$tmpIndex = $this->createExternalIdIndex($externalId, $sourceCode, $languageId);

			if(isset($this->externalIdMap[$tmpIndex]))
			{
				if($index === null)
				{
					$index = $this->externalIdMap[$tmpIndex];
				}

				unset($this->externalIdMap[$tmpIndex]);
			}
		}

		if($index)
		{
			$this->pool->deleteItem($index);
		}

		$this->isItemChanged = true;
		return new Result();
	}
}
