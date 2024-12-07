<?php

namespace Bitrix\Socialnetwork\Permission\Rule\Trait;

use Bitrix\Socialnetwork\Permission\Model\GroupModel;
use Bitrix\Socialnetwork\Internals\Group\GroupEntity;
use Bitrix\Socialnetwork\Item\Workgroup\AccessManager;
use Bitrix\Socialnetwork\Space\Member;
use Bitrix\Socialnetwork\UserToGroupTable;

trait AccessTrait
{
	protected static array $relations = [];

	protected function getAccessManager(GroupModel $item, ?int $targetUserId, ?int $currentUserId): AccessManager
	{
		$group = $this->getEntityByModel($item);

		$target = $this->getRelation($item->getId(), $targetUserId);
		$current = $this->getRelation($item->getId(), $currentUserId);

		return new AccessManager(
			$group,
			$target,
			$current,
			[],
			[
				'userId' => $currentUserId,
			]
		);
	}

	protected function getEntityByModel(GroupModel $item): GroupEntity
	{
		return GroupEntity::wakeUpObject($item->getDomainObject());
	}

	protected function getRelation(int $groupId, ?int $userId): ?Member
	{
		if ($userId === null)
		{
			return null;
		}

		$key = $groupId . '_' . $userId;

		if (!isset(self::$relations[$key]))
		{
			self::$relations[$key] = UserToGroupTable::query()
				->setSelect(['ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID', 'AUTO_MEMBER'])
				->where('GROUP_ID', $groupId)
				->where('USER_ID', $userId)
				->fetchObject();
		}

		return self::$relations[$key];
	}
}