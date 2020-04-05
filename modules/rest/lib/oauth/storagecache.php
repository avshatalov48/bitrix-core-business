<?php
namespace Bitrix\Rest\OAuth;


use Bitrix\Main\Application;
use Bitrix\Rest\AuthStorageInterface;

class StorageCache implements AuthStorageInterface
{
	const CACHE_TTL = 3600;
	const CACHE_PREFIX = "oauth_";

	public function store(array $authResult)
	{
		$cache = $this->getCache();

		$cache->read(static::CACHE_TTL, $this->getCacheId($authResult["access_token"]));
		$cache->set($this->getCacheId($authResult["access_token"]), $authResult);
	}

	public function rewrite(array $authResult)
	{
		$cache = $this->getCache();

		$cache->clean($this->getCacheId($authResult["access_token"]));
		$cache->read(static::CACHE_TTL, $this->getCacheId($authResult["access_token"]));
		$cache->set($this->getCacheId($authResult["access_token"]), $authResult);
	}

	public function restore($accessToken)
	{
		$cache = $this->getCache();

		$authResult = false;

		if($readResult = $cache->read(static::CACHE_TTL, $this->getCacheId($accessToken)))
		{
			$authResult = $cache->get($this->getCacheId($accessToken));
		}

		return $authResult;
	}

	protected function getCacheId($accessToken)
	{
		return static::CACHE_PREFIX.$accessToken;
	}

	protected function getCache()
	{
		return Application::getInstance()->getManagedCache();
	}
}