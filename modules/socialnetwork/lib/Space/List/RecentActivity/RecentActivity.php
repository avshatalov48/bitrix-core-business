<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
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
			'SECONDARY_ENTITY_ID' => $recentActivityData->getSecondaryEntityId(),
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

	public function deleteMulti(array $idsToDelete): void
	{
		$idChunks = array_chunk($idsToDelete, 500);

		foreach ($idChunks as $idChunk)
		{
			SpaceUserRecentActivityTable::deleteByFilter(['ID' => $idChunk]);
		}
	}

	public function getIdsToDeleteByUserId(int $userId, string $typeId, int $entityId): array
	{
		$query = SpaceUserRecentActivityTable::query()
			->setSelect(['ID'])
			->where('USER_ID', $userId)
		;

		$this->prepareToDeleteQuery($query, $typeId, $entityId);

		$result = $query->fetchAll();

		$result = array_map(fn($item) => $item['ID'], $result);
		Collection::normalizeArrayValuesByInt($result);

		return $result;
	}

	private function prepareToDeleteQuery(Query $query, string $typeId, int $entityId): void
	{
		if (array_key_exists($typeId, Dictionary::COMMON_TO_COMMENT_ENTITY_TYPE))
		{
			$commonCondition = Query::filter()
				->where('TYPE_ID', $typeId)
				->where('ENTITY_ID', $entityId)
			;
			$secondaryCondition = Query::filter()
				->where('TYPE_ID', Dictionary::COMMON_TO_COMMENT_ENTITY_TYPE[$typeId])
				->where('SECONDARY_ENTITY_ID', $entityId)
			;

			$query
				->where(
					Query::filter()
						->logic(ConditionTree::LOGIC_OR)
						->where($commonCondition)
						->where($secondaryCondition)
				)
			;
		}
		else
		{
			$query
				->where('TYPE_ID', $typeId)
				->where('ENTITY_ID', $entityId)
			;
		}
	}

	public function getIdsToDeleteBySpaceId(int $spaceId, string $typeId, int $entityId): array
	{
		$query = SpaceUserRecentActivityTable::query()
			->setSelect(['ID'])
			->where('SPACE_ID', $spaceId)
		;

		$this->prepareToDeleteQuery($query, $typeId, $entityId);

		$result = $query->fetchAll();

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

		if (empty($queryResult))
		{
			return null;
		}

		$recentActivityData
			->setId($queryResult['ID'] ?? null)
			->setTypeId($queryResult['TYPE_ID'] ?? null)
			->setEntityId($queryResult['ENTITY_ID'] ?? null)
			->setDateTime($queryResult['DATETIME'] ?? null)
			->setSecondaryEntityId($queryResult['SECONDARY_ENTITY_ID'] ?? null)
		;

		return $recentActivityData;
	}
}