<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CheckStartWorkflowRequest;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Response\WorkflowAccessService\CheckAccessResponse;
use Bitrix\Main\Localization\Loc;

class WorkflowAccessService
{
	private const PREFIX_LOC_ID = 'BIZPROC_LIB_API_WORKFLOW_ACCESS_SERVICE_';
	private const RIGHTS_ERROR = 'START_WORKFLOW_RIGHTS_ERROR';

	public function checkStartWorkflow(CheckStartWorkflowRequest $request): CheckAccessResponse
	{
		$hasAccess =
			\CBPDocument::canUserOperateDocument(
				\CBPCanUserOperateOperation::StartWorkflow,
				$request->userId,
				$request->complexDocumentId,
				$request->parameters,
			)
		;

		$response = new CheckAccessResponse();
		if (!$hasAccess)
		{
			$response->addError(new Error(Loc::getMessage(static::PREFIX_LOC_ID . static::RIGHTS_ERROR)));
		}

		return $response;
	}
}
