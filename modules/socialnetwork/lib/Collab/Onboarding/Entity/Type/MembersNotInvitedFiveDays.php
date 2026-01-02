<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\ChatNotification;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result\JobResult;
use Bitrix\Socialnetwork\Collab\Onboarding\Notification\NotificationService;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\ClosestWorkDate;
use CTimeZone;

class MembersNotInvitedFiveDays extends AbstractJob
{
	public const JOB_OFFSET = self::SECONDS_PER_DAY * 5;

	public function getType(): Type
	{
		return Type::MembersNotInvitedFiveDays;
	}

	public function isImmediatelyExecuted(): bool
	{
		return true;
	}

	protected function createNextExecutionDate(): DateTime
	{
		$closestWorkDate = (new ClosestWorkDate())->get(self::JOB_OFFSET);

		$userTimeOffset = CTimeZone::GetOffset($this->userId);
		$closestWorkDate->add("-{$userTimeOffset} seconds");

		return $closestWorkDate;
	}

	public function __invoke(): JobResult
	{
		$notificationService = new NotificationService([
			ChatNotification::getInstance(),
		]);

		$notificationService->send(
			Loc::getMessage('SOCIALNETWORK_COLLAB_ONBOARDING_MEMBERS_NOT_INVITED_FIVE_DAYS'),
			$this->userId,
			$this->collabId
		);

		return new JobResult();
	}
}
