<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Script\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Activity\PropertiesDialog;

/**
 * @property-read string $DocumentId
 * @property-read int $TemplateId
 * @property-read array $StartBy
 * @property-read array $TemplateParameters
 */
class CBPStartScriptActivity extends CBPActivity
{
	private static array $templatesCache = [];
	private static array $templateDocumentCache = [];

	private array $complexDocumentId = [];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'DocumentId' => null,
			'TemplateId' => 0,
			'StartBy' => null,
			'TemplateParameters' => [],
		];

		$this->setPropertiesTypes([
			'Title' => [
				'Type' => 'string',
			],
			'DocumentId' => [
				'Type' => 'string',
			],
			'TemplateId' => [
				'Type' => 'int',
			],
			'StartBy' => [
				'Type' => 'user',
			],
		]);
	}

	public function execute()
	{
		if (
			$this->isExecutePropertiesFilled()
			&& $this->checkTemplate()
			&& $this->checkDocumentId()
			&& $this->checkDocumentIdsLimit()
			&& $this->checkQueuesLimit()
			&& $this->checkLooping()
		)
		{
			$templateId = $this->TemplateId;
			$template = self::getTemplate($templateId);
			$scriptId = $template['SCRIPT_ID'];

			$userId = CBPHelper::extractFirstUser($this->StartBy, $this->getDocumentType());
			if ($userId === null)
			{
				$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_EMPTY_START_BY'));
			}
			elseif (!Manager::canUserStartScript($scriptId, $userId))
			{
				// todo: use activity loc message
				$startScriptResult = new \Bitrix\Bizproc\Script\StartScriptResult();
				$startScriptResult->addNotEnoughRightsError();

				$this->trackError($startScriptResult->getErrorMessages()[0]);
			}
			else
			{
				$result = Manager::startScript(
					$scriptId,
					$userId,
					[$this->complexDocumentId[2]],
					$this->getParameterRuntimeValues(),
				);

				if (!$result->isSuccess())
				{
					$this->trackError($result->getErrors()[0]->getMessage());
				}
			}

			self::$templateDocumentCache[$templateId][] = $this->complexDocumentId[2];
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function isExecutePropertiesFilled(): bool
	{
		$templateId = $this->TemplateId;
		if (!is_numeric($templateId) || (int)$templateId <= 0)
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_INCORRECT_TEMPLATE_ID'));

			return false;
		}
		if (CBPHelper::isEmptyValue($this->DocumentId))
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_EMPTY_DOCUMENT_ID'));

			return false;
		}
		if (CBPHelper::isEmptyValue($this->StartBy))
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_EMPTY_START_BY'));

			return false;
		}

		return true;
	}

	private function checkTemplate(): bool
	{
		$template = self::getTemplate($this->TemplateId);
		if ($template === false)
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_NOT_FOUND_TEMPLATE'));

			return false;
		}

		return true;
	}

	private function getParameterRuntimeValues(): array
	{
		$parameters = $this->TemplateParameters;
		if (!is_array($parameters))
		{
			$parameters = [];
		}

		$rootDocumentId = $this->getRootActivity()->getDocumentId();
		$templateParameters = self::getTemplate($this->TemplateId)['PARAMETERS'];
		if ($templateParameters)
		{
			foreach ($templateParameters as $key => $parameter)
			{
				if ($parameter['Type'] === FieldType::USER && !empty($parameters[$key]))
				{
					$userIds = CBPHelper::extractUsers($parameters[$key], $rootDocumentId, false);
					$userIds = array_map(static fn ($user) => '[' . $user . ']', $userIds);
					$parameters[$key] = implode(',', $userIds);
				}
			}
		}

		return $parameters;
	}

	private function checkDocumentId(): bool
	{
		$template = self::getTemplate($this->TemplateId);

		$documentId = $template['COMPLEX_DOCUMENT_TYPE'];
		$documentId[2] = $this->DocumentId;
		if (is_array($documentId[2]))
		{
			$documentId[2] = reset($documentId[2]);
		}

		$documentService = $this->workflow->getRuntime()->getDocumentService();
		$documentId = $documentService->normalizeDocumentId($documentId, $template['COMPLEX_DOCUMENT_TYPE'][2]);
		try
		{
			$realDocumentType = $documentService->getDocumentType($documentId);
		}
		catch (Exception $exception)
		{
			$realDocumentType = null;
		}
		if ($realDocumentType === null)
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_NOT_FOUND_DOCUMENT'));

			return false;
		}
		if (!CBPHelper::isEqualDocument($realDocumentType, $template['COMPLEX_DOCUMENT_TYPE']))
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_NOT_MATCH_DOCUMENT_TYPE'));

			return false;
		}
		$this->complexDocumentId = $documentId;

		return true;
	}

	private function checkDocumentIdsLimit(): bool
	{
		$templateId = (int)$this->TemplateId;

		if (isset(self::$templateDocumentCache[$templateId]))
		{
			if (!Manager::checkDocumentIdsLimit(self::$templateDocumentCache[$templateId]))
			{
				$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_LIMIT_DOCUMENT_IDS'));

				return false;
			}
		}
		else
		{
			self::$templateDocumentCache[$templateId] = [];
		}

		return true;
	}

	private function checkQueuesLimit(): bool
	{
		$template = self::getTemplate($this->TemplateId);
		if ($template)
		{
			if (!Manager::checkQueuesCount($template['SCRIPT_ID']))
			{
				$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_LIMIT_QUEUE'));

				return false;
			}
		}

		return true;
	}

	private function checkLooping(): bool
	{
		$rootActivity = $this->getRootActivity();
		if (
			(int)($rootActivity->getWorkflowTemplateId()) === (int)$this->TemplateId
			|| in_array($this->complexDocumentId[2], self::$templateDocumentCache[(int)$this->TemplateId], true)
		)
		{
			$this->trackError(Loc::getMessage('BP_SSA_ACTIVITY_LOOPING_ERROR'));

			return false;
		}

		return true;
	}

	public static function getPropertiesDialog(
		$documentType,
		$activityName,
		$workflowTemplate,
		$workflowParameters,
		$workflowVariables,
		$request = null,
		$formName = '',
		$popupWindow = null,
		$siteId = ''
	)
	{
		$dialog = new PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $workflowTemplate,
				'workflowParameters' => $workflowParameters,
				'workflowVariables' => $workflowVariables,
				'formName' => $formName,
				'siteId' => $siteId,
			],
		);
		$dialog->setMap(self::getPropertiesMap($documentType));

		$currentValues =
			is_array($request)
				? self::getCurrentValuesFromRequest($request, $documentType)
				: self::getCurrentValuesFromActivity($workflowTemplate, $activityName)
		;
		$dialog->setCurrentValues($currentValues);

		$dialog->setRuntimeData([
			'parametersForm' =>
				$currentValues['template_id']
					? self::renderParametersForm(
						$documentType,
						$formName,
						$currentValues['template_id'],
						(new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser))->getId(),
						$currentValues['parameters'],
					)
					: ''
			,
		]);

		return $dialog;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [
			'DocumentId' => [
				'Name' => Loc::getMessage('BP_SSA_ACTIVITY_MAP_DOCUMENT_ID'),
				'FieldName' => 'document_id',
				'Type' => FieldType::STRING,
				'Required' => true,
			],
			'TemplateId' => [
				'Name' => Loc::getMessage('BP_SSA_ACTIVITY_MAP_TEMPLATE_ID'),
				'FieldName' => 'template_id',
				'Type' => FieldType::INT,
				'Required' => true,
			],
			'StartBy' => [
				'Name' => Loc::getMessage('BP_SSA_ACTIVITY_MAP_START_BY'),
				'FieldName' => 'start_by',
				'Type' => FieldType::USER,
				'Required' => true,
			]
		];
	}

	private static function getCurrentValuesFromRequest(array $request, array $documentType): array
	{
		$templateId =
			isset($request['template_id']) && is_numeric($request['template_id'])
				? (int)$request['template_id']
				: null
		;
		$parameters = [];
		if ($templateId)
		{
			[$parameters, $errors] = self::extractParameterValues($templateId, $documentType, $request);
		}

		return [
			'template_id' => $templateId,
			'document_id' => $request['document_id'] ?? null,
			'start_by' => $request['start_by'] ?? null,
			'parameters' => $parameters,
		];
	}

	private static function getCurrentValuesFromActivity(array $template, string $activityName): array
	{
		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($template, $activityName);
		$properties = $currentActivity['Properties'] ?? [];

		return [
			'template_id' => isset($properties['TemplateId']) ? (int)$properties['TemplateId'] : null,
			'document_id' => $properties['DocumentId'] ?? null,
			'start_by' => $properties['StartBy'] ?? null,
			'parameters' => $properties['TemplateParameters'] ?? [],
		];
	}

	private static function renderParametersForm(
		array $documentType,
		string $formName,
		int $templateId,
		int $userId,
		array $values = []
	): string
	{
		$result = '';

		$template = self::getTemplate($templateId);
		if ($template && $template['PARAMETERS'])
		{
			$canShow = Manager::canUserStartScript($template['SCRIPT_ID'], $userId);
			$result .=
				'<tr><td colspan="2">' . Loc::getMessage('BP_SSA_ACTIVITY_MAP_PARAMETERS') . '</td></tr>'
			;

			if ($canShow)
			{
				$documentService = CBPRuntime::getRuntime()->getDocumentService();
				foreach ($template['PARAMETERS'] as $id => $parameter)
				{
					$value = array_key_exists($id, $values) ? $values[$id] : $parameter['Default'];
					$dt =
						$parameter['Type'] === FieldType::USER
							? $documentType
							: $template['COMPLEX_DOCUMENT_TYPE']
					;
					$control = $documentService->getFieldInputControl(
						$dt,
						$parameter,
						['Form' => $formName, 'Field' => self::getParameterFormKey($id)],
						$value,
						true
					);

					$name = !CBPHelper::isEmptyValue($parameter['Name']) ? $parameter['Name'] : $parameter['Description'];
					$result .=
						'<tr>
						<td align="right" width="40%" valign="top" class="adm-detail-content-cell-l">'
						. ($parameter['Required'] ? '<span class="adm-required-field">*' : '')
						. htmlspecialcharsbx($name)
						. ($parameter['Required'] ? '</span>' : '')
						. ':</td>
						<td width="60%" valign="top" class="adm-detail-content-cell-r">' . $control . '</td>
					</tr>'
					;
				}
			}
			else
			{
				$result .=
					'<tr><td colspan="2">' . Loc::getMessage('BP_SSA_ACTIVITY_MAP_PARAMETERS_HIDDEN') . '</td></tr>'
				;
			}
		}

		return $result;
	}

	public static function getPropertiesDialogValues(
		$documentType,
		$activityName,
		&$workflowTemplate,
		&$workflowParameters,
		&$workflowVariables,
		$currentValues,
		&$errors
	)
	{
		$errors = [];
		$documentId = $currentValues['document_id'] ?? null;
		$templateId =
			(
				isset($currentValues['template_id'])
				&& is_numeric($currentValues['template_id'])
				&& $currentValues['template_id'] > 0
			)
				? (int)$currentValues['template_id']
				: null
		;

		$startBy = CBPHelper::usersStringToArray($currentValues['start_by'], $documentType, $errors);
		$parameters = [];

		if ($templateId)
		{
			[$parameters, $extractErrors] = self::extractParameterValues($templateId, $documentType, $currentValues);
			if ($extractErrors)
			{
				$errors = $extractErrors;

				return false;
			}
		}

		$properties = [
			'DocumentId' => $documentId,
			'TemplateId' => $templateId,
			'StartBy' => $startBy,
			'TemplateParameters' => $parameters,
		];

		$currentUser =  new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$errors = self::validateProperties($properties, $currentUser);
		if ($errors)
		{
			return false;
		}

		$activity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$activity['Properties'] = $properties;

		return true;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		if (CBPHelper::isEmptyValue($arTestProperties['DocumentId']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'DocumentId',
				'message' => Loc::getMessage('BP_SSA_ACTIVITY_VALIDATE_EMPTY_DOCUMENT_ID'),
			];
		}
		if (CBPHelper::isEmptyValue($arTestProperties['TemplateId']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'TemplateId',
				'message' => Loc::getMessage('BP_SSA_ACTIVITY_VALIDATE_EMPTY_TEMPLATE_ID'),
			];
		}
		if (CBPHelper::isEmptyValue($arTestProperties['StartBy']))
		{
			$errors[] = [
				'code' => 'NotExist',
				'parameter' => 'StartBy',
				'message' => Loc::getMessage('BP_SSA_ACTIVITY_VALIDATE_EMPTY_START_BY'),
			];
		}

		$template = $arTestProperties['TemplateId'] ? self::getTemplate($arTestProperties['TemplateId']) : null;
		if ($template && $template['PARAMETERS'])
		{
			foreach ($template['PARAMETERS'] as $id => $parameter)
			{
				$value = $arTestProperties['TemplateParameters'][$id] ?? null;
				if (CBPHelper::getBool($parameter['Required']) && CBPHelper::isEmptyValue($value))
				{
					$errors[] = [
						'code' => 'NotExist',
						'parameter' => 'TemplateParameters_' . $id,
						'message' =>
							Loc::getMessage(
								'BP_SSA_ACTIVITY_TEMPLATE_PARAMETERS_ERROR',
								['#NAME#' => $parameter['Name'] ?? $parameter['Description']]
							),
					];
				}
			}
		}

		return array_merge($errors, parent::validateProperties($arTestProperties, $user));
	}

	public static function getAjaxResponse($request)
	{
		$result = false;
		$isDocumentTypeCorrect = isset($request['document_type']) && is_array($request['document_type']);
		$isTemplateIdCorrect = isset($request['template_id']) && is_numeric($request['template_id']);
		$isFormNameCorrect =
			isset($request['form_name'])
			&& is_string($request['form_name'])
			&& !CBPHelper::isEmptyValue($request['form_name'])
		;

		if ($isDocumentTypeCorrect && $isFormNameCorrect && $isTemplateIdCorrect)
		{
			$result = self::renderParametersForm(
				$request['document_type'],
				$request['form_name'],
				(int)$request['template_id'],
				(new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser))->getId(),
			);
		}

		return $result;
	}

	private static function getTemplate(int $templateId)
	{
		if (!isset(self::$templatesCache[$templateId]))
		{
			$selectFields = [
				'ID',
				'MODULE_ID',
				'ENTITY',
				'DOCUMENT_TYPE',
				'WORKFLOW_TEMPLATE_ID',
				'WORKFLOW_TEMPLATE.NAME',
				'WORKFLOW_TEMPLATE.PARAMETERS'
			];

			// todo: use Script Manager
			$script =
				\Bitrix\Bizproc\Script\Entity\ScriptTable::query()
					->setSelect($selectFields)
					->where('WORKFLOW_TEMPLATE_ID', $templateId)
					->where('ACTIVE', 'Y')
					->exec()
					->fetchObject()
			;
			$template = false;

			if ($script)
			{
				$moduleId =  $script->getModuleId();
				$entity = $script->getEntity();
				$documentType = $script->getDocumentType();

				$template = [
					'ID' => $script->getWorkflowTemplateId(),
					'MODULE_ID' => $moduleId,
					'ENTITY' => $entity,
					'DOCUMENT_TYPE' => $documentType,
					'NAME' => $script->getWorkflowTemplate()->getName(),
					'PARAMETERS' => $script->getWorkflowTemplate()->getParameters(),
					'COMPLEX_DOCUMENT_TYPE' => [$moduleId, $entity, $documentType],
					'SCRIPT_ID' => $script->getId(),
				];
			}

			self::$templatesCache[$templateId] = $template;
		}

		return self::$templatesCache[$templateId];
	}

	private static function extractParameterValues(int $templateId, array $documentType, array $request): array
	{
		$result = [];
		$errors = [];

		$template = self::getTemplate($templateId);
		if ($template && $template['PARAMETERS'])
		{
			$documentService = CBPRuntime::getRuntime()->getDocumentService();
			foreach ($template['PARAMETERS'] as $id => $parameter)
			{
				$dt =
					$parameter['Type'] === FieldType::USER
						? $documentType
						: $template['COMPLEX_DOCUMENT_TYPE']
				;
				$result[$id] = $documentService->getFieldInputValue(
					$dt,
					$parameter,
					self::getParameterFormKey($id),
					$request,
					$errors
				);
			}
		}

		return [$result, $errors];
	}

	private static function getParameterFormKey(string $id): string
	{
		return 'bpssaparameter_' . $id;
	}
}
