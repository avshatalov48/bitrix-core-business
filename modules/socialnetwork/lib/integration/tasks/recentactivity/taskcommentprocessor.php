<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\RecentActivity;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Dictionary;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\AbstractProcessor;

final class TaskCommentProcessor extends AbstractProcessor
{
	public function isAvailable(): bool
	{
		return Loader::includeModule('tasks');
	}

	protected function getTypeId(): string
	{
		return Dictionary::ENTITY_TYPE['task_comment'];
	}

	public function process(): void
	{
		$groupId = (int)($this->event->getData()['GROUP_ID'] ?? null);
		$taskId = (int)($this->event->getData()['ID'] ?? null);
		$commentId = (int)($this->event->getData()['MESSAGE_ID'] ?? null);

		if ($taskId <= 0 || $commentId <= 0 || $groupId < 0)
		{
			return;
		}

		switch ($this->event->getType())
		{
			case EventDictionary::EVENT_SPACE_TASK_COMMENT_ADD:
				$this->onCommentAdd($taskId, $commentId, $groupId);
				break;
			case EventDictionary::EVENT_SPACE_TASK_COMMENT_DELETE:
				$this->onCommentDelete($commentId);
				break;
			default:
				break;
		}
	}

	private function onCommentDelete(int $commentId): void
	{
		$this->deleteRecentActivityData($commentId);
	}

	private function onCommentAdd(int $taskId, int $commentId, int $groupId): void
	{
		if ($groupId > 0)
		{
			$this->saveRecentActivityData($groupId, $commentId, $taskId);
		}
		else
		{
			$this->saveRecentActivityData(0, $commentId, $taskId);
		}
	}
}
