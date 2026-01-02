<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;
use Bitrix\Socialnetwork\Collab\Onboarding\Collab\Repository\CollabRepository;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobCollection;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\JobFactory;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;

class CollaberAcceptInvitationEventListener extends AbstractEventListener
{
	private CollabRepository $collabRepository;

	public function __construct()
	{
		parent::__construct();

		$this->collabRepository = CollabRepository::getInstance();
	}

	public function onCollaberAcceptInvitation(array $params): EventResult
	{
		$initiatorId = (int)($params['INITIATED_BY_USER_ID'] ?? 0);
		$collaberId = (int)($params['USER_ID'] ?? 0);
		$groupId = (int)($params['GROUP_ID'] ?? 0);

		if ($initiatorId <= 0 || $collaberId <= 0 || $groupId <= 0)
		{
			return new EventResult(EventResult::SUCCESS);
		}

		if (!$this->isFirstMemberAlreadyAdded($groupId))
		{
			$this->addJobsAfterFirstCollaberAcceptInvitation($groupId, $initiatorId, $collaberId);
		}

		$this->deleteJobsAfterCollaberAcceptingInvitation($groupId);

		return new EventResult(EventResult::SUCCESS);
	}

	private function isFirstMemberAlreadyAdded(int $collabId): bool
	{
		$firstAddedData = $this->collabRepository->getFirstAddedMemberData($collabId);

		return (bool)$firstAddedData?->isFirstMemberAdded;
	}

	private function addJobsAfterFirstCollaberAcceptInvitation(int $collabId, int $initiatorId, int $collaberId): void
	{
		$jobCollection = new JobCollection(
			JobFactory::create($collabId, $initiatorId, Type::StartChattingOneDay->value),
			JobFactory::create($collabId, $initiatorId, Type::CreatedTaskOrMeetingOrFileThreeDays->value),
			JobFactory::create($collabId, $collaberId, Type::CreatedTaskOrMeetingOrFileThreeDays->value),
		);

		$this->queueService->add($jobCollection);
	}

	private function deleteJobsAfterCollaberAcceptingInvitation(int $collabId): void
	{
		$this->queueService->deleteByFilter([
			'COLLAB_IDS' => [$collabId],
			'JOB_TYPES' => [
				Type::CollaberNotAcceptInvitationOneDay->value,
				Type::CollaberNotAcceptInvitationTwoDays->value,
			],
		]);
	}
}
