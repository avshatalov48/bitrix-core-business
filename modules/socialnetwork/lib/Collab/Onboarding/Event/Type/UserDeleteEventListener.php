<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;

class UserDeleteEventListener extends AbstractEventListener
{
	public function onAfterUserDelete(int $userId): EventResult
	{
		$eventResult = new EventResult(EventResult::SUCCESS);

		if ($userId <= 0)
		{
			return $eventResult;
		}

		$this->queueService->deleteByUserIds($userId);

		return $eventResult;
	}
}
