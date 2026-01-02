<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Repository\Cache;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\Promotion\PromotionService;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class JobCacheProxy implements JobCacheInterface
{
	use InstanceTrait;

	private JobCacheInterface $cache;
	private PromotionService $promoService;

	private function __construct()
	{
		$this->cache = ServiceLocator::getInstance()->get('socialnetwork.onboarding.job.cache');
		$this->promoService = ServiceLocator::getInstance()->get('socialnetwork.onboarding.promotion.service');
	}

	public function getByUserId(int $userId): ?JobCollection
	{
		return $this->cache->getByUserId($userId);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function save(array $sortedByUserJobs): void
	{
		$this->cache->save($sortedByUserJobs);
		$this->promoService->onPromotionUpdate($sortedByUserJobs);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function cleanByJobCollection(array $sortedByUserJobs): void
	{
		$this->cache->cleanByJobCollection($sortedByUserJobs);

		$cachedByUserJobs = [];
		foreach (array_keys($sortedByUserJobs) as $userId)
		{
			$cachedByUserJobs[$userId] = $this->getByUserId($userId);
		}

		$this->promoService->onPromotionUpdate($cachedByUserJobs);
	}

	public function cleanByUserIds(int ...$userIds): void
	{
		$this->cache->cleanByUserIds(...$userIds);

		$cachedByUserJobs = [];
		foreach ($userIds as $userId)
		{
			$cachedByUserJobs[$userId] = new JobCollection();
		}

		$this->promoService->onPromotionUpdate($cachedByUserJobs);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function cleanByCollabIds(int ...$collabIds): void
	{
		$this->cache->cleanByCollabIds(...$collabIds);
	}
}
