<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Collab\Entity\Event\CollabEntityAddEvent;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;

class CollabEntityAddEventListener extends AbstractEventListener
{
	public function onCollabEntityAdd(CollabEntityAddEvent $event): EventResult
	{
		$entity = $event->getEntity();

		if ($this->isExpectedEntityType($entity->getType()))
		{
			$collabId = $entity->getCollab()->getId();

			$this->deleteJobsAfterAddCollabEntity($collabId);
		}

		return new EventResult(EventResult::SUCCESS);
	}

	private function isExpectedEntityType(EntityType $type): bool
	{
		return in_array($type, [EntityType::Task, EntityType::CalendarEvent, EntityType::File], true);
	}

	private function deleteJobsAfterAddCollabEntity(int $collabId): void
	{
		$this->queueService->deleteByFilter([
			'COLLAB_IDS' => [$collabId],
			'JOB_TYPES' => [Type::CreatedTaskOrMeetingOrFileThreeDays->value],
		]);
	}
}
