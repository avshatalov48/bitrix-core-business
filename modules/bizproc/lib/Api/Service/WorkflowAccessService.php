<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CanViewTimelineRequest;
use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CheckStartWorkflowRequest;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Response\WorkflowAccessService\CanViewTimelineResponse;
use Bitrix\Bizproc\Api\Response\WorkflowAccessService\CheckAccessResponse;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Main\Localization\Loc;

class WorkflowAccessService
{
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
			$response->addError(new Error(Loc::getMessage(
				'BIZPROC_LIB_API_WORKFLOW_ACCESS_SERVICE_START_WORKFLOW_RIGHTS_ERROR'
			)));
		}

		return $response;
	}

	public function canViewTimeline(CanViewTimelineRequest $request): CanViewTimelineResponse
	{
		$workflowUser =
			WorkflowUserTable::query()
				->setSelect(['*'])
				->setFilter([
					'=WORKFLOW_ID' => $request->workflowId,
					'=USER_ID' => $request->userId,
				])
				->setLimit(1)
				->exec()
				->fetchObject()
		;

		if (!$workflowUser && !$this->canViewWorkflow($request->workflowId, $request->userId))
		{
			return CanViewTimelineResponse::createError(static::getViewAccessDeniedError());
		}

		return new CanViewTimelineResponse();
	}

	private function canViewWorkflow($workflowId, $userId): bool
	{
		$documentId = \CBPStateService::getStateDocumentId($workflowId);

		return (
			$documentId
			&& \CBPDocument::canUserOperateDocument(
				\CBPCanUserOperateOperation::ViewWorkflow,
				$userId,
				$documentId,
				[
					'WorkflowId' => $workflowId,
				]
			)
		);
	}

	public static function getViewAccessDeniedError(): \Bitrix\Bizproc\Error
	{
		return new \Bitrix\Bizproc\Error(Loc::getMessage(
			'BIZPROC_LIB_API_WORKFLOW_ACCESS_SERVICE_VIEW_TIMELINE_RIGHTS_ERROR_MSGVER_1'
		));
	}
}
