<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Event\CollabUpdateEvent;
use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository\CollabRepository;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobFactory;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type\CreatedTaskOrMeetingOrFileThreeDays;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\ClosestWorkDate;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use CTimeZone;

class CollabUpdateEventListener extends AbstractEventListener
{
	private CollabRepository $collabRepository;

	public function __construct()
	{
		parent::__construct();

		$this->collabRepository = CollabRepository::getInstance();
	}

	public function onCollabUpdate(CollabUpdateEvent $event): EventResult
	{
		$jobCollection = new JobCollection();

		$command = $event->getCommand();
		$collabId = $command->getId();
		$initiatorId = $command->getInitiatorId();

		if ($this->mayCreateCollabEntityJobs($initiatorId, $collabId))
		{
			$jobCollection->merge($this->getCollabEntityJobs($command));
		}

		if (!$this->isFirstMemberJustNowAdded($event))
		{
			$this->queueService->add($jobCollection);

			return new EventResult(EventResult::SUCCESS);
		}

		if ($command->getAddInvitedMembers())
		{
			$jobCollection->merge($this->getJobsAfterFirstCollaberInvited($collabId, $initiatorId));
		}

		if ($command->getAddMembers() || $command->getAddModeratorMembers())
		{
			$jobCollection->merge($this->getJobsAfterFirstEmployeeInvited($collabId, $initiatorId));
		}

		$this->queueService->add($jobCollection);
		$this->deleteJobsAfterMemberAdded($collabId);

		return new EventResult(EventResult::SUCCESS);
	}

	private function isFirstMemberJustNowAdded(CollabUpdateEvent $event): bool
	{
		$collabBefore = $event->getCollabBefore();
		$memberBeforeIds = $collabBefore->getUserMemberIds();

		$isSingleMember = count($memberBeforeIds) === 1;

		return $isSingleMember && $this->hasAddedMembersInCommand($event->getCommand());
	}

	private function hasAddedMembersInCommand(CollabUpdateCommand $command): bool
	{
		return
			$command->getAddInvitedMembers()
			|| $command->getAddMembers()
			|| $command->getAddModeratorMembers()
		;
	}

	private function mayCreateCollabEntityJobs(int $initiatorId, int $collabId): bool
	{
		$firstAddedData = $this->collabRepository->getFirstAddedMemberData($collabId);

		if (!$firstAddedData || !$firstAddedData->isFirstMemberAdded)
		{
			return true;
		}

		$lastDateForJobCreation = (new ClosestWorkDate())->get(
			CreatedTaskOrMeetingOrFileThreeDays::JOB_OFFSET,
			$firstAddedData->addedDate->toUserTime()
		);

		$userTimeOffset = CTimeZone::GetOffset($initiatorId);
		$lastDateForJobCreation->add("-{$userTimeOffset} seconds");

		return time() < $lastDateForJobCreation->getTimestamp();
	}

	private function getCollabEntityJobs(CollabUpdateCommand $command): JobCollection
	{
		$addedMembers = [];

		if ($command->getAddMembers())
		{
			$addedMembers = $command->getAddMembers();
		}

		if ($command->getAddModeratorMembers())
		{
			$addedMembers = array_merge($addedMembers, $command->getAddModeratorMembers());
		}

		$firstAddedData = $this->collabRepository->getFirstAddedMemberData($command->getId());
		if ($firstAddedData?->isFirstMemberAdded && $command->getAddInvitedMembers())
		{
			$addedMembers = array_merge($addedMembers, $command->getAddInvitedMembers());
		}

		$jobCollection = new JobCollection();

		if (empty($addedMembers))
		{
			return $jobCollection;
		}

		$addedMemberIds = (new AccessCodeConverter(...$addedMembers))->getUserIds();

		foreach ($addedMemberIds as $memberId)
		{
			$jobCollection->add(
				JobFactory::create($command->getId(), $memberId, Type::CreatedTaskOrMeetingOrFileThreeDays->value)
			);
		}

		return $jobCollection;
	}

	private function getJobsAfterFirstCollaberInvited(int $collabId, int $initiatorId): JobCollection
	{
		return new JobCollection(
			JobFactory::create($collabId, $initiatorId, Type::CollaberNotAcceptInvitationOneDay->value),
			JobFactory::create($collabId, $initiatorId, Type::CollaberNotAcceptInvitationTwoDays->value),
		);
	}

	private function getJobsAfterFirstEmployeeInvited(int $collabId, int $initiatorId): JobCollection
	{
		return new JobCollection(
			JobFactory::create($collabId, $initiatorId, Type::StartChattingOneDay->value),
			JobFactory::create($collabId, $initiatorId, Type::CreatedTaskOrMeetingOrFileThreeDays->value),
		);
	}

	private function deleteJobsAfterMemberAdded(int $collabId): void
	{
		$this->queueService->deleteByFilter([
			'COLLAB_IDS' => [$collabId],
			'JOB_TYPES' => [
				Type::MembersNotInvitedOneDay->value,
				Type::MembersNotInvitedTwoDays->value,
				Type::MembersNotInvitedFourDays->value,
				Type::MembersNotInvitedFiveDays->value,
			]
		]);
	}
}
