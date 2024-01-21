<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CheckStartWorkflowRequest;
use Bitrix\Bizproc\Api\Request\WorkflowService\StartWorkflowRequest;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Response\WorkflowService\StartWorkflowResponse;
use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;

class WorkflowService
{
	private const PREFIX_LOC_ID = 'BIZPROC_LIB_API_WORKFLOW_SERVICE_';
	private const UNKNOWN_CREATE_WORKFLOW_ERROR = 'UNKNOWN_CREATE_WORKFLOW_ERROR';

	private WorkflowAccessService $accessService;

	public function __construct(?WorkflowAccessService $accessService = null)
	{
		$this->accessService = $accessService ?? new WorkflowAccessService();
	}

	public function startWorkflow(StartWorkflowRequest $request): StartWorkflowResponse
	{
		$response = new StartWorkflowResponse();

		$accessRequest = new CheckStartWorkflowRequest(
			userId: $request->userId,
			complexDocumentId: $request->complexDocumentId,
			parameters: [
				\CBPDocument::PARAM_TAGRET_USER => 'user_' . $request->targetUserId,
				'DocumentCategoryId' => $request->documentCategoryId,
			],
		);
		$accessResponse = $this->accessService->checkStartWorkflow($accessRequest);
		if (!$accessResponse->isSuccess())
		{
			$response->addErrors($accessResponse->getErrors());

			return $response;
		}
		if (isset($request->startDuration) && $request->startDuration < 0)
		{
			throw new ArgumentException('Start duration must be non negative');
		}

		$startWorkflowErrors = [];
		$instanceId = \CBPDocument::startWorkflow(
			$request->templateId,
			$request->complexDocumentId,
			$request->parameters,
			$startWorkflowErrors,
			$request->parentWorkflow,
		);

		if ($startWorkflowErrors)
		{
			foreach ($startWorkflowErrors as $error)
			{
				if (is_numeric($error['code']))
				{
					$response->addError(new Error($error['message'], (int)$error['code']));
				}
				else
				{
					$response->addError(new Error($error['message']));
				}
			}
		}
		elseif (is_null($instanceId))
		{
			$response->addError(
				new Error(Loc::getMessage(static::PREFIX_LOC_ID . static::UNKNOWN_CREATE_WORKFLOW_ERROR))
			);
		}
		else
		{
			if (isset($request->startDuration))
			{
				$metadata = new EO_WorkflowMetadata();

				$metadata->setWorkflowId($instanceId);
				$metadata->setStartDuration($request->startDuration);
				$metadata->save();
			}

			$response->setWorkflowId($instanceId);
		}

		return $response;
	}
}