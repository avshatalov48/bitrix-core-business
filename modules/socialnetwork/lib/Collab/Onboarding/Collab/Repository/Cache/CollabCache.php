<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository\Cache;

use Bitrix\Main\Data\Cache;
use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Entity\FirstAddedMemberData;
use Bitrix\Socialnetwork\Helper\SingletonTrait;

class CollabCache implements CollabCacheInterface
{
	use SingletonTrait;

	private const TTL = 86400 * 30; // 30 days
	private const PATH = 'sonet/collab_onboarding';
	private const ID_PREFIX = 'first_added_member_data';

	public function getFirstAddedMemberData(int $collabId): ?FirstAddedMemberData
	{
		$cache = Cache::createInstance();
		$cacheId = $this->getCacheId($collabId);

		if ($cache->initCache(self::TTL, $cacheId, self::PATH))
		{
			$firstAddedData = $cache->getVars();
			if (!is_array($firstAddedData))
			{
				return null;
			}

			return FirstAddedMemberData::createFromArray($firstAddedData);
		}

		return null;
	}

	public function save(FirstAddedMemberData $firstAddedData): void
	{
		$cache = Cache::createInstance();
		$cacheId = $this->getCacheId($firstAddedData->collabId);

		$cache->clean($cacheId, self::PATH);

		$cache->startDataCache(self::TTL, $cacheId, self::PATH);
		$cache->endDataCache($firstAddedData->toArray());
	}

	private function getCacheId(int $collabId): string
	{
		return self::ID_PREFIX . $collabId;
	}
}
