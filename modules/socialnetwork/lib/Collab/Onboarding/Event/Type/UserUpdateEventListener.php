<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;

class UserUpdateEventListener extends AbstractEventListener
{
	public function onAfterUserFired(array $data): EventResult
	{
		$eventResult = new EventResult(EventResult::SUCCESS);

		$userId = (int)($data['ID'] ?? 0);
		if ($userId <= 0)
		{
			return $eventResult;
		}

		$isFired = ($data['ACTIVE'] ?? '') === 'N';

		if ($isFired)
		{
			$this->queueService->deleteByUserIds($userId);
		}

		return $eventResult;
	}
}
