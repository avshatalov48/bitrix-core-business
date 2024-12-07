<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Socialnetwork\Internals\Space\RecentActivity\SpaceUserLatestActivityTable;
use Bitrix\Socialnetwork\Internals\Space\RecentActivity\SpaceUserRecentActivityTable;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\LatestActivityData;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

final class Service
{
	private RecentActivity $recentActivity;
	private LatestActivity $latestActivity;

	private static array $cache = [];

	public function __construct()
	{
		$this->recentActivity = new RecentActivity();
		$this->latestActivity = new LatestActivity();
	}

	public function save(RecentActivityData $recentActivityData): bool
	{
		$id = $this->recentActivity->save($recentActivityData);
		if ($id <= 0)
		{
			return false;
		}

		$recentActivityData->setId($id);

		$currentLatestActivity = $this->get(
			$recentActivityData->getUserId(),
			$recentActivityData->getSpaceId()
		);

		if ($recentActivityData->getDateTime()->getTimestamp() < $currentLatestActivity->getDateTime()?->getTimestamp())
		{
			return false;
		}

		if ($recentActivityData->getId() !== $currentLatestActivity->getId())
		{
			$this->latestActivity->save(LatestActivityData::createFromRecentActivityData($recentActivityData));
		}

		$this->saveInCache($recentActivityData);

		return true;
	}

	public function deleteByUserId(int $userId, string $entityType, int $entityId): void
	{
		$idsToDelete = $this->recentActivity->getIdsToDeleteByUserId($userId, $entityType, $entityId);

		if (empty($idsToDelete))
		{
			return;
		}

		$this->recentActivity->deleteMulti($idsToDelete);

		$latestActivities = $this->latestActivity->getByUserId($userId, $idsToDelete);
		$this->deleteLatestActivities($latestActivities);

		$this->clearCache();
	}

	public function deleteBySpaceId(int $spaceId, string $entityType, int $entityId): void
	{
		$idsToDelete = $this->recentActivity->getIdsToDeleteBySpaceId($spaceId, $entityType, $entityId);
		if (empty($idsToDelete))
		{
			return;
		}

		$this->recentActivity->deleteMulti($idsToDelete);

		$latestActivities = $this->latestActivity->getBySpaceId($spaceId, $idsToDelete);
		$this->deleteLatestActivities($latestActivities);

		$this->clearCache();
	}

	private function deleteLatestActivities(array $latestActivities): void
	{
		$idsToDelete = [];
		foreach ($latestActivities as $latestActivity)
		{
			$newestActivity = $this->recentActivity->getNewestActivity(
				$latestActivity->getUserId(),
				$latestActivity->getSpaceId(),
			);

			if ($newestActivity)
			{
				$latestActivity->setActivityId($newestActivity->getId());
				$this->latestActivity->update($latestActivity);
			}
			else
			{
				$idsToDelete[] = $latestActivity->getId();
			}
		}

		if (!empty($idsToDelete))
		{
			$this->latestActivity->deleteMulti($idsToDelete);
		}
	}

	public function get(int $userId, int $spaceId): RecentActivityData
	{
		$cachedValue = $this->getFromCache($userId, $spaceId);

		if (!is_null($cachedValue))
		{
			return $cachedValue;
		}

		$queryResult =
			SpaceUserRecentActivityTable::query()
				->setSelect(['*', 'LATEST_ACTIVITY'])
				->registerRuntimeField(
					(new Reference(
						'LATEST_ACTIVITY',
						SpaceUserLatestActivityTable::class,
						Join::on('this.ID','ref.ACTIVITY_ID'),
					))
						->configureJoinType(Join::TYPE_RIGHT)
				)
				->where('LATEST_ACTIVITY.USER_ID', $userId)
				->where('LATEST_ACTIVITY.SPACE_ID', $spaceId)
				->fetch()
		;

		$recentActivityData =
			(new RecentActivityData())
				->setSpaceId($spaceId)
				->setUserId($userId)
		;

		if (!empty($queryResult))
		{
			$recentActivityData
				->setId($queryResult['ID'] ?? null)
				->setTypeId($queryResult['TYPE_ID'] ?? null)
				->setEntityId($queryResult['ENTITY_ID'] ?? null)
				->setDateTime($queryResult['DATETIME'] ?? null)
				->setSecondaryEntityId($queryResult['SECONDARY_ENTITY_ID'] ?? null)
			;

			$this->saveInCache($recentActivityData);
		}

		return $recentActivityData;
	}

	private function getCacheKey(int $userId, int $spaceId): string
	{
		return "{$userId}_$spaceId";
	}

	private function saveInCache(RecentActivityData $recentActivityData): void
	{
		$cacheKey = $this->getCacheKey($recentActivityData->getUserId(), $recentActivityData->getSpaceId());
		self::$cache[$cacheKey] = clone $recentActivityData;
	}

	private function getFromCache(int $userId, int $spaceId): ?RecentActivityData
	{
		return self::$cache[$this->getCacheKey($userId, $spaceId)] ?? null;
	}

	private function clearCache(): void
	{
		self::$cache = [];
	}
}