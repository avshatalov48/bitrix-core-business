<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Executor\Error\JobNotCompletedError;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result\JobResult;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\ClosestWorkDate;
use CTimeZone;

class CollaberNotAcceptInvitationOneDay extends AbstractJob
{
	public const LIFE_TIME = self::SECONDS_PER_DAY;
	public const JOB_OFFSET = self::SECONDS_PER_DAY;

	public function getType(): Type
	{
		return Type::CollaberNotAcceptInvitationOneDay;
	}

	public function isImmediatelyExecuted(): bool
	{
		return false; // Displayed only when the user has entered the collab
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
		$isCompletedByLifeTime = time() > ($this->nextExecution->getTimestamp() + static::LIFE_TIME);

		if ($isCompletedByLifeTime)
		{
			return new JobResult();
		}

		return (new JobResult())->addError(new JobNotCompletedError());
	}
}
