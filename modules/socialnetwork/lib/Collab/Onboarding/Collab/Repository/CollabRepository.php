<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository;

use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Entity\FirstAddedMemberData;
use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository\Cache\CollabCache;
use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository\Cache\CollabCacheInterface;
use Bitrix\Socialnetwork\Helper\InstanceTrait;
use Bitrix\Socialnetwork\UserToGroupTable;

class CollabRepository
{
	use InstanceTrait;

	private CollabCacheInterface $cache;

	public function __construct()
	{
		$this->cache = CollabCache::getInstance();
	}

	public function getFirstAddedMemberData(int $collabId): ?FirstAddedMemberData
	{
		if ($collabId <= 0)
		{
			return null;
		}

		$firstAddedData = $this->cache->getFirstAddedMemberData($collabId);
		if (null === $firstAddedData)
		{
			$row = UserToGroupTable::query()
				->setSelect(['DATE_CREATE'])
				->where('GROUP_ID', $collabId)
				->where('ROLE', '<', UserToGroupTable::ROLE_REQUEST)
				->setOrder(['DATE_CREATE' => 'ASC'])
				->setOffset(1)
				->setLimit(1)
				->exec()
				->fetch();

			$date = $row['DATE_CREATE'] ?? null;

			$firstAddedData = new FirstAddedMemberData($collabId, $date, $date !== null);
			$this->cache->save($firstAddedData);
		}

		return $firstAddedData;
	}
}
