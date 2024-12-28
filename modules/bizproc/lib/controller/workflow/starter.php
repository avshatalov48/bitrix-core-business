<?php

namespace Bitrix\Bizproc\Controller\Workflow;

use Bitrix\Bizproc\Api\Request\WorkflowService\PrepareParametersRequest;
use Bitrix\Bizproc\Api\Request\WorkflowService\PrepareStartParametersRequest;
use Bitrix\Bizproc\Api\Request\WorkflowService\SetConstantsRequest;
use Bitrix\Bizproc\Api\Request\WorkflowService\StartWorkflowRequest;
use Bitrix\Bizproc\Api\Service\WorkflowService;
use Bitrix\Bizproc\Error;
use Bitrix\Main\Localization\Loc;

class Starter extends \Bitrix\Bizproc\Controller\Base
{
	public function getTemplatesAction(): ?array
	{
		if (!$this->checkBizprocFeature())
		{
			return null;
		}

		$complexDocumentType = $this->getComplexDocumentType();
		if (!$complexDocumentType)
		{
			return null;
		}

		$complexDocumentId = null;
		if ($this->getRequest()->get('signedDocumentId'))
		{
			$complexDocumentId = $this->getComplexDocumentId();
			if (!$complexDocumentId)
			{
				return null;
			}

			if (!$this->checkDocumentTypeMatchDocumentId($complexDocumentType, $complexDocumentId))
			{
				return null;
			}
		}

		return [
			'templates' => (
				\CBPDocument::getTemplatesForStart($this->getCurrentUserId(), $complexDocumentType, $complexDocumentId)
			),
		];
	}

	public function startWorkflowAction(int $templateId, ?int $startDuration = null): ?array
	{
		if (!$this->checkBizprocFeature())
		{
			return null;
		}

		$complexDocumentType = $this->getComplexDocumentType();
		if (!$complexDocumentType)
		{
			return null;
		}

		$complexDocumentId = $this->getComplexDocumentId();
		if (!$complexDocumentId)
		{
			return null;
		}

		if (!$this->checkDocumentTypeMatchDocumentId($complexDocumentType, $complexDocumentId))
		{
			return null;
		}

		$service = new WorkflowService();
		$workflowParameters = $service->prepareStartParameters(
			new PrepareStartParametersRequest(
				templateId: $templateId,
				complexDocumentType: $complexDocumentType,
				requestParameters: array_merge(
					$this->getRequest()->toArray(),
					$this->getRequest()->getFileList()->toArray()
				),
				targetUserId: $this->getCurrentUserId(),
			)
		);

		if (!$workflowParameters->isSuccess())
		{
			$this->addErrors($workflowParameters->getErrors());

			return null;
		}

		$startWorkflow = $service->startWorkflow(
			new StartWorkflowRequest(
				userId: $this->getCurrentUserId(),
				targetUserId: $this->getCurrentUserId(),
				templateId: $templateId,
				complexDocumentId: $complexDocumentId,
				parameters: $workflowParameters->getParameters(),
				startDuration: $startDuration >= 0 ? $startDuration : null,
			)
		);

		if (!$startWorkflow->isSuccess())
		{
			$this->addErrors($startWorkflow->getErrors());

			return null;
		}

		return ['workflowId' => $startWorkflow->getWorkflowId()];
	}

	public function checkParametersAction(int $autoExecuteType): ?array
	{
		if (!$this->checkBizprocFeature())
		{
			return null;
		}

		if ($autoExecuteType < 0)
		{
			$this->addError(new Error(
				Loc::getMessage('BIZPROC_LIB_API_CONTROLLER_WORKFLOW_STARTER_ERROR_INCORRECT_AUTO_EXECUTE_TYPE') ?? ''
			));

			return null;
		}

		$parametersDocumentType = $this->getComplexDocumentType();
		if (!$parametersDocumentType)
		{
			return null;
		}

		$canStart = false;
		if ($this->getRequest()->get('signedDocumentId'))
		{
			$canStart = \CBPDocument::canUserOperateDocument(
				\CBPCanUserOperateOperation::StartWorkflow,
				$this->getCurrentUserId(),
				$this->getComplexDocumentId(),
			);
		}

		if (!$canStart
			&& !\CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::StartWorkflow,
				$this->getCurrentUserId(),
				$parametersDocumentType,
			)
		)
		{
			$this->addError(new Error(
				Loc::getMessage('BIZPROC_LIB_API_CONTROLLER_WORKFLOW_STARTER_ERROR_ACCESS_DENIED') ?? ''
			));

			return null;
		}

