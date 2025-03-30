<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Api\Request\WorkflowService\PrepareParametersRequest;
use Bitrix\Bizproc\Api\Request\WorkflowService\StartWorkflowRequest;
use Bitrix\Bizproc\Api\Request\WorkflowStateService\GetAverageWorkflowDurationRequest;
use Bitrix\Bizproc\Api\Service\WorkflowService;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class BizprocWorkflowStart extends \CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['MODULE_ID'] = trim(
			empty($arParams['MODULE_ID']) ? ($_REQUEST['module_id'] ?? '') : $arParams['MODULE_ID']
		);
		$arParams['ENTITY'] = trim(empty($arParams['ENTITY']) ? ($_REQUEST['entity'] ?? '') : $arParams['ENTITY']);
		$arParams['DOCUMENT_TYPE'] = trim(
			empty($arParams['DOCUMENT_TYPE']) ? ($_REQUEST['document_type'] ?? '') : $arParams['DOCUMENT_TYPE']
		);
		$arParams['DOCUMENT_ID'] = trim(
			empty($arParams['DOCUMENT_ID']) ? ($_REQUEST['document_id'] ?? '') : $arParams['DOCUMENT_ID']
		);
		$arParams['TEMPLATE_ID'] =
			isset($arParams['TEMPLATE_ID'])
				? (int)$arParams['TEMPLATE_ID']
				: (int)($_REQUEST['workflow_template_id'] ?? 0)
		;
		$arParams['AUTO_EXECUTE_TYPE'] =
			isset($arParams['AUTO_EXECUTE_TYPE'])
				? (int)$arParams['AUTO_EXECUTE_TYPE']
				: null
		;

		$arParams['SET_TITLE'] = (($arParams['SET_TITLE'] ?? 'Y') === 'N' ? 'N' : 'Y');

		if (Main\Loader::includeModule('bizproc'))
		{
			if (is_string($arParams['SIGNED_DOCUMENT_TYPE'] ?? null) && $arParams['SIGNED_DOCUMENT_TYPE'])
			{
				$unsignedDocumentType = CBPDocument::unSignDocumentType(
					htmlspecialcharsback($arParams['SIGNED_DOCUMENT_TYPE'])
				);

				$arParams['MODULE_ID'] = $unsignedDocumentType ? $unsignedDocumentType[0] : '';
				$arParams['ENTITY'] = $unsignedDocumentType ? $unsignedDocumentType[1] : '';
				$arParams['DOCUMENT_TYPE'] = $unsignedDocumentType ? $unsignedDocumentType[2] : '';
			}

			if (is_string($arParams['SIGNED_DOCUMENT_ID'] ?? null) && $arParams['SIGNED_DOCUMENT_ID'])
			{
				$unsignedDocumentId = CBPDocument::unSignDocumentType(
					htmlspecialcharsback($arParams['SIGNED_DOCUMENT_ID'])
				);

				$arParams['DOCUMENT_ID'] = $unsignedDocumentId ? $unsignedDocumentId[2] : '';
			}
		}

		return $arParams;
	}

	public function executeComponent()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		if ($this->getTemplateName() === 'slider')
		{
			$this->prepareSliderResult();

			if (isset($this->arResult['errors']))
			{
				$this->includeComponentTemplate('error');

				return false;
			}

			if ($this->isSingleStart())
			{
				$this->includeComponentTemplate('single_start');
			}
			else
			{
				$this->includeComponentTemplate($this->isAutostart() ? 'autostart' : '');
			}

			return true;
		}

		$errors = $this->checkParams();
		if ($errors)
		{
			return $this->showErrorMessages($errors);
		}

		$this->arResult['DOCUMENT_ID'] = $this->arParams['DOCUMENT_ID'];
		$this->arResult['DOCUMENT_TYPE'] = $this->arParams['DOCUMENT_TYPE'];
		$this->arResult['back_url'] = trim($_REQUEST['back_url'] ?? '');

		$this->arParams['DOCUMENT_TYPE'] = $this->getComplexDocumentType();
		$this->arParams['DOCUMENT_ID'] = $this->getComplexDocumentId();
		$this->arParams['USER_GROUPS'] = $this->getUserGroupArray();

		if ($this->isAutostart())
		{
			$this->autoStartParametersAction($this->arParams['AUTO_EXECUTE_TYPE']);

			return true;
		}

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('BPABS_TITLE'));
		}

		if (!$this->canUserStartWorkflowOnDocument())
		{
			return $this->showErrorMessages([$this->getErrorByCode('access_denied')]);
		}

		if (!empty($_REQUEST['cancel']) && !empty($_REQUEST['back_url']))
		{
			LocalRedirect(str_replace('#WF#', '', $_REQUEST['back_url']));
		}

		$this->arResult['SHOW_MODE'] = 'SelectWorkflow';
		$this->arResult['TEMPLATES'] = $this->getTemplatesForStart();
		$this->arResult['PARAMETERS_VALUES'] = [];
		$this->arResult['ERROR_MESSAGE'] = '';

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$this->arResult['DocumentService'] = $runtime->GetService('DocumentService');

		$templateId = $this->arParams['TEMPLATE_ID'];
		if (
			$this->isSingleStart()
			&& empty($_POST['CancelStartParamWorkflow'])
			&& array_key_exists($templateId, $this->arResult['TEMPLATES'])
		)
		{
			$this->startParametersAction($templateId);

			return true;
		}

		$this->IncludeComponentTemplate();

		return true;
	}

	private function prepareSliderResult(): void
	{
		$errors = $this->checkParams();
		if ($errors)
		{
			$this->arResult = ['errors' => $errors];

			return;
		}

		if ($this->isSingleStart())
		{
			if (!$this->canUserStartWorkflowOnDocument())
			{
				$this->arResult = ['errors' => [$this->getErrorByCode('access_denied')]];

				return;
			}

			$templateId = (int)$this->arParams['TEMPLATE_ID'];
			$template = $this->getTemplateById($templateId);
			if (!$template)
			{
				$this->arResult = ['errors' => [$this->getErrorByCode('template_not_found')]];

				return;
			}

			$workflowStateService = new WorkflowStateService();
			$averageDuration = $workflowStateService->getAverageWorkflowDuration(
				new GetAverageWorkflowDurationRequest($templateId)
			);

			$isConstantsTuned = CBPWorkflowTemplateLoader::isConstantsTuned($templateId);
			if (!$isConstantsTuned && !$this->canUserCreateWorkflowOnDocumentType())
			{
				unset($template['CONSTANTS']);
			}

			$this->arResult = [
				'template' => $template,
				'isConstantsTuned' => $isConstantsTuned,
				'duration' => $averageDuration->isSuccess() ? $averageDuration->getRoundedAverageDuration() : null,
				'documentType' => $this->getComplexDocumentType(),
				'signedDocumentType' => CBPDocument::signDocumentType($this->getComplexDocumentType()),
				'signedDocumentId' => CBPDocument::signDocumentType($this->getComplexDocumentId()),
			];

			return;
		}

		if ($this->isAutostart())
		{
			$executeType = (int)$this->arParams['AUTO_EXECUTE_TYPE'];
			$states = CBPWorkflowTemplateLoader::getDocumentTypeStates($this->getComplexDocumentType(), $executeType);
			if (
				!$this->canUserStartWorkflowOnDocument()
				&& !$this->canUserStartWorkflowOnDocumentType(['DocumentStates' => $states])
			)
			{
				$this->arResult = ['errors' => [$this->getErrorByCode('access_denied')]];

				return;
			}

			$templatesFromStates = $this->getTemplatesWithParametersFromStates($states);
			if (!$templatesFromStates)
			{
				$this->arResult = ['errors' => [$this->getErrorByCode('empty_autostart_parameters')]];

				return;
			}

			$documentId = $this->getComplexDocumentId();
			$this->arResult = [
				'templates' => $templatesFromStates,
				'documentType' => $this->getComplexDocumentType(),
				'signedDocumentType' => CBPDocument::signDocumentType($this->getComplexDocumentType()),
				'signedDocumentId' => !empty($documentId[2]) ? CBPDocument::signDocumentType($documentId) : '',
				'autoExecuteType' => $executeType,
			];

			return;
		}

		$this->arResult = ['errors' => [$this->getErrorByCode('access_denied')]];
	}

	private function getTemplateById(int $templateId): bool|array
	{
		return CBPWorkflowTemplateLoader::getList(
			[],
			[
				'ID' => $templateId,
				'DOCUMENT_TYPE' => $this->getComplexDocumentType(),
				'ACTIVE' => 'Y',
				'IS_SYSTEN' => 'N',
				'<AUTO_EXECUTE' => CBPDocumentEventType::Automation,
			],
			false,
			false,
			['ID', 'NAME', 'DESCRIPTION', 'PARAMETERS', 'CONSTANTS'],
		)->fetch();
	}

	private function startParametersAction(int $templateId): void
	{
		$errors = [];

		$template = $this->arResult['TEMPLATES'][$templateId];
		$hasParameters = is_array($template['PARAMETERS']) && $template['PARAMETERS'];
		$canStartWorkflow = !$hasParameters;

		$parameters = [];
		if ($hasParameters && $this->isDoStartParamWorkflowAction())
		{
			['errors' => $errors, 'parameters' => $parameters] =
				$this->prepareStartParametersFromRequest($template['PARAMETERS'])
			;
			$canStartWorkflow = !$errors;
		}

		$isConstantsTuned = CBPWorkflowTemplateLoader::isConstantsTuned($templateId);
		if (!$isConstantsTuned)
		{
			$errors[] = $this->getErrorByCode('required_constants');
			$canStartWorkflow = false;
		}

		if ($canStartWorkflow)
		{
			$startResult = $this->startWorkflow($templateId, $parameters);
			if ($startResult['errors'])
			{
				$this->arResult['SHOW_MODE'] = 'StartWorkflowError';
				$errors = array_merge($errors, $startResult['errors']);
			}
			else
			{
				$this->arResult['SHOW_MODE'] = 'StartWorkflowSuccess';
				if (!empty($this->arResult['back_url']))
				{
					LocalRedirect(str_replace('#WF#', $startResult['workflowId'], $_REQUEST['back_url']));
				}
			}
		}
		else
		{
			$this->arResult['PARAMETERS_VALUES'] = $this->restoreWorkflowStartParameters($template['PARAMETERS']);
			$this->arResult['SHOW_MODE'] = $isConstantsTuned ? 'WorkflowParameters' : 'StartWorkflowError';
		}

		if ($errors)
		{
			$this->arResult['ERROR_MESSAGE'] = $this->createErrorMessage($errors);
		}

		$this->IncludeComponentTemplate();
	}

	private function prepareStartParametersFromRequest(array $templateParameters): array
	{
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

		$response =
			(new WorkflowService())
				->prepareParameters(
					new PrepareParametersRequest(
						templateParameters: $templateParameters,
						requestParameters: array_merge($request->toArray(), $request->getFileList()->toArray()),
						complexDocumentType: $this->getComplexDocumentType(),
					)
			)
		;

		$errors = [];
		if (!$response->isSuccess())
		{
			foreach ($response->getErrors() as $error)
			{
				$errors[] = $this->createCheckWorkflowParametersError($error->jsonSerialize());
			}
		}

		return ['errors' => $errors, 'parameters' => $response->getParameters()];
	}

	private function getTemplatesForStart(): array
	{
		// todo: use?
		// CBPDocument::getTemplatesForStart(
		// 	$this->getCurrentUserId(),
		// 	$this->getComplexDocumentType(),
		// 	$this->getComplexDocumentId(),
		// 	['UserGroups' => $this->arParams['USER_GROUPS'] ?? $this->getUserGroupArray()],
		// );

		$dbWorkflowTemplate = CBPWorkflowTemplateLoader::getList(
			['SORT' => 'ASC', 'NAME' => 'ASC'],
			[
				'DOCUMENT_TYPE' => $this->getComplexDocumentType(),
				'ACTIVE' => 'Y',
				'IS_SYSTEM' => 'N',
				'<AUTO_EXECUTE' => CBPDocumentEventType::Automation,
			],
			false,
			false,
			['ID', 'NAME', 'DESCRIPTION', 'MODIFIED', 'USER_ID', 'PARAMETERS', 'AUTO_EXECUTE']
		);

		$templates = [];
		while ($template = $dbWorkflowTemplate->GetNext())
		{
			$templates[$template['ID']] = $template;
			$templates[$template['ID']]['URL'] = htmlspecialcharsbx(
				$GLOBALS['APPLICATION']->GetCurPageParam(
					'workflow_template_id=' . $template['ID'] . '&' . bitrix_sessid_get(),
					['workflow_template_id', 'sessid']
				)
			);
		}

		if ($templates && mb_strtolower($this->arParams['MODULE_ID']) === 'webdav')
		{
			return $this->filterTemplatesByStartWorkflowAccess($templates);
		}

		return $templates;
	}

	private function filterTemplatesByStartWorkflowAccess(array $templates): array
	{
		$states = CBPDocument::GetDocumentStates($this->getComplexDocumentType(), $this->getComplexDocumentId());

		$result = [];
		foreach ($templates as $key => $template)
		{
			$checkAccessParameters = ['WorkflowTemplateId' => $key, 'DocumentStates' => $states];
			if ($this->canUserStartWorkflowOnDocument($checkAccessParameters))
			{
				$result[$key] = $template;
			}
		}

		return $result;
	}

	private function startWorkflow(int $templateId, array $workflowParameters): array
	{
		$currentUserId = $this->getCurrentUserId();

		$response =
			(new WorkflowService())
				->startWorkflow(
					new StartWorkflowRequest(
						userId: $currentUserId,
						targetUserId: $currentUserId,
						templateId: $templateId,
						complexDocumentId: $this->getComplexDocumentId(),
						parameters: array_merge(
							$workflowParameters,
							[
								CBPDocument::PARAM_TAGRET_USER => 'user_' . $currentUserId,
								CBPDocument::PARAM_DOCUMENT_EVENT_TYPE => CBPDocumentEventType::Manual,
							],
						),
						startDuration: 0, // todo start duration
						checkAccess: false, // checked earlier
					)
				)
		;

		$errors = [];
		if (!$response->isSuccess())
		{
			foreach ($response->getErrors() as $error)
			{
				$errors[] = $this->createStartWorkflowError($error->jsonSerialize());
			}
		}

		return ['errors' => $errors, 'workflowId' => $response->getWorkflowId()];
	}

	private function restoreWorkflowStartParameters(array $templateParameters): array
	{
		$hasParametersInRequest = $this->isDoStartParamWorkflowAction();
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

		$restored = [];
		foreach ($templateParameters as $key => $property)
		{
			$restored[$key] = $this->convertParameterValues(
				$hasParametersInRequest ? $request->get($key) : $property['Default']
			);
		}

		return $restored;
	}

	private function isDoStartParamWorkflowAction(): bool
	{
		$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

		return $request->isPost() && !empty($request->get('DoStartParamWorkflow'));
	}

	private function isAutostart(): bool
	{
		return $this->arParams['AUTO_EXECUTE_TYPE'] !== null;
	}

	private function isSingleStart(): bool
	{
		return $this->arParams['TEMPLATE_ID'] > 0;
	}

	protected function autoStartParametersAction($execType)
	{
		$states = CBPWorkflowTemplateLoader::getDocumentTypeStates($this->getComplexDocumentType(), $execType);

		if (
			!$this->canUserStartWorkflowOnDocument()
			&& !$this->canUserStartWorkflowOnDocumentType(['DocumentStates' => $states])
		)
		{
			return $this->showErrorMessages([$this->getErrorByCode('access_denied')]);
		}

		$this->arResult['TEMPLATES'] = $this->getTemplatesWithParametersFromStates($states);

		if (!$this->arResult['TEMPLATES'])
		{
			return $this->showErrorMessages([$this->getErrorByCode('empty_autostart_parameters')]);
		}

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$this->arResult['DocumentService'] = $runtime->GetService('DocumentService');
		$this->arResult['EXEC_TYPE'] = $execType;

		$this->IncludeComponentTemplate('autostart');

		return true;
	}

	private function getTemplatesWithParametersFromStates(array $documentStates): array
	{
		$templates = [];
		foreach ($documentStates as $template)
		{
			if (!is_array($template['TEMPLATE_PARAMETERS']) || !$template['TEMPLATE_PARAMETERS'])
			{
				continue;
			}

			$templates[] = [
				'ID' => $template['TEMPLATE_ID'],
				'NAME' => $template['TEMPLATE_NAME'],
				'DESCRIPTION' => $template['TEMPLATE_DESCRIPTION'],
				'PARAMETERS' => $this->getTemplateParametersFromState($template),
			];
		}

		return $templates;
	}

	private function getTemplateParametersFromState(array $template): array
	{
		$parameters = [];
		foreach ($template['TEMPLATE_PARAMETERS'] as $parameterKey => $parameter)
		{
			if ($parameterKey === 'TargetUser')
			{
				continue;
			}

			$parameter['Default'] = $this->convertParameterValues($parameter['Default']);
			$parameters["bizproc{$template['TEMPLATE_ID']}_{$parameterKey}"] = $parameter;
		}

		return $parameters;
	}

	private function convertParameterValues($values)
	{
		if (!is_array($values))
		{
			return CBPHelper::convertParameterValues($values);
		}

		$convertedValues = [];
		foreach ($values as $key => $value)
		{
			$convertedValues[$key] = CBPHelper::convertParameterValues($value);
		}

		return $convertedValues;
	}

	private function checkParams(): array
	{
		$errors = [];

		if (empty($this->arParams['MODULE_ID']))
		{
			$errors[] = $this->getErrorByCode('empty_module_id');
		}

		if (empty($this->arParams['ENTITY']))
		{
			$errors[] = $this->getErrorByCode('empty_entity');
		}

		if (empty($this->arParams['DOCUMENT_TYPE']))
		{
			$errors[] = $this->getErrorByCode('empty_document_type');
		}

		if (empty($this->arParams['DOCUMENT_ID']) && $this->arParams['AUTO_EXECUTE_TYPE'] === null)
		{
			$errors[] = $this->getErrorByCode('empty_document_id');
		}

		if ($this->arParams['AUTO_EXECUTE_TYPE'] === null && !check_bitrix_sessid())
		{
			$errors[] = $this->getErrorByCode('access_denied');
		}

		return $errors;
	}

	private function getUserGroupArray(): array
	{
		$documentType = $this->getComplexDocumentType();

		$userGroups = CBPDocument::getUserGroups(
			$documentType,
			$this->getComplexDocumentId(),
			$this->getCurrentUserId()
		);

		if (is_array($userGroups))
		{
			return $userGroups;
		}

		return Main\Engine\CurrentUser::get()->getUserGroups();
	}

	private function canUserStartWorkflowOnDocument(array $parameters = []): bool
	{
		$documentId = $this->getComplexDocumentId();

		if (empty($documentId[2]))
		{
			return false;
		}

		if (!isset($parameters['UserGroups']))
		{
			$parameters['UserGroups'] = $this->arParams['USER_GROUPS'] ?? $this->getUserGroupArray();
		}

		return CBPDocument::canUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$this->getCurrentUserId(),
			$documentId,
			$parameters
		);
	}

	private function canUserStartWorkflowOnDocumentType(array $parameters = []): bool
	{
		if (!isset($parameters['UserGroups']))
		{
			$parameters['UserGroups'] = $this->arParams['USER_GROUPS'] ?? $this->getUserGroupArray();
		}

		return CBPDocument::canUserOperateDocumentType(
			CBPCanUserOperateOperation::StartWorkflow,
			$this->getCurrentUserId(),
			$this->getComplexDocumentType(),
			$parameters
		);
	}

	private function canUserCreateWorkflowOnDocumentType(): bool
	{
		return CBPDocument::canUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$this->getCurrentUserId(),
			$this->getComplexDocumentType(),
		);
	}

	private function getComplexDocumentType(): array
	{
		return (
			is_array($this->arParams['DOCUMENT_TYPE'])
				? $this->arParams['DOCUMENT_TYPE']
				: [$this->arParams['MODULE_ID'], $this->arParams['ENTITY'], $this->arParams['DOCUMENT_TYPE']]
		);
	}

	private function getComplexDocumentId(): array
	{
		return (
			is_array($this->arParams['DOCUMENT_ID'])
				? $this->arParams['DOCUMENT_ID']
				: [$this->arParams['MODULE_ID'], $this->arParams['ENTITY'], $this->arParams['DOCUMENT_ID']]
		);
	}

	private function showErrorMessages(array $errors): bool
	{
		ShowError($this->createErrorMessage($errors));

		return false;
	}

	private function createErrorMessage(array $errors): string
	{
		return (new CAdminException($errors))->GetString();
	}

	private function getErrorByCode(string $code): array
	{
		$text = match ($code)
		{
			'empty_module_id' => Loc::getMessage('BPATT_NO_MODULE_ID'),
			'empty_entity' => Loc::getMessage('BPABS_EMPTY_ENTITY'),
			'empty_document_type' => Loc::getMessage('BPABS_EMPTY_DOC_TYPE'),
			'empty_document_id' => Loc::getMessage('BPABS_EMPTY_DOC_ID'),
			'access_denied' => Loc::getMessage('BIZPROC_CMP_WORKFLOW_START_TEMPLATE_NO_PERMISSIONS'),
			'required_constants' => Loc::getMessage('BPABS_REQUIRED_CONSTANTS'),
			'empty_autostart_parameters' => Loc::getMessage('BPABS_NO_AUTOSTART_PARAMETERS'),
			'template_not_found' => Loc::getMessage('BIZPROC_CMP_WORKFLOW_START_TEMPLATE_NOT_FOUND') ?? '',
			default => '',
		};

		if ($code === 'empty_autostart_parameters')
		{
			$code = 'access_denied'; // compatibility
		}

		return $this->createError($code, $text);
	}

	private function createStartWorkflowError(array $error): array
	{
		$message = ($error['code'] > 0 ? '[' . $error['code'] . '] ' : '') . $error['message'];

		return $this->createError('StartWorkflowError', $message);
	}

	private function createCheckWorkflowParametersError(array $error): array
	{
		return $this->createError('CheckWorkflowParameters', $error['message']);
	}

	private function createError(string $code, string $message): array
	{
		return ['id' => $code, 'text' => $message];
	}

	private function getCurrentUserId(): int
	{
		return (int)(Main\Engine\CurrentUser::get()->getId());
	}
}
