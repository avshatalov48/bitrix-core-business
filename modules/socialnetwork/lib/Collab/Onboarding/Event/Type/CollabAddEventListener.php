<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabAddEvent;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobFactory;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;

class CollabAddEventListener extends AbstractEventListener
{
	public function onCollabAdd(CollabAddEvent $event): EventResult
	{
		$collab = $event->getCollab();
		$collabId = $collab->getId();

		$members = $collab->getUserMemberIds();

		$jobCollection = (count($members) === 1)
			? $this->getJobsMemberNotInvited($collabId, $event->getCommand()->getInitiatorId())
			: $this->getJobsFirstMemberAdded($collabId, $event->getCommand()->getInitiatorId())
		;

		$this->queueService->add($jobCollection);

		return new EventResult(EventResult::SUCCESS);
	}

	private function getJobsMemberNotInvited(int $collabId, int $userId): JobCollection
	{
		return new JobCollection(
			JobFactory::create($collabId, $userId, Type::MembersNotInvitedOneDay->value),
			JobFactory::create($collabId, $userId, Type::MembersNotInvitedTwoDays->value),
			JobFactory::create($collabId, $userId, Type::MembersNotInvitedFourDays->value),
			JobFactory::create($collabId, $userId, Type::MembersNotInvitedFiveDays->value),
		);
	}

	private function getJobsFirstMemberAdded(int $collabId, int $userId): JobCollection
	{
		return new JobCollection(
			JobFactory::create($collabId, $userId, Type::StartChattingOneDay->value),
			JobFactory::create($collabId, $userId, Type::CreatedTaskOrMeetingOrFileThreeDays->value),
		);
	}
}
