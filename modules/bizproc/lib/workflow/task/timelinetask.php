<?php

namespace Bitrix\Bizproc\Workflow\Task;

use Bitrix\Bizproc\Workflow\Task;
use Bitrix\Main\Type\DateTime;

class TimelineTask implements \JsonSerializable
{
	private Task $task;
	private ?int $userId = null;
	private ?string $approveType = null;

	public function __construct(Task $task)
	{
		$this->task = $task;
		if ($this->hasApproveType())
		{
			$parameters = $this->task->getParameters();
			$this->approveType = is_string($parameters['ApproveType'] ?? null) ? $parameters['ApproveType'] : 'all';
		}
	}

	public function setUserId(int $userId): static
	{
		$this->userId = $userId;

		return $this;
	}

	public function calculateExecutionTime(): ?int
	{
		$createdDate = $this->getCreatedDate();
		if (isset($createdDate))
		{
			return
				$this->task->isCompleted()
					? $this->task->getModified()->getTimestamp() - $createdDate->getTimestamp()
					: (new DateTime())->getTimestamp() - $createdDate->getTimestamp()
			;
		}

		return null;
	}

	/**
	 * @return int[]
	 */
	public function getTaskUserIds(): array
	{
		$ids = [];
		foreach ($this->task->getTaskUsers() as $taskUser)
		{
			$ids[] = $taskUser->getUserId();
		}

		return $ids;
	}

	private function checkViewRights(): bool
	{
		return isset($this->userId) && $this->task->hasViewRights($this->userId);
	}

	public function jsonSerialize(): array
	{
		$users = [];
		if (!$this->checkViewRights())
		{
			return [
				'canView' => false,
				'status' => $this->task->getStatus(),
			];
		}

		foreach ($this->task->getTaskUsers() as $taskUser)
		{
			$userData = [
				'id' => $taskUser->getUserId(),
				'status' => $taskUser->getStatus(),
			];

			if ($taskUser->hasDateUpdate() && $taskUser->get('DATE_UPDATE') !== null)
			{
				$dateUpdate = $taskUser->get('DATE_UPDATE');
			}
			else
			{
				$dateUpdate = new DateTime();
			}

			if ($this->getCreatedDate())
			{
				$userData['executionTime'] = $dateUpdate->getTimestamp() - $this->getCreatedDate()->getTimestamp();
			}

			$users[] = $userData;
		}

		$viewData = [
			'canView' => true,
			'id' => $this->task->getId(),
			'name' => $this->task->getName(),
			'status' => $this->task->getStatus(),
			'modified' => $this->task->getModified()->getTimestamp(),
			'executionTime' => $this->calculateExecutionTime(),
			'users' => $users,
		];

		if ($this->approveType)
		{
			$viewData['approveType'] = $this->approveType;
		}

		return $viewData;
	}

	private function getCreatedDate(): ?DateTime
	{
		return $this->task->hasCreatedDate() ? $this->task->get('CREATED_DATE') : null;
	}

	public function hasApproveType(): int
	{
		return in_array(
			$this->task->getActivity(),
			[
				'ApproveActivity',
				'ReviewActivity',
			],
			true,
		);
	}
}