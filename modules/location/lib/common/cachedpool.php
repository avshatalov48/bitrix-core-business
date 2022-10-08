<?php
namespace Bitrix\Location\Common;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\EventManager;

/**
 * Class CachePool
 * @package Bitrix\Location\Common
 */
class CachedPool extends \Bitrix\Location\Common\Cache
{
	/** @var Pool  */
	protected $pool;
	/** @var Cache */
	protected $cache;
	/** @var bool  */
	protected $isItemChanged = false;

	/**
	 * CachePool constructor.
	 * @param Pool $pool
	 * @param int $ttl
	 * @param string $cacheId
	 * @param Cache $cache
	 * @param EventManager $eventManager
	 */
	public function __construct(Pool $pool, int $ttl, string $cacheId, Cache $cache, EventManager $eventManager)
	{
		$this->pool = $pool;
		parent::__construct($ttl, $cacheId, $cache, $eventManager);
	}

	public function loadDataFromCache(): void
	{
		$items = $this->cache->getVars()['items'];
		if(is_array($items))
		{
			$this->pool->setItems($items);
		}
	}

	public function saveDataToCache(): void
	{
		if($this->isItemChanged)
		{
			$this->cache->forceRewriting(true);
			$this->cache->startDataCache();
			$this->cache->endDataCache(['items' => $this->pool->getItems()]);
		}
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getItem(string $key)
	{
		return $this->pool->getItem($key);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function addItem(string $key, $value): void
	{
		$this->pool->addItem($key, $value);
		$this->isItemChanged = true;
	}

	/**
	 * @param string $key
	 */
	public function deleteItem(string $key): void
	{
		$this->pool->deleteItem($key);
		$this->isItemChanged = true;
	}

	public function cleanItems(): void
	{
		$this->pool->cleanItems();
		$this->isItemChanged = true;
	}
}