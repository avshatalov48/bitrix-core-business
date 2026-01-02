<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Bizproc\Api\Enum\ErrorMessage;
use Bitrix\Bizproc\Api\Data\WorkflowTemplateService\WorkflowTemplate;
use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateDraftTable;
use Bitrix\Bizproc\Api\Request\WorkflowTemplateService as WorkflowTemplateRequest;
use Bitrix\Bizproc\Api\Response\WorkflowTemplateService as WorkflowTemplateResponse;

class WorkflowTemplateService
{
	private const TRACK_ON_INTERVAL = 7 * 86400; // 7 days in seconds

	private WorkflowAccessService $accessService;

	public function __construct(?WorkflowAccessService $accessService = null)
	{
		$this->accessService = $accessService ?? new WorkflowAccessService();
	}

	public function prepareParameters(
		WorkflowTemplateRequest\PrepareParametersRequest $request
	): WorkflowTemplateResponse\PrepareParametersResponse
	{
		try
		{
			\CBPHelper::parseDocumentId($request->complexDocumentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			return WorkflowTemplateResponse\PrepareParametersResponse::createError(
				\Bitrix\Bizproc\Error::createFromThrowable($e)
			);
		}

		$parameters = [];
		foreach ($request->templateParameters as $key => $property)
		{
			$value = $request->requestParameters[$key] ?? null;

			if ($property['Type'] === FieldType::FILE)
			{
				if (!empty($value) && isset($value['name']))
				{
					$parameters[$key] = $value;
					if (is_array($value['name']))
					{
						$parameters[$key] = [];
						\CFile::ConvertFilesToPost($value, $parameters[$key]);
					}
				}

				continue;
			}

			$parameters[$key] = $value;
		}

		$errors = [];
		$response =
			(new WorkflowTemplateResponse\PrepareParametersResponse())
				->setRawParameters($parameters)
				->setParameters(
					\CBPWorkflowTemplateLoader::checkWorkflowParameters(
						$request->templateParameters, $parameters, $request->complexDocumentType, $errors
					)
				)
		;

		if ($errors)
		{
			foreach ($errors as $error)
			{
				$response->addError(new \Bitrix\Bizproc\Error($error['message'], $error['code']));
			}
		}

		return $response;
	}

	public function setConstants(
		WorkflowTemplateRequest\SetConstantsRequest $request
	): WorkflowTemplateResponse\SetConstantsResponse
	{
		if ($request->templateId <= 0)
		{
			return WorkflowTemplateResponse\SetConstantsResponse::createError(new Error('negative template id'));
		}

		if ($request->userId <= 0)
		{
			return WorkflowTemplateResponse\SetConstantsResponse::createError(new Error('negative user id'));
		}

		try
		{
			\CBPHelper::parseDocumentId($request->complexDocumentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			return WorkflowTemplateResponse\SetConstantsResponse::createError(Error::createFromThrowable($e));
		}

		if (
			!\CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::CreateWorkflow,
				$request->userId,
				$request->complexDocumentType
			)
		)
		{
			return WorkflowTemplateResponse\SetConstantsResponse::createError(new Error('access denied'));
		}

		$constants = \CBPWorkflowTemplateLoader::getTemplateConstants($request->templateId);
		if (!is_array($constants) || !$constants)
		{
			return WorkflowTemplateResponse\SetConstantsResponse::createOk();
		}

		$preparedResult = $this->prepareParameters(
			new WorkflowTemplateRequest\PrepareParametersRequest(
				templateParameters: $constants,
				requestParameters: $request->requestConstants,
				complexDocumentType: $request->complexDocumentType,
			)
		);

		if (!$preparedResult->isSuccess())
		{
			return (new WorkflowTemplateResponse\SetConstantsResponse())->addErrors($preparedResult->getErrors());
		}

		$preparedConstants = $preparedResult->getParameters();
		foreach ($constants as $key => $constant)
		{
			$constants[$key]['Default'] = $preparedConstants[$key] ?? null;
		}

		try
		{
			\CBPWorkflowTemplateLoader::update($request->templateId, ['CONSTANTS' => $constants]);
		}
		catch (\Exception $e)
		{
			return WorkflowTemplateResponse\SetConstantsResponse::createError(
				new Error('something go wrong, try again')
			);
		}

		return WorkflowTemplateResponse\SetConstantsResponse::createOk();
	}

	public function prepareStartParameters(
		WorkflowTemplateRequest\PrepareStartParametersRequest $request
	): WorkflowTemplateResponse\PrepareStartParametersResponse
	{
		if ($request->templateId <= 0)
		{
			return WorkflowTemplateResponse\PrepareStartParametersResponse::createError(
				new Error('negative template id')
			);
		}

		if ($request->targetUserId <= 0)
		{
			return WorkflowTemplateResponse\PrepareStartParametersResponse::createError(
				new Error('negative target user id')
			);
		}

		try
		{
			\CBPHelper::parseDocumentId($request->complexDocumentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			return WorkflowTemplateResponse\PrepareStartParametersResponse::createError(Error::createFromThrowable($e));
		}

		$template =
			\CBPWorkflowTemplateLoader::getList(
				[],
				[
					'ID' => $request->templateId,
					'DOCUMENT_TYPE' => $request->complexDocumentType,
					'ACTIVE' => 'Y',
					'<AUTO_EXECUTE' => \CBPDocumentEventType::Automation,
				],
				false,
				false,
				['ID', 'PARAMETERS']
			)->fetch()
		;

		if (!$template)
		{
			return WorkflowTemplateResponse\PrepareStartParametersResponse::createError(
				new Error('template not found')
			);
		}

		$workflowParameters = [];
		if (is_array($template['PARAMETERS']) && $template['PARAMETERS'])
		{
			$preparedParameters = $this->prepareParameters(
				new WorkflowTemplateRequest\PrepareParametersRequest(
					$template['PARAMETERS'],
					$request->requestParameters,
					$request->complexDocumentType
				)
			);

			if (!$preparedParameters->isSuccess())
			{
				return (new WorkflowTemplateResponse\PrepareStartParametersResponse())->addErrors(
					$preparedParameters->getErrors()
				);
			}

			$workflowParameters = $preparedParameters->getParameters();
		}

		$workflowParameters[\CBPDocument::PARAM_TAGRET_USER] = 'user_' . $request->targetUserId;
		$workflowParameters[\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE] = $request->eventType;

		return (new WorkflowTemplateResponse\PrepareStartParametersResponse())->setParameters($workflowParameters);
	}

	public function saveTemplate(
		WorkflowTemplateRequest\SaveTemplateRequest $request
	): WorkflowTemplateResponse\SaveTemplateResponse
	{
		$response = new WorkflowTemplateResponse\SaveTemplateResponse();

		$documentType = $this->getDocumentType($request->parameters);
		if (is_null($documentType))
		{
			$errorMsg = ErrorMessage::INVALID_PARAM_ARG->getError([
				'#PARAM#' => 'DOCUMENT_TYPE',
				'#VALUE#' => $documentType
			]);
			$response->addError($errorMsg);

			return $response;
		}

		if ($request->checkAccess && !$this->accessService->canCreateWorkflow($documentType, $request->user->getId()))
		{
			$response->addError(
				ErrorMessage::ACCESS_DENIED->getError()
			);

			return $response;
		}

		try {
			$template = WorkflowTemplate::createFromRequest($request);
			$templateId = $template->getTemplateId();
			$fields = $template->getFields();
			if ($templateId > 0)
			{
				\CBPWorkflowTemplateLoader::update($templateId, $fields);
				$response->setTemplateId($templateId);
			}
			else
			{
				$response->setTemplateId(\CBPWorkflowTemplateLoader::add($fields));
			}

			$this->handleTrackOnOption($response->getTemplateId(), $template->getFields());
		}
		catch (\Throwable $exception)
		{
			if (method_exists($exception, 'getErrors'))
			{
				$errors = $exception->getErrors();
				$response->setActivityErrors($errors);
				foreach ($errors as $error)
				{
					$response->addError(new Error($error['message'], $error['code'] ?? 0));
				}
			}
			else
			{
				$response->addError(new Error($exception->getMessage(), $exception->getCode()));
			}
		}

		return $response;
	}

	private function handleTrackOnOption(int $templateId, array $fields): void
	{
		if (isset($fields['TRACK_ON']))
		{
			$optionName = 'tpl_track_on_' . $templateId;
			if ($fields['TRACK_ON'] === 'Y')
			{
				$trackOn = (int)Option::get('bizproc', $optionName, 0);
				if ((time() - self::TRACK_ON_INTERVAL) > $trackOn)
				{
					Option::set('bizproc', $optionName, time());
				}
			}
			else
			{
				Option::delete('bizproc', ['name' => $optionName]);
			}
		}
	}

	public function importTemplate(
		WorkflowTemplateRequest\ImportTemplateRequest $request
	): WorkflowTemplateResponse\ImportTemplateResponse
	{
		$response = new WorkflowTemplateResponse\ImportTemplateResponse();

		$documentType = $this->getDocumentType($request->parameters);
		if (is_null($documentType))
		{
			$errorMsg = ErrorMessage::INVALID_PARAM_ARG->getError([
				'#PARAM#' => 'DOCUMENT_TYPE',
				'#VALUE#' => $documentType
			]);
			$response->addError($errorMsg);

			return $response;
		}

		if ($request->checkAccess && !$this->accessService->canCreateWorkflow($documentType, $request->user->getId()))
		{
			$response->addError(
				ErrorMessage::IMPORT_ACCESS_DENIED->getError()
			);

			return $response;
		}

		$newTemplateId = 0;
		$file = $request->file;
		if (is_uploaded_file($file['tmp_name']))
		{
			$fileHandle = fopen($file['tmp_name'], 'rb');
			$data = fread($fileHandle, filesize($file['tmp_name']));
			fclose($fileHandle);

			try
			{
				$newTemplateId = \CBPWorkflowTemplateLoader::ImportTemplate(
					$request->id,
					$documentType,
					$request->autostart,
					$request->name,
					$request->description,
					$data
				);
			}
			catch (\Throwable $exception)
			{
				$response->addError(new Error(preg_replace("#[\r\n]+#", " ", $exception->getMessage())));
			}
		}

		if ($newTemplateId <= 0)
		{
			$response->addError(new Error(Loc::getMessage('BIZPROC_LIB_API_WORKFLOW_TEMPLATE_SERVICE_IMPORT_ERROR')));
		}

		$response->setTemplateId($newTemplateId);

		return $response;
	}

	private function getDocumentType(array $parameters): ?array
	{
		if (!isset($parameters['MODULE_ID'], $parameters['ENTITY'], $parameters['DOCUMENT_TYPE']))
		{
			return null;
		}

		return [
			$parameters['MODULE_ID'],
			$parameters['ENTITY'],
			$parameters['DOCUMENT_TYPE']
		];
	}

	public function exportTemplate(
		WorkflowTemplateRequest\ExportTemplateRequest $request)
	: WorkflowTemplateResponse\ExportTemplateResponse
	{
		$response = new WorkflowTemplateResponse\ExportTemplateResponse();

		$documentType = $this->getDocumentType($request->parameters);
		if (is_null($documentType))
		{
			$errorMsg = ErrorMessage::INVALID_PARAM_ARG->getError([
				'#PARAM#' => 'DOCUMENT_TYPE',
				'#VALUE#' => $documentType
			]);
			$response->addError($errorMsg);

			return $response;
		}

		if ($request->checkAccess && !$this->accessService->canCreateWorkflow($documentType, $request->user->getId()))
		{
			$response->addError(
				ErrorMessage::EXPORT_ACCESS_DENIED->getError()
			);

			return $response;
		}

		$bp = \CBPWorkflowTemplateLoader::ExportTemplate($request->id);

		if (!$bp)
		{
			$response->addError(new Error('Not found', 404));

			return $response;
		}

		$response->setTemplateData((string) $bp);

		return $response;
	}

	public function saveTemplateDraft(
		WorkflowTemplateRequest\SaveTemplateRequest $request
	): WorkflowTemplateResponse\SaveTemplateDraftResponse
	{
		$response = new WorkflowTemplateResponse\SaveTemplateDraftResponse();

		$documentType = $this->getDocumentType($request->parameters);
		if (is_null($documentType))
		{
			$errorMsg = ErrorMessage::INVALID_PARAM_ARG->getError([
				'#PARAM#' => 'DOCUMENT_TYPE',
				'#VALUE#' => $documentType
			]);
			$response->addError($errorMsg);

			return $response;
		}

		if ($request->checkAccess && !$this->accessService->canCreateWorkflow($documentType, $request->user->getId()))
		{
			$response->addError(
				ErrorMessage::ACCESS_DENIED->getError()
			);

			return $response;
		}

		try
		{
			$template = WorkflowTemplate::createFromRequest($request);
			$templateId = $template->getTemplateId() > 0 ? $template->getTemplateId() : null;
			$fields = $template->getFields();

			$tpl = \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::createObject();
			$availableFields = [
				'NAME',
				'DESCRIPTION',
				'TYPE',
				'SORT',
				'AUTO_EXECUTE',
				'IS_SYSTEM',
				'TEMPLATE',
			];

			foreach ($availableFields as $field)
			{
				if (isset($fields[$field]))
				{
					$tpl->set($field, $fields[$field]);
				}
			}

			[$moduleId, $entity, $documentType] = $template->getDocumentType();
			$tpl->setModuleId($moduleId);
			$tpl->setEntity($entity);
			$tpl->setDocumentType($documentType);

			$result = WorkflowTemplateDraftTable::add([
				'MODULE_ID' => $tpl->getModuleId(),
				'ENTITY' => $tpl->getEntity(),
				'DOCUMENT_TYPE' => $tpl->getDocumentType(),
				'TEMPLATE_ID' => $templateId,
				'TEMPLATE_DATA' => $tpl->collectValues(),
				'USER_ID' => $request->user->getId(),
				'CREATED' => new \Bitrix\Main\Type\DateTime()
			]);

			if (!$result->isSuccess())
			{
				$response->addErrors($result->getErrors());

				return $response;
			}

			$response->setTemplateDraftId((int)$result->getId());
		}
		catch (\Throwable $exception)
		{
			$response->addError(new Error($exception->getMessage(), $exception->getCode()));
		}

		return $response;
	}

	public function loadTemplateDraft(
		WorkflowTemplateRequest\LoadTemplateDraftRequest $request
	): WorkflowTemplateResponse\LoadTemplateResponse
	{
		$response = new WorkflowTemplateResponse\LoadTemplateResponse();

		if ($request->id <= 0)
		{
			$response->addError(new Error('incorrect draft id'));

			return $response;
		}

		try
		{
			$draft = WorkflowTemplateDraftTable::getByPrimary($request->id)->fetchObject();
			if (!$draft)
			{
				$response->addError(ErrorMessage::GET_DATA_ERROR->getError());
			}

			$response->setData($draft->collectValues());
		}
		catch (\Throwable $exception)
		{
			$response->addError(new Error($exception->getMessage(), $exception->getCode()));
		}

		return $response;
	}
}