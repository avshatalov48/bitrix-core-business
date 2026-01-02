<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabDeleteEvent;

class CollabDeleteEventListener extends AbstractEventListener
{
	public function onCollabDelete(CollabDeleteEvent $event): EventResult
	{
		$collabId = $event->getCollab()->getId();

		$this->queueService->deleteByCollabIds($collabId);

		return new EventResult(EventResult::SUCCESS);
	}
}
