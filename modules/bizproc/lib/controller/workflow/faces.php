<?php

namespace Bitrix\Bizproc\Controller\Workflow;

use Bitrix\Bizproc;
use Bitrix\Bizproc\UI\WorkflowFacesView;
use Bitrix\Main\Localization\Loc;

class Faces extends Bizproc\Controller\Base
{
	public function loadAction(string $workflowId, int $userId, int $runningTaskId = 0): ?WorkflowFacesView
	{
		if (empty($workflowId))
		{
			$this->addError(new Bizproc\Error('empty workflow id'));

			return null;
		}

		if ($runningTaskId < 0)
		{
			$this->addError(new Bizproc\Error('negative running task id'));

			return null;
		}

		$currentUserId = (int)($this->getCurrentUser()?->getId() ?? 0); // $this->getCurrentUser()->getId() return string
		if ((new \CBPWorkflowTemplateUser($currentUserId))->isAdmin())
		{
			return $this->getWorkflowFaces($workflowId, $runningTaskId);
		}

		$accessService = new Bizproc\Api\Service\WorkflowAccessService();
		$canViewTimelineResponse = $accessService->canViewTimeline(
			new Bizproc\Api\Request\WorkflowAccessService\CanViewTimelineRequest($workflowId, $userId)
		);

		$canView = $canViewTimelineResponse->isSuccess();
		if ($canView && $currentUserId !== $userId)
		{
			$canView = \CBPHelper::checkUserSubordination($currentUserId, $userId);
		}

		if ($canView)
		{
			return $this->getWorkflowFaces($workflowId, $runningTaskId);
		}

		$this->addError(
			new Bizproc\Error(Loc::getMessage('BIZPROC_CONTROLLER_WORKFLOW_FACES_CAN_READ_ERROR'), 'ACCESS_DENIED')
		);

		return null;
	}

	private function getWorkflowFaces(string $workflowId, int $runningTaskId): WorkflowFacesView
	{
		return new WorkflowFacesView($workflowId, $runningTaskId === 0 ? null : $runningTaskId);
	}
}
