<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity;

use Bitrix\Socialnetwork\Internals\Space\RecentActivity\SpaceUserLatestActivityTable;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\LatestActivityData;

final class LatestActivity
{
	public function save(LatestActivityData $latestActivityData): void
	{
		$insertFields = [
			'USER_ID' => $latestActivityData->getUserId(),
			'SPACE_ID' => $latestActivityData->getSpaceId(),
			'ACTIVITY_ID' => $latestActivityData->getActivityId(),
		];
		$updateFields = [
			'ACTIVITY_ID' => $latestActivityData->getActivityId(),
		];

		SpaceUserLatestActivityTable::merge($insertFields, $updateFields);
	}

	/** @return array<LatestActivityData> */
	public function getByActivityIds(int $userId, array $activityIds): array
	{
		$queryResult = SpaceUserLatestActivityTable::query()
			->setSelect(['*'])
			->where('USER_ID', $userId)
			->whereIn('ACTIVITY_ID', $activityIds)
			->exec()
			->fetchAll()
		;

		$latestActivities = [];
		foreach ($queryResult as $item)
		{
			$latestActivities[] = LatestActivityData::createFromQueryResult($item);
		}

		return $latestActivities;
	}

	public function delete(int $id): void
	{
		SpaceUserLatestActivityTable::delete($id);
	}

	public function update(LatestActivityData $latestActivityData): void
	{
		SpaceUserLatestActivityTable::update($latestActivityData->getId(),[
			'ACTIVITY_ID' => $latestActivityData->getActivityId(),
		]);
	}
}