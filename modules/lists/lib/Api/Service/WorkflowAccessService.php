<?php

namespace Bitrix\Lists\Api\Service;

use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CheckStartWorkflowRequest;
use Bitrix\Bizproc\Api\Response\WorkflowAccessService\CheckAccessResponse;
use Bitrix\Main\Loader;

Loader::requireModule('bizproc');

class WorkflowAccessService extends \Bitrix\Bizproc\Api\Service\WorkflowAccessService
{
	public function checkStartWorkflow(CheckStartWorkflowRequest $request): CheckAccessResponse
	{
		return new CheckAccessResponse();
	}
}