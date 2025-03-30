<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetTimelineRequest;
use Bitrix\Bizproc\Api\Service\UserService;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Bizproc;

class Workflow extends Base
{
	private const PAGE_SIZE = 20;

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
		$timeline = $response->getTimeline();

		if (!$timeline || !$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		$workflowState = $timeline->getWorkflowState();

		$userIds = [$workflowState->getStartedBy()];
		foreach ($timeline->getTasks() as $task)
		{
			$userIds = array_merge($userIds, $task->getTaskUserIds());
		}

		$userService = new UserService();

		$request = new UsersToGet($userIds);
		$response = $userService->getUsersView($request);

		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		$data = $timeline->jsonSerialize();
		$data['users'] = $response->getUserViews();
		$duration = $workflowStateService->getAverageWorkflowDuration(
			new GetAverageWorkflowDurationRequest($workflowState->getWorkflowTemplateId())
		)->getAverageDuration();

		$data['stats'] = [
			'averageDuration' => $duration,
			'efficiency' => $this->getWorkflowEfficiency(
				$timeline->getExecutionTime() ?? 0,
				$duration
			),
		];

		$data['biMenu'] = $this->getBiMenu($workflowState->getWorkflowTemplateId());

		return $data;
	}

	private function getBiMenu(int $workflowTemplateId): ?array
	{
		if (!Loader::includeModule('biconnector'))
		{
			return null;
		}

		if (!defined('\Bitrix\BIConnector\Superset\Scope\ScopeService::BIC_SCOPE_WORKFLOW_TEMPLATE'))
		{
			return null;
		}

		$menu = \Bitrix\BIConnector\Superset\Scope\ScopeService::getInstance()->prepareScopeMenuItem(
			\Bitrix\BIConnector\Superset\Scope\ScopeService::BIC_SCOPE_WORKFLOW_TEMPLATE,
			[
				'workflow_template_id' => $workflowTemplateId,
			]
		);

		return $menu ?: null;
	}

	public function terminateAction(string $workflowId): bool
	{
		$currentUserId = $this->getCurrentUser()?->getId();

		$workflowService = new \Bitrix\Bizproc\Api\Service\WorkflowService(
			accessService: new \Bitrix\Bizproc\Api\Service\WorkflowAccessService(),
		);

		$request = new Bizproc\Api\Request\WorkflowService\TerminateWorkflowRequest(
			workflowId: $workflowId,
			userId: $currentUserId,
		);

		$result = $workflowService->terminateWorkflow($request);
		if ($result->isSuccess())
		{
			return true;
		}

		$this->addErrors($result->getErrors());

		return false;
	}

	public function terminateByTemplateAction(int $templateId, string $signedDocument): bool
	{
		$currentUserId = $this->getCurrentUser()?->getId();

		$workflowService = new \Bitrix\Bizproc\Api\Service\WorkflowService(
			new \Bitrix\Bizproc\Api\Service\WorkflowAccessService(),
		);

		[$documentType, $documentCategoryId, $documentId] = \CBPDocument::unSignParameters($signedDocument);
		$complexDocumentId = [$documentType[0], $documentType[1], $documentId];

		$request = new Bizproc\Api\Request\WorkflowService\TerminateByTemplateRequest(
			$templateId,
			$complexDocumentId,
			$currentUserId,
		);

		$result = $workflowService->terminateWorkflowsByTemplate($request);
		if ($result->isSuccess())
		{
			return true;
		}

		$this->addErrors($result->getErrors());

		return false;
	}

	public function getTemplateInstancesAction(int $templateId, int $offset = 0): ?array
	{
		$template = Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::getById($templateId)->fetchObject();
		$hasPermission = false;

		if ($template)
		{
			$hasPermission = \CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::StartWorkflow,
				$this->getCurrentUser()->getId(),
				$template->getDocumentComplexType(),
			);
		}

		if (!$hasPermission)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_WORKFLOW_TEMPLATE_NO_PRERMISSIONS')));

			return null;
		}

		$query = Bizproc\Workflow\Entity\WorkflowInstanceTable::query();
		$query->addFilter('WORKFLOW_TEMPLATE_ID', $templateId)
			->addSelect('ID')
			->setOrder(['STARTED' => 'ASC'])
			->setLimit(self::PAGE_SIZE + 1)
			->setOffset($offset)
		;
		$result = $query->exec();
		$ids = array_column($result->fetchAll(), 'ID');

		if (!$ids)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_WORKFLOW_TEMPLATE_NO_LIST')));

			return null;
		}

		$hasNextPage = count($ids) > self::PAGE_SIZE;

		if ($hasNextPage)
		{
			$ids = array_slice($ids, 0, self::PAGE_SIZE);
		}

		return [
			'list' => array_map(
				static fn($id) => new Bizproc\UI\WorkflowFacesView($id),
				$ids,
			),
			'hasNextPage' => $hasNextPage,
		];
	}
}
