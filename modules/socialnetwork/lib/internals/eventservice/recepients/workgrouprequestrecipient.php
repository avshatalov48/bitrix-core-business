<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\EO_Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

final class WorkgroupRequestRecipient implements Collector
{
	private int $groupId;
	private EO_Workgroup|null $group = null;

	public function __construct(int $groupId)
	{
		$this->groupId = $groupId;
		$this->getGroup();
	}

	public function fetch(int $limit, int $offset): RecepientCollection
	{
		$memberIds = $this->getMemberIds();
		if (empty($memberIds))
		{
			return new RecepientCollection(...[]);
		}

		$res = UserTable::query()
			->setSelect(['ID', 'ACTIVE', 'IS_REAL_USER', 'IS_ONLINE'])
			->whereIn('ID', $memberIds)
			->where('ACTIVE', '=', 'Y')
			->where('IS_REAL_USER', '=', 'Y')
			->setLimit($limit)
			->setOffset($offset)
			->fetch()
		;

		$recipients = [];
		foreach ($res as $user)
		{
			$userId = $user['ID'] ?? 0;
			$isOnline = $user['IS_ONLINE'] ?? true;

			$recipients[] = new Recepient((int)$userId, (bool)$isOnline);
		}

		return new RecepientCollection(...$recipients);
	}

	private function getMemberIds(): array
	{
		if (!$this->getGroup())
		{
			return [];
		}

		$initiatePerms = $this->getGroup()->get('INITIATE_PERMS') ?? UserToGroupTable::ROLE_USER;
		$scrumMasterId = (int)$this->getGroup()->get('SCRUM_MASTER_ID');

		$members = UserToGroupTable::query()
			->setSelect(['USER_ID'])
			->where('GROUP_ID', $this->groupId)
			->where('ROLE', '<=', $initiatePerms)
			->fetchAll()
		;

		$memberIds = array_map(fn($member): int => (int)$member['USER_ID'], $members);
		if ($scrumMasterId > 0)
		{
			$memberIds = array_unique(array_merge($memberIds, [$scrumMasterId]));
		}

		return $memberIds;
	}

	private function getGroup(): EO_Workgroup|null
	{
		if ($this->group)
		{
			return $this->group;
		}

		$this->group = WorkgroupTable::query()
			->setSelect(['ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID', 'INITIATE_PERMS'])
			->where('ID', $this->groupId)
			->fetchObject()
		;

		return $this->group;
	}
}
