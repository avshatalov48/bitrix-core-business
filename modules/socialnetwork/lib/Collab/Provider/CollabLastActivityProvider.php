<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Socialnetwork\Collab\Activity\LastActivity;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Internals\CollabLastActivityTable;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class CollabLastActivityProvider
{
	use InstanceTrait;

	public function get(int $userId): ?LastActivity
	{
		$query = CollabLastActivityTable::query()
			->setSelect(['USER_ID', 'COLLAB_ID', 'ACTIVITY_DATE'])
			->where('MEMBER.USER_ID', $userId);

		$entity = $query->exec()->fetchObject();

		if ($entity === null)
		{
			return null;
		}

		return new LastActivity(
			$entity->getUserId(),
			$entity->getCollabId(),
			$entity->getActivityDate()
		);
	}

	public function getCollab(int $userId): ?Collab
	{
		$lastActivity = $this->get($userId);
		if ($lastActivity === null)
		{
			return null;
		}

		return CollabProvider::getInstance()->getCollab($lastActivity->collabId);
	}
}