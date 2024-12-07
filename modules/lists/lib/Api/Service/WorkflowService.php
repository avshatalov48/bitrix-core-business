<?php

namespace Bitrix\Lists\Api\Service;

use Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata;
use Bitrix\Lists\Api\Request\WorkflowService\StartWorkflowsRequest;
use Bitrix\Lists\Api\Response\Response;
use Bitrix\Lists\Api\Response\WorkflowService\GetParameterValuesResponse;
use Bitrix\Lists\Api\Response\WorkflowService\StartWorkflowsResponse;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

final class WorkflowService
{
	private string $iBlockTypeId;
	private bool $isBpEnabled;
	private ?array $complexDocumentType;

	public function __construct(array $iBlockInfo)
	{
		$this->iBlockTypeId = (string)($iBlockInfo['IBLOCK_TYPE_ID'] ?? '');

		$this->isBpEnabled = (
			Loader::includeModule('bizproc')
			&& \CLists::isBpFeatureEnabled($this->iBlockTypeId)
			&& (isset($iBlockInfo['BIZPROC']) && $iBlockInfo['BIZPROC'] === 'Y') // $iBlockInfo['BIZPROC'] != 'N'
		);

		$this->complexDocumentType = (
			$this->isBpEnabled
				? \BizprocDocument::generateDocumentComplexType(
					$this->iBlockTypeId,
					max((int)($iBlockInfo['ID'] ?? 0), 0)
				)
				: null
		);
	}

	public function getSignedDocument(int $elementId): ?string
	{
		if ($this->isBpEnabled)
		{
			return \CBPDocument::signParameters([$this->complexDocumentType, (string)$elementId]);
		}

		return null;
	}

	public function canUserWriteDocument(int $elementId, int $userId, array $userGroups): bool
	{
		if ($elementId < 0 || $userId <= 0)
		{
			return false;
		}

		$canWrite = true;
		if ($this->isBpEnabled)
		{
			$complexDocumentId = $this->getComplexDocumentId($elementId);

			$operation = \CBPCanUserOperateOperation::WriteDocument;
			$parameters = ['AllUserGroups' => $userGroups];

			$canWrite = (
				$elementId > 0
					? \CBPDocument::canUserOperateDocument($operation, $userId, $complexDocumentId, $parameters)
					: \CBPDocument::canUserOperateDocumentType($operation, $userId, $this->complexDocumentType, $parameters)
			);
		}

		return $canWrite;
	}

	public function canUserStartWorkflow(int $currentUserId, int $elementId, int $sectionId = 0): bool
	{
		if ($currentUserId <= 0 || $elementId < 0 || $sectionId < 0)
		{
			return false;
		}

		$canStart = true;
		if ($this->isBpEnabled)
		{
			$documentStates = $this->getDocumentStates($this->getComplexDocumentId($elementId));

			$canStart = \CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::StartWorkflow,
				$currentUserId,
				$this->complexDocumentType,
				['sectionId' => $sectionId, 'DocumentStates' => $documentStates]
			);
		}

