<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity;

use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\Internals\Space\RecentActivity\SpaceUserRecentActivityTable;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

final class RecentActivity
{
	public function save(RecentActivityData $recentActivityData): ?int
	{
		$insertFields = [
			'USER_ID' => $recentActivityData->getUserId(),
			'SPACE_ID' => $recentActivityData->getSpaceId(),
			'TYPE_ID' => $recentActivityData->getTypeId(),
			'ENTITY_ID' => $recentActivityData->getEntityId(),
			'DATETIME' => $recentActivityData->getDateTime(),
		];

		$updateFields = [
			'DATETIME' => $recentActivityData->getDateTime(),
		];

		SpaceUserRecentActivityTable::merge($insertFields, $updateFields, SpaceUserRecentActivityTable::getUniqueFields());

		$id = SpaceUserRecentActivityTable::query()
			->setSelect(['ID'])
			->where('SPACE_ID', $recentActivityData->getSpaceId())
			->where('TYPE_ID', $recentActivityData->getTypeId())
			->where('ENTITY_ID', $recentActivityData->getEntityId())
			->where('USER_ID', $recentActivityData->getUserId())
			->fetch()
		;

		$id = (int)($id['ID'] ?? null);

		return $id > 0 ? $id : null;
	}

	public function delete(array $idsToDelete): void
	{
		SpaceUserRecentActivityTable::deleteByFilter([
			'ID' => $idsToDelete,
		]);
	}

	public function getIdsToDelete(int $userId, string $typeId, int $entityId): array
	{
		$result = SpaceUserRecentActivityTable::query()
			->setSelect(['ID'])
			->where('USER_ID', $userId)
			->where('TYPE_ID', $typeId)
			->where('ENTITY_ID', $entityId)
			->exec()
			->fetchAll()
		;

		$result = array_map(fn($item) => $item['ID'], $result);
		Collection::normalizeArrayValuesByInt($result);

		return $result;
	}

	public function getNewestActivity(int $userId, int $spaceId): ?RecentActivityData
	{
		$queryResult = SpaceUserRecentActivityTable::query()
			->setSelect(['*'])
			->where('SPACE_ID', $spaceId)
			->where('USER_ID', $userId)
			->addOrder('DATETIME', 'DESC')
			->setLimit(1)
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
			;

			return $recentActivityData;
		}
		else
		{
			return null;
		}
	}
}