<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;

class JobCache implements JobCacheInterface
{
	private const TTL = 86400 * 30; // 30 days
	private const PATH = 'sonet/onboarding_user_option';
	private const ID_PREFIX = 'promo_queue_by_user_id_';

	public function getByUserId(int $userId): ?JobCollection
	{
		$cache = Cache::createInstance();
		$cacheId = $this->getCacheId($userId);

		if ($cache->initCache(self::TTL, $cacheId, self::PATH))
		{
			$jobs = $cache->getVars();
			if (!is_array($jobs))
			{
				return null;
			}

			return JobCollection::mapFromArray($jobs);
		}

		return null;
	}

	private function getCacheId(int $userId): string
	{
		return self::ID_PREFIX . $userId;
	}

	/**
	 * @param array<int, JobCollection> $sortedByUserJobs
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function save(array $sortedByUserJobs): void
	{
		if (empty($sortedByUserJobs))
		{
			return;
		}

		$cache = Cache::createInstance();

		foreach ($sortedByUserJobs as $userId => $jobs)
		{
			$this->cleanByUserIds($userId);
			$cacheId = $this->getCacheId($userId);

			$cache->startDataCache(self::TTL, $cacheId, self::PATH);
			$this->registerCollabTags($cacheId, $jobs);
			$cache->endDataCache($jobs->toArray());
		}
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private function registerCollabTags(string $cacheId, JobCollection $jobs): void
	{
		$taggedCache = Application::getInstance()->getTaggedCache();

		$collabIds = $jobs->getCollabIdList();

		foreach ($collabIds as $collabId)
		{
			$taggedCache->startTagCache(self::PATH. '/' . Cache::getPath($cacheId));
			$taggedCache->registerTag($this->getCacheTag($collabId));
			$taggedCache->endTagCache();
		}
	}

	private function getCacheTag(int $collabId): string
	{
		return 'sonet_onboarding_collab_id_' . $collabId;
	}

	public function cleanByUserIds(int ...$userIds): void
	{
		$cache = Cache::createInstance();

		foreach ($userIds as $userId)
		{
			$cacheId = $this->getCacheId($userId);
			$cache->clean($cacheId, self::PATH);
		}
	}

	/**
	 * @param array<int, JobCollection> $sortedByUserJobs
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function cleanByJobCollection(array $sortedByUserJobs): void
	{
		foreach ($sortedByUserJobs as $userId => $jobs)
		{
			$cachedJobs = $this->getByUserId($userId);

			if (null !== $cachedJobs)
			{
				$cachedJobs->cleanIntersectingJobs($jobs);
				$this->save([$userId => $cachedJobs]);
			}
		}
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function cleanByCollabIds(int ...$collabIds): void
	{
		$taggedCache = Application::getInstance()->getTaggedCache();

		foreach ($collabIds as $collabId)
		{
			$taggedCache->clearByTag($this->getCacheTag($collabId));
		}
	}
}