		return $canStart;
	}

	public function isConstantsTuned(): bool
	{
		$isConstantsTuned = true;
		if ($this->isBpEnabled)
		{
			$templates = array_merge(
				\CBPWorkflowTemplateLoader::searchTemplatesByDocumentType(
					$this->complexDocumentType, \CBPDocumentEventType::Create
				),
				\CBPWorkflowTemplateLoader::searchTemplatesByDocumentType(
					$this->complexDocumentType, \CBPDocumentEventType::Edit
				),
			);

			foreach ($templates as $template)
			{
				if (!\CBPWorkflowTemplateLoader::isConstantsTuned($template['ID']))
				{
					$isConstantsTuned = false;

					break;
				}
			}
		}

		return $isConstantsTuned;
	}

	public function hasParameters(int $elementId): bool
	{
		$hasParameters = false;
		if ($this->isBpEnabled)
		{
			$states = $this->getNotRunningDocumentStates($elementId);
			foreach ($states as $state)
			{
				$parameters = $state['TEMPLATE_PARAMETERS'] ?? [];
				if (!empty($parameters) && is_array($parameters))
				{
					$hasParameters = true;

					break;
				}
			}
		}

		return $hasParameters;
	}

	public function getParameterValuesFromRequest(array $request, int $elementId): GetParameterValuesResponse
	{
		$response = new GetParameterValuesResponse();

		$parameters = [];
		if ($this->isBpEnabled && $elementId >= 0)
		{
			$documentStates = $this->getDocumentStates($this->getComplexDocumentId($elementId));
			foreach ($documentStates as $state)
			{
				if (empty($state['ID']))
				{
					$errors = [];
					$parameters[$state['TEMPLATE_ID']] = \CBPWorkflowTemplateLoader::checkWorkflowParameters(
						$state['TEMPLATE_PARAMETERS'] ?? [],
						$request[$state['TEMPLATE_ID']] ?? [],
						$this->complexDocumentType,
						$errors
					);

					foreach ($errors as $error)
					{
						$response->addError(new Error(!empty($error['message']) ? $error['message'] : ''));
					}
				}
			}
		}

		return $response->setParameters($parameters);
	}

	public function startWorkflows(StartWorkflowsRequest $request): StartWorkflowsResponse
	{
		$response = new StartWorkflowsResponse();

		if ($request->elementId <= 0 || $request->currentUserId <= 0)
		{
			$response->addError(
				new Error(
					Loc::getMessage('LISTS_LIB_API_WORKFLOW_SERVICE_INCORRECT_START_WORKFLOW_INPUT_DATA') ?? ''
				)
			);
		}

		$workflowIds = [];
		if ($this->isBpEnabled && $response->isSuccess())
		{
			$complexDocumentId = $this->getComplexDocumentId($request->elementId);
			$documentStates = $this->getDocumentStates($request->isNewElement ? null : $complexDocumentId);
			foreach ($documentStates as $state)
			{
				if (empty($state['ID']))
				{
					$errors = [];

					$startWorkflowParameters = [
						\CBPDocument::PARAM_TAGRET_USER => 'user_' . $request->currentUserId,
						\CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => $request->changedFields,
					];

					$workflowIds[$state['TEMPLATE_ID']] = \CBPDocument::startWorkflow(
						$state['TEMPLATE_ID'],
						$complexDocumentId,
						array_merge($request->parameters[$state['TEMPLATE_ID']] ?? [], $startWorkflowParameters),
						$errors
					);

					if (!$errors && isset($request->timeToStart))
					{
						$metadata = new EO_WorkflowMetadata();
						$metadata->setWorkflowId($workflowIds[$state['TEMPLATE_ID']]);
						$metadata->setStartDuration($request->timeToStart);
						$metadata->save();
					}

					foreach ($errors as $error)
					{
						$response->addError(new Error(!empty($error['message']) ? $error['message'] : ''));
					}
				}
			}
		}

		return $response->setWorkflowIds($workflowIds);
	}

	public function getComplexDocumentId(int $elementId): ?array
	{
		if ($this->isBpEnabled)
		{
			return ($elementId > 0 ? \BizprocDocument::getDocumentComplexId($this->iBlockTypeId, $elementId) : null);
		}

		return null;
	}

	public function getComplexDocumentType(): ?array
	{
		return $this->complexDocumentType;
	}

	public function getDocumentStates(?array $complexDocumentId): array
	{
		if ($this->isBpEnabled)
		{
			return \CBPDocument::getDocumentStates($this->complexDocumentType, $complexDocumentId);
		}

		return [];
	}

	public function getNotRunningDocumentStates(int $elementId)
	{
		if (!$this->isBpEnabled)
		{
			return [];
		}

		$autoExecuteType = $elementId > 0 ? \CBPDocumentEventType::Edit: \CBPDocumentEventType::Create;

		return \CBPWorkflowTemplateLoader::getDocumentTypeStates($this->complexDocumentType, $autoExecuteType);
	}

	public function getDocumentTypeStates(): array
	{
		if (!$this->isBpEnabled)
		{
			return [];
		}

		$states = array_merge(
			\CBPWorkflowTemplateLoader::getDocumentTypeStates($this->complexDocumentType, \CBPDocumentEventType::Create),
			\CBPWorkflowTemplateLoader::getDocumentTypeStates($this->complexDocumentType, \CBPDocumentEventType::Edit),
		);

		$result = [];
		$templateIds = [];
		foreach ($states as $state)
		{
			$templateId = (int)$state['TEMPLATE_ID'];
			if (isset($templateIds[$templateId]))
			{
				continue;
			}
			$templateIds[$templateId] = true;

			$state['TEMPLATE_CONSTANTS'] = \CBPWorkflowTemplateLoader::getTemplateConstants($templateId);
			$result[] = $state;
		}

		return $result;
	}

	public function hasTemplatesOnStartup(?array $complexDocumentId = null): bool
	{
		if (!$this->isBpEnabled)
		{
			return false;
		}

		$templates = (
			$complexDocumentId
				? \CBPWorkflowTemplateLoader::searchTemplatesByDocumentType(
					$this->complexDocumentType, \CBPDocumentEventType::Edit
				)
				: \CBPWorkflowTemplateLoader::searchTemplatesByDocumentType(
					$this->complexDocumentType, \CBPDocumentEventType::Create
				)
		);

		return !empty($templates);
	}

	public function getDocumentStatesWithParameters(int $elementId): array
	{
		if ($elementId < 0 || !$this->isBpEnabled)
		{
			return [];
		}

		$states = [];
		foreach ($this->getDocumentStates($this->getComplexDocumentId($elementId)) as $documentState)
		{
			$parameters = $documentState['TEMPLATE_PARAMETERS'] ?? [];
			if (!empty($parameters))
			{
				$states[] = $documentState;
			}
		}

		return $states;
	}

	public function getNotTunedDocumentTypeStates(): array
	{
		$notTuned = [];
		if ($this->isBpEnabled)
		{
			foreach ($this->getDocumentTypeStates() as $state)
			{
				$templateId = (int)$state['TEMPLATE_ID'];
				if (\CBPWorkflowTemplateLoader::isConstantsTuned($templateId))
				{
					continue;
				}

				$notTuned[] = $state;
			}
		}

		return $notTuned;
	}

	public function setConstants(array $request): Response
	{
		$response = new Response();

		$properties = [];
		if ($this->isBpEnabled)
		{
			foreach ($this->getDocumentTypeStates() as $state)
			{
				$templateId = (int)$state['TEMPLATE_ID'];
				if (array_key_exists($templateId, $request) && is_array($state['TEMPLATE_CONSTANTS']))
				{
					$errors = [];
					$values = \CBPWorkflowTemplateLoader::checkWorkflowParameters(
						$state['TEMPLATE_CONSTANTS'],
						$request[$templateId] ?? [],
						$this->complexDocumentType,
						$errors
					);

					foreach ($errors as $error)
					{
						if (!empty($error['message']))
						{
							$response->addError(new Error($error['message']));
						}
					}

					if (!$errors)
					{
						foreach ($state['TEMPLATE_CONSTANTS'] as $id => $property)
						{
							$property['Default'] = $values[$id] ?? null;
							$properties[$templateId][$id] = $property;
						}
					}
				}
			}

			if ($response->isSuccess())
			{
				foreach ($properties as $templateId => $constants)
				{
					\CBPWorkflowTemplateLoader::update($templateId, ['CONSTANTS' => $constants]);
				}
			}
		}

		return $response;
	}
}
