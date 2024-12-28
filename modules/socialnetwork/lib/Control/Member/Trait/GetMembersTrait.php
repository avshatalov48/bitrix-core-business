<?php

namespace Bitrix\Socialnetwork\Control\Member\Trait;

use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\UserToGroupTable;

trait GetMembersTrait
{
	protected function getMemberIds(int $groupId, ?string $role = null): array
	{
		$query = UserToGroupTable::query()
			->setSelect(['USER_ID'])
			->where('GROUP_ID', $groupId)
		;

		if ($role !== null)
		{
			$query->setFilter(['ROLE' => $role]);
		}

		$rows = $query->exec()->fetchAll();

		$memberIds = array_column($rows, 'USER_ID');

		Collection::normalizeArrayValuesByInt($memberIds, false);

		return $memberIds;
	}
}