<?php

namespace Bitrix\Lists\Workflow;

use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Lists\Api\Request\WorkflowService\StartWorkflowsRequest;
use Bitrix\Lists\Api\Service\WorkflowService;
use Bitrix\Main\Result;

final class Starter
{
	private WorkflowService $workflowService;
	private int $currentUserId;
	private int $elementId = 0;
	private array $parameters = [];
	private array $changedFields = [];
	private ?int $timeToStart = null;

	private bool $hasTemplatesOnStartup = false;

	public function __construct(array $iBlockInfo, int $currentUserId)
	{
		$this->workflowService = new WorkflowService($iBlockInfo);
		$this->currentUserId = max($currentUserId, 0);
	}

	public function setElementId(int $elementId): void
	{
		if ($elementId > 0)
		{
			$this->elementId = $elementId;
		}
	}

	public function setTimeToStart(?int $timeToStart = null): self
	{
		$this->timeToStart = $timeToStart;

		return $this;
	}

	public function setParameters(array $parameters): Result
	{
		$result = new Result();

		$parameterValuesResponse = $this->workflowService->getParameterValuesFromRequest($parameters, $this->elementId);
		if ($parameterValuesResponse->isSuccess())
		{
			$this->parameters = $parameterValuesResponse->getParameters();
			$this->hasTemplatesOnStartup = $this->elementId > 0 && $parameterValuesResponse->hasTemplatesOnStartup();
		}

		return (
			$result
				->addErrors($parameterValuesResponse->getErrors())
				->setData(['parameters' => $parameterValuesResponse->getParameters()])
		);
	}

	public function setChangedFields(array $changedFields): void
	{
		$this->changedFields = $changedFields;
	}

	public function isEnabled(): bool
	{
		return $this->workflowService->isBpEnabled();
	}

	public function isRunnable(int $createdBy): Result
	{
		$result = new Result();

		if (!$this->isEnabled())
		{
			return $result->addError(new Error('not available'));
		}

		$currentUserGroups = $this->getCurrentUserGroups($this->currentUserId > 0 && $this->currentUserId === $createdBy);
		if (!$this->workflowService->canUserWriteDocument($this->elementId, $this->currentUserId, $currentUserGroups))
		{
			return $result->addError(new Error('cant write document'));
		}

		if (!$this->workflowService->isConstantsTuned())
		{
			return $result->addError(new Error('constants are not configured'));
		}

		return $result;
	}

	private function getCurrentUserGroups(bool $isAuthor = false): array
	{
		$userGroups = $this->currentUserId ? \CUser::GetUserGroup($this->currentUserId) : [];
		if ($isAuthor && is_array($userGroups))
		{
			$userGroups[] = 'author';
		}

		return is_array($userGroups) ? $userGroups : [];
	}

	public function run(bool $isNewElement = false): Result
	{
		$result = new Result();

		$startWorkflowResponse = $this->workflowService->startWorkflows(
			new StartWorkflowsRequest(
				$this->elementId,
				$this->currentUserId,
				$this->parameters,
				$this->hasTemplatesOnStartup && !$isNewElement ? $this->changedFields : [],
				$isNewElement,
				$this->timeToStart,
			)
		);

		return (
			$result
				->addErrors($startWorkflowResponse->getErrors())
				->setData(['workflowIds' => $startWorkflowResponse->getWorkflowIds()])
		);
	}
}
