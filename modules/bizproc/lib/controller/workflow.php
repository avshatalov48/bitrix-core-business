<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetTimelineRequest;
use Bitrix\Bizproc\Api\Service\UserService;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Main\Engine\CurrentUser;

class Workflow extends Base
{
	private function getWorkflowEfficiency(int $currentDuration, ?int $averageDuration): string
	{
		if (null === $averageDuration)
		{
			return 'first';
		}
		if ($currentDuration < $averageDuration)
		{
			return 'fast';
		}
		if ($currentDuration < ($averageDuration + 259200)) // трое суток
		{
			return  'slow';
		}
		return  'stopped';
	}

	public function getTimelineAction(string $workflowId): ?array
	{
		$workflowStateService = new WorkflowStateService();

		$request = new GetTimelineRequest(workflowId: $workflowId, userId: CurrentUser::get()->getId());
		$response = $workflowStateService->getTimeline($request);
		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		$timeline = $response->getTimeline();

		$workflowState = $timeline->getWorkflowState();

		$userIds = [$workflowState->getStartedBy()];
		foreach ($timeline->getTasks() as $task)
		{
			$userIds = array_merge($userIds, $task->getTaskUserIds());
		}

		$userService = new UserService();

		$request = new UsersToGet($userIds);
		$response = $userService->getUsersView($request);

		if ($response->isSuccess())
		{
			$data = $timeline->jsonSerialize();
			$data['users'] = $response->getUserViews();

			$data['stats'] = [
				'averageDuration' => $duration = $workflowStateService->getAverageWorkflowDuration(
					new GetAverageWorkflowDurationRequest($workflowState->getWorkflowTemplateId())
				)->getAverageDuration(),
				'efficiency' => $this->getWorkflowEfficiency(
					$timeline->getExecutionTime() ?? 0,
					$duration
				),
			];

			return $data;
		}
		else
		{
			$this->addErrors($response->getErrors());

			return null;
		}
	}
}
