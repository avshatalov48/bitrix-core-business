<?php
namespace Bitrix\Location\Common;

use Bitrix\Main\Data;
use Bitrix\Main\EventManager;

/**
 * Class CachePool
 * @package Bitrix\Location\Common
 * todo: race condition?
 */
abstract class Cache
{
	/** @var Data\Cache */
	protected $cache;

	/**
	 * CachePool constructor.
	 * @param int $ttl
	 * @param string $cacheId
	 * @param Data\Cache $cache
	 * @param EventManager $eventManager
	 */
	public function __construct(int $ttl, string $cacheId, Data\Cache $cache, EventManager $eventManager)
	{
		$cacheDir = '/location';
		$this->cache = $cache;

		if($this->cache->initCache($ttl, $cacheId, $cacheDir))
		{
			$this->loadDataFromCache();
		}

		$eventManager->addEventHandler('main', 'OnAfterEpilog', [$this, 'saveDataToCache']);
	}

	abstract protected function loadDataFromCache(): void;
	abstract public function saveDataToCache(): void;
}