<?php

namespace Bitrix\Bizproc\UI;

use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Request\WorkflowFacesService\GetDataRequest;
use Bitrix\Bizproc\Api\Response\WorkflowFacesService\GetDataByStepsResponse;
use Bitrix\Bizproc\Api\Service\UserService;
use Bitrix\Bizproc\Api\Service\WorkflowAccessService;
use Bitrix\Bizproc\Api\Service\WorkflowFacesService;

class WorkflowFacesView implements \JsonSerializable
{
	private string $workflowId;
	private int $runningTaskId;
	private array $usersView = [];
	private ?GetDataByStepsResponse $data = null;

	public function __construct(string $workflowId, ?int $runningTaskId = null)
	{
		$this->workflowId = $workflowId;
		$this->runningTaskId = max($runningTaskId ?? 0, 0);
	}

	public function jsonSerialize(): array
	{
		$data = $this->loadData();
		if (!$data->isSuccess())
		{
			return [];
		}

		$this->loadUsersView($this->getUniqueUserIds());

		$result = [
			'workflowId' => $this->workflowId,
			'steps' => $this->getSteps(),
			'timeStep' => $this->getTimeStep(),
			'isWorkflowFinished' => $data->getIsWorkflowFinished(),
		];

		$progressBox = $data->getProgressBox();
		if ($progressBox && $progressBox->getProgressTasksCount() > 0)
		{
			$result['progressBox'] = $progressBox->getData();
		}

		return $result;
	}

	public function getUniqueUserIds(): array
	{
		$data = $this->loadData();
		if ($data->isSuccess())
		{
			return $data->getUniqueUserIds();
		}

		return [];
	}

	public function getSteps(): array
	{
		$data = $this->loadData();
		if (!$data->isSuccess())
		{
			return [];
		}

		$steps = [];
		foreach ($data->getSteps() as $step)
		{
			if ($step)
			{
				$stepData = $step->getData();
				$stepData['avatarsData'] = $this->getStepAvatars($step->getAvatars());

				if ($step->getDuration() <= 0)
				{
					$stepData['duration'] = $step::getEmptyDurationText();
				}

				$steps[] = $stepData;
			}
		}

		return $steps;
	}

	public function getTimeStep(): ?array
	{
		$data = $this->loadData();
		if ($data->isSuccess())
		{
			return $data->getTimeStep()?->getData();
		}

		return null;
	}

	private function loadData(): GetDataByStepsResponse
	{
		if ($this->data === null)
		{
			$workflowFacesService = new WorkflowFacesService(
				new WorkflowAccessService()
			);

			$request = new GetDataRequest(
				workflowId: $this->workflowId,
				runningTaskId: $this->runningTaskId,
				skipAccessCheck: true,
			);

			$this->data = $workflowFacesService->getDataBySteps($request);
		}

		return $this->data;
	}

	private function loadUsersView(array $userIds): void
	{
		$userService = new UserService();
		$response = $userService->getUsersView(new UsersToGet($userIds));
		if ($response->isSuccess())
		{
			foreach ($response->getUserViews() as $userView)
			{
				$userId = $userView->getUserId();

				$this->usersView[$userId] = [
					'id' => $userId,
					'avatarUrl' => $userView->getUserAvatar(),
				];
			}
		}
	}

	private function getStepAvatars(array $userIds): array
	{
		$result = [];
		foreach ($userIds as $userId)
		{
			$result[] = $this->getUserById((int)$userId);
		}

		return $result;
	}

	private function getUserById(int $userId): array
	{
		if ($userId <= 0)
		{
			return ['id' => 0, 'avatarUrl' => null];
		}

		if (array_key_exists($userId, $this->usersView))
		{
			return $this->usersView[$userId];
		}

		return [];
	}
}
