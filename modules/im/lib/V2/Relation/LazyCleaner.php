<?php

namespace Bitrix\Im\V2\Relation;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Application;

class LazyCleaner
{
	private const MAX_DELETIONS_PER_HIT = 20;

	private static self $instance;

	private array $toDelete = [];
	private int $deletionCount = 0;
	private bool $isScheduled = false;

	private function __construct()
	{
	}

	public static function getInstance(): static
	{
		self::$instance ??= new static();

		return self::$instance;
	}

	public function canMarkForDeletion(): bool
	{
		return $this->deletionCount < self::MAX_DELETIONS_PER_HIT;
	}

	public function markForDeletion(int $chatId, int $userId): bool
	{
		if (!$this->canMarkForDeletion())
		{
			return false;
		}

		if (isset($this->toDelete[$chatId][$userId]))
		{
			return true;
		}

		$this->deletionCount++;
		$this->toDelete[$chatId][$userId] = $userId;
		$this->schedule();

		return true;
	}

	private function schedule(): void
	{
		if ($this->isScheduled)
		{
			return;
		}

		$this->isScheduled = true;
		Application::getInstance()->addBackgroundJob(fn () => $this->delete());
	}

	private function delete(): void
	{
		$this->isScheduled = false;

		foreach ($this->toDelete as $chatId => $userIds)
		{
			$chat = Chat::getInstance($chatId);
			foreach ($userIds as $userId)
			{
				$chat->deleteUser($userId, new DeleteUserConfig(withMessage: false, withNotification: false));
			}
		}

		$this->toDelete = [];
	}
}