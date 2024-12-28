<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Provider;

use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class CollabDefaultProvider
{
	use InstanceTrait;

	public function getCollab(int $userId): ?Collab
	{
		$lastActivityProvider = CollabLastActivityProvider::getInstance();

		$lastActiveCollab = $lastActivityProvider->getCollab($userId);
		if ($lastActiveCollab !== null)
		{
			return $lastActiveCollab;
		}

		$collabProvider = CollabProvider::getInstance();

		$collabsByUser = $collabProvider->getListByUserId($userId);
		$firstUserCollab = $collabsByUser->getFirst();
		if ($firstUserCollab === null)
		{
			return null;
		}

		return $collabProvider->getCollab($firstUserCollab->getId());
	}
}
