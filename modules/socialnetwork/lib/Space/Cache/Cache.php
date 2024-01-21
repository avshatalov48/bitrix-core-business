<?php

namespace Bitrix\Socialnetwork\Space\Cache;

use \Bitrix\Main\Data;

class Cache
{
	public const CACHE_ID = 'socialnetwork-toolbar-composition-';
	private const TTL = 86400;
	private const DIRECTORY = 'socialnetwork';

	private Data\Cache $cache;

	public function __construct(private int $userId, private int $spaceId)
	{
		$this->init();
	}

	public function store(array $data): void
	{
		$this->cache->forceRewriting(true);
		$this->cache->startDataCache();
		$this->cache->endDataCache($data);
	}

	public function get(): array
	{
		if (!$this->initCache())
		{
			return [];
		}
		$variables = $this->cache->getVars();
		return is_array($variables) ? $variables : [];
	}

	public function initCache(): bool
	{
		return $this->cache->initCache(static::TTL, $this->getCacheId(), static::DIRECTORY);
	}

	private function init(): void
	{
		$this->cache = Data\Cache::createInstance();
		$this->initCache();
	}

	private function getCacheId(): string
	{
		return static::CACHE_ID . $this->userId . '-' . $this->spaceId;
	}
}