		$parameters = [];
		$hasErrors = false;
		foreach (\CBPWorkflowTemplateLoader::getDocumentTypeStates($parametersDocumentType, $autoExecuteType) as $template)
		{
			if (is_array($template['TEMPLATE_PARAMETERS']) && $template['TEMPLATE_PARAMETERS'])
			{
				$parameters[$template['TEMPLATE_ID']] =
					$this->prepareWorkflowParameters(
						$template['TEMPLATE_PARAMETERS'],
						$parametersDocumentType,
						"bizproc{$template['TEMPLATE_ID']}_",
					)
				;

				if ($parameters[$template['TEMPLATE_ID']] === null)
				{
					$hasErrors = true;
				}
			}
		}

		if ($hasErrors)
		{
			return null;
		}

		return ['parameters' => \CBPDocument::signParameters($parameters)];
	}

	public function setConstantsAction(int $templateId): ?array
	{
		if (!$this->checkBizprocFeature())
		{
			return null;
		}

		$parametersDocumentType = $this->getComplexDocumentType();
		if (!$parametersDocumentType)
		{
			return null;
		}

		$request = $this->getRequest();

		$response =
			(new WorkflowService())
				->setConstants(
					new SetConstantsRequest(
						templateId: $templateId,
						requestConstants: array_merge($request->toArray(), $request->getFileList()->toArray()),
						complexDocumentType: $parametersDocumentType,
						userId: $this->getCurrentUserId(),
					)
				)
		;

		if ($response->isSuccess())
		{
			return ['success' => true];
		}

		$this->addErrors($response->getErrors());

		return null;
	}

	private function getComplexDocumentType(): ?array
	{
		$request = $this->getRequest();

		$signedDocumentType = $request->get('signedDocumentType');
		if (!is_string($signedDocumentType))
		{
			$this->addError(new Error(
				Loc::getMessage('BIZPROC_LIB_API_CONTROLLER_WORKFLOW_STARTER_ERROR_INCORRECT_DOCUMENT_TYPE') ?? ''
			));

			return null;
		}

		$parametersDocumentType = \CBPDocument::unSignDocumentType($signedDocumentType);

		try
		{
			\CBPHelper::parseDocumentId($parametersDocumentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			$this->addError(Error::createFromThrowable($e));

			return null;
		}

		return $parametersDocumentType;
	}

	private function getComplexDocumentId(): ?array
	{
		$request = $this->getRequest();

		$signedDocumentId = $request->get('signedDocumentId');
		if (!is_string($signedDocumentId))
		{
			$this->addError(new Error(
				Loc::getMessage('BIZPROC_LIB_API_CONTROLLER_WORKFLOW_STARTER_ERROR_INCORRECT_DOCUMENT_ID') ?? ''
			));

			return null;
		}

		$parametersDocumentId = \CBPDocument::unSignDocumentType($signedDocumentId);

		try
		{
			\CBPHelper::parseDocumentId($parametersDocumentId);
		}
		catch (\CBPArgumentNullException $e)
		{
			$this->addError(Error::createFromThrowable($e));

			return null;
		}

		return $parametersDocumentId;
	}

	private function checkDocumentTypeMatchDocumentId(array $parametersDocumentType, array $parametersDocumentId): bool
	{
		if (
			$parametersDocumentType[0] === $parametersDocumentId[0]
			&& $parametersDocumentType[1] === $parametersDocumentId[1]
		)
		{
			return true;
		}

		$this->addError(new Error(
			Loc::getMessage('BIZPROC_LIB_API_CONTROLLER_WORKFLOW_STARTER_ERROR_DOC_TYPE_DONT_MATCH_DOC_ID') ?? ''
		));

		return false;
	}

	private function checkBizprocFeature(): bool
	{
		if (!\CBPRuntime::isFeatureEnabled())
		{
			$this->addError(new Error(
				Loc::getMessage('BIZPROC_LIB_API_CONTROLLER_WORKFLOW_STARTER_ERROR_BIZPROC_FEATURE_DISABLED') ?? '')
			);

			return false;
		}

		return true;
	}

	private function getCurrentUserId(): int
	{
		return (int)($this->getCurrentUser()?->getId());
	}

	private function prepareWorkflowParameters(
		array $templateParameters,
		array $parametersDocumentType,
		string $keyPrefix = '',
	): ?array
	{
		$request = $this->getRequest();
		$allRequestParameters = array_merge($request->toArray(), $request->getFileList()->toArray());

		$requestParameters = [];
		foreach($templateParameters as $key => $property)
		{
			$searchKey = $keyPrefix . $key;
			$requestParameters[$key] = $allRequestParameters[$searchKey] ?? null;
		}

		$parameters = (new WorkflowService())
			->prepareParameters(
				new PrepareParametersRequest(
					templateParameters: $templateParameters,
					requestParameters: $requestParameters,
					complexDocumentType: $parametersDocumentType,
				)
			)
		;

		if ($parameters->isSuccess())
		{
			return $parameters->getParameters();
		}

		$this->addErrors($parameters->getErrors());

		return null;
	}
}
