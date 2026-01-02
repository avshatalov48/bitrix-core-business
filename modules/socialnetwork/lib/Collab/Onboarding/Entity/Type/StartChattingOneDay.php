<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Entity\Type;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Collab\Onboarding\Entity\AbstractJob;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\ChatNotification;
use Bitrix\Socialnetwork\Collab\Onboarding\Integration\Im\ChatService;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result\JobResult;
use Bitrix\Socialnetwork\Collab\Onboarding\Notification\NotificationService;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\ClosestWorkDate;
use CTimeZone;

class StartChattingOneDay extends AbstractJob
{
	public const JOB_OFFSET = self::SECONDS_PER_DAY;

	public function getType(): Type
	{
		return Type::StartChattingOneDay;
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
		$jobResult = new JobResult();

		if ($this->createdDate === null)
		{
			$jobResult->addError(new Error('The created date cannot be null'));

			return $jobResult;
		}

		if (!$this->isStartChatting())
		{
			$this->sendNotification();
		}

		return $jobResult;
	}

	private function isStartChatting(): bool
	{
		$collab = CollabProvider::getInstance()->getCollab($this->collabId);
		if ($collab === null)
		{
			return false;
		}

		return ChatService::getInstance()->isExistUserMessageByPeriod(
			$collab->getChatId(),
			$this->createdDate,
			$this->nextExecution,
		);
	}

	private function sendNotification(): void
	{
		$notificationService = new NotificationService([
			ChatNotification::getInstance(),
		]);

		$notificationService->send(
			Loc::getMessage('SOCIALNETWORK_COLLAB_ONBOARDING_START_CHATTING'),
			$this->userId,
			$this->collabId
		);
	}
}
