<?php

namespace Bitrix\Lists\Workflow;

use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Lists\Api\Request\WorkflowService\StartWorkflowsRequest;
use Bitrix\Lists\Api\Service\WorkflowService;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

final class Starter
{
	private WorkflowService $workflowService;
	private int $currentUserId;
	private bool $isEnabled;
	private int $elementId = 0;
	private array $parameters = [];
	private array $changedFields = [];
	private ?int $timeToStart = null;

	private ?bool $hasTemplatesOnStartup = null;

	public function __construct(array $iBlockInfo, int $currentUserId)
	{
		$this->isEnabled = (
			Loader::includeModule('bizproc')
			&& \CLists::isBpFeatureEnabled($iBlockInfo['IBLOCK_TYPE_ID'])
			&& (isset($iBlockInfo['BIZPROC']) && $iBlockInfo['BIZPROC'] === 'Y') // $iBlockInfo['BIZPROC'] != 'N'
		);

		$this->workflowService = new WorkflowService($iBlockInfo);
		$this->currentUserId = max($currentUserId, 0);
	}

	public function setElementId(int $elementId): void
	{
		if ($elementId > 0)
		{
			$this->elementId = $elementId;
			$this->hasTemplatesOnStartup = null;
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

	public function hasTemplatesOnStartup(): bool
	{
		if ($this->hasTemplatesOnStartup === null)
		{
			$this->hasTemplatesOnStartup = $this->workflowService->hasTemplatesOnStartup(
				$this->workflowService->getComplexDocumentId($this->elementId)
			);
		}

		return $this->hasTemplatesOnStartup;
	}

	public function setChangedFields(array $changedFields): void
	{
		$this->changedFields = $changedFields;
	}

	public function isEnabled(): bool
	{
		return $this->isEnabled;
	}

	public function isRunnable(int $createdBy): Result
	{
		$result = new Result();

		if (!$this->isEnabled())
		{
			return $result->addError(
				new Error(Loc::getMessage('LISTS_LIB_WORKFLOW_STARTER_BP_NOT_AVAILABLE') ?? '')
			);
		}

		$currentUserGroups = $this->getCurrentUserGroups($this->currentUserId > 0 && $this->currentUserId === $createdBy);
		if (!$this->workflowService->canUserWriteDocument($this->elementId, $this->currentUserId, $currentUserGroups))
		{
			return $result->addError(
				new Error(Loc::getMessage('LISTS_LIB_WORKFLOW_STARTER_USER_CANT_WRITE_DOCUMENT') ?? '')
			);
		}

		if (!$this->workflowService->isConstantsTuned())
		{
			return $result->addError(
				new Error(Loc::getMessage('LISTS_LIB_WORKFLOW_STARTER_CONSTANTS_NOT_CONFIGURED') ?? '')
			);
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
				!$isNewElement && $this->hasTemplatesOnStartup() ? $this->changedFields : [],
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
