<?php

namespace Bitrix\Bizproc\Task\Data\ExternalEventTask;

final class UsersByStatus
{
	public readonly array $completed;
	public readonly array $waiting;
	public readonly array $remove;
	public readonly array $markCompleted;

	public function __construct(
		array $allTaskUsers,
		array $state,
		array $removeUsers,
		array $externallyCompleted = null
	)
	{
		$users = ['completed' => [], 'waiting' => [], 'remove' => []];
		foreach ($allTaskUsers as $user)
		{
			$userId = (int)$user['USER_ID'];
			$status = 'completed';

			if ((int)$user['STATUS'] === \CBPTaskUserStatus::Waiting)
			{
				$status = 'waiting';
				if (in_array($userId, $removeUsers, true))
				{
					$allowableEvents = \CBPDocument::getAllowableEvents($userId, $this->getUserGroups($userId), $state);
					$status = $allowableEvents ? 'waiting' : 'remove';
				}
			}

			$users[$status][] = $userId;
		}

		$markCompleted = [];
		if ($users['remove'] && $externallyCompleted)
		{
			$intersect = array_intersect($externallyCompleted, $users['remove']);
			if ($intersect)
			{
				$markCompleted = $intersect;
				$users['remove'] = array_diff($users['remove'], $intersect);
			}
		}

		$this->completed = $users['completed'];
		$this->waiting = $users['waiting'];
		$this->remove = $users['remove'];
		$this->markCompleted = $markCompleted;
	}

	private function getUserGroups(int $userId): array
	{
		$currentUser = \Bitrix\Main\Engine\CurrentUser::get();

		return (int)$currentUser->getId() === $userId ? $currentUser->getUserGroups() : \CUser::GetUserGroup($userId);
	}
}
