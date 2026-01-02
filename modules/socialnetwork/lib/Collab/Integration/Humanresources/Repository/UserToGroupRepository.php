<?php

namespace Bitrix\Socialnetwork\Collab\Integration\Humanresources\Repository;

use Bitrix\HumanResources\Item\NodeRelation;
use Bitrix\Socialnetwork\UserToGroupTable;

class UserToGroupRepository
{
	public function getForRelation(NodeRelation $relation, array $filteredUsers): array
	{
		if (empty($filteredUsers))
		{
			return [];
		}

		return UserToGroupTable::query()
			->setSelect(['USER_ID'])
			->where('GROUP_ID', $relation->entityId)
			->whereIn('USER_ID', $filteredUsers)
			->where('INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_STRUCTURE)
			->exec()
			->fetchAll()
		;
	}
}
