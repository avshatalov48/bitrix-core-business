<?php

use \Bitrix\Bizproc\FieldType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPStartWorkflowActivity extends CBPActivity implements IBPEventActivity, IBPActivityExternalEventListener
{
	private static $templatesCache = [];
	protected $wfId = null;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'DocumentId' => null,
			'TemplateId' => 0,
			'TemplateParameters' => [],
			'UseSubscription' => 'N',

			'WorkflowId' => null,
		];

		$this->SetPropertiesTypes([
			'WorkflowId' => [
				'Type' => 'string',
			],
		]);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();
		$this->WorkflowId = null;
	}

	public function OnExternalEvent($eventParameters = [])
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed || $this->wfId != $eventParameters[0])
		{
			return;
		}

		$this->Unsubscribe($this);
		$this->workflow->CloseActivity($this);
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
		{
			throw new Exception('eventHandler');
		}

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->SubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'bizproc', "OnWorkflowComplete", $this->wfId);

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
		{
			throw new Exception('eventHandler');
		}

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->UnSubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'bizproc', "OnWorkflowComplete", $this->wfId);

		$this->wfId = null;
		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
	}

	public function Cancel()
	{
		if ($this->UseSubscription == 'Y')
		{
			$this->Unsubscribe($this);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function Execute()
	{
		if (!$this->TemplateId)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$template = self::getTemplate($this->TemplateId);

		if (!$template)
		{
			$this->writeDebugInfo($this->getDebugInfo([
				'Entity' => '',
				'DocumentType' => ''
			]));

			$this->WriteToTrackingService(
				Bitrix\Main\Localization\Loc::getMessage('BPSWFA_TEMPLATE_NOT_FOUND_ERROR'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		$logMap = $this->getDebugInfo([
			'Entity' => implode('@', [$template['MODULE_ID'], $template['ENTITY']]),
			'DocumentType' => implode('@', $template['DOCUMENT_TYPE']),
		]);

		if (!$this->DocumentId)
		{
			$this->writeDebugInfo($logMap);

			$this->WriteToTrackingService(
				Bitrix\Main\Localization\Loc::getMessage('BPSWFA_ERROR_DOCUMENT_ID'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		$rootActivity = $this->GetRootActivity();
		$rootDocumentId = $rootActivity->GetDocumentId();
		$templateId = 0;
		if (method_exists($rootActivity, 'GetWorkflowTemplateId'))
		{
			$templateId = $rootActivity->GetWorkflowTemplateId();
			if ((int)$templateId === (int)$this->TemplateId)
			{
				$this->writeDebugInfo($logMap);

				$this->WriteToTrackingService(
					GetMessage("BPSWFA_SELFSTART_ERROR"),
					0,
					CBPTrackingType::Error
				);

				return CBPActivityExecutionStatus::Closed;
			}
		}

		$documentId = $template['DOCUMENT_TYPE'];
		$documentId[2] = $this->DocumentId;

		//if Multiple, take only first Id
		if (is_array($documentId[2]))
		{
			$documentId[2] = reset($documentId[2]);
		}

		/** @var CBPDocumentService $documentService */
		$documentService = CBPRuntime::GetRuntime()->GetService('DocumentService');
		$documentId = $documentService->normalizeDocumentId($documentId, $template['DOCUMENT_TYPE'][2]);

		//hotfix 141709
		if (
			preg_match('/^_[0-9]+$/', $documentId[2])
			&& $documentId[0] === 'crm'
			&& strpos($template['DOCUMENT_TYPE'][2], 'DYNAMIC_') === 0
		)
		{
			$documentId[2] = $template['DOCUMENT_TYPE'][2] . $documentId[2];
		}

		//check Document type
		try
		{
			$realDocumentType = $documentService->GetDocumentType($documentId)[2];
		}
		catch (Exception $e)
		{
			$realDocumentType = null;
		}

		if (!$realDocumentType || $realDocumentType != $template['DOCUMENT_TYPE'][2])
		{
			$this->writeDebugInfo($logMap);

			$this->WriteToTrackingService(
				$realDocumentType
					? GetMessage("BPSWFA_DOCTYPE_ERROR_1")
					: GetMessage("BPSWFA_DOCTYPE_NOT_FOUND_ERROR")
				,
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		$this->writeDebugInfo($logMap);

		$parameters = $this->TemplateParameters;
		if (!is_array($parameters))
		{
			$parameters = [];
		}

		if ($template['PARAMETERS'])
		{
			foreach ($template['PARAMETERS'] as $key => $parameter)
			{
				if ($parameter['Type'] == FieldType::USER && !empty($parameters[$key]))
				{
					$userIds = CBPHelper::ExtractUsers($parameters[$key], $rootDocumentId);
					if (is_array($userIds))
					{
						foreach ($userIds as $i => $uid)
						{
							$userIds[$i] = 'user_' . $uid;
						}
					}
					$parameters[$key] = $userIds;
				}
			}
		}

		if (!empty($parameters))
		{
			$this->writeDebugInfo($this->getDebugInfo($parameters, $template['PARAMETERS']));
		}

		$parameters[CBPDocument::PARAM_TAGRET_USER] = $this->GetRootActivity()->{CBPDocument::PARAM_TAGRET_USER};

		$errors = [];
		$this->wfId = CBPDocument::StartWorkflow(
			$template['ID'],
			$documentId,
			$parameters,
			$errors,
			['workflowId' => $this->GetWorkflowInstanceId(), 'templateId' => $templateId]
		);
		$this->WorkflowId = $this->wfId;
		$workflowIsCompleted = false;

		if ($this->wfId && !$errors)
		{
			$info = CBPRuntime::GetRuntime()->GetService('StateService')->getWorkflowStateInfo($this->wfId);
			if ($info['WORKFLOW_STATUS'] === null)
			{
				$workflowIsCompleted = true;
			}
		}

		if ($errors)
		{
			if ($this->UseSubscription == 'Y')
			{
				throw new Exception($errors[0]['message']);
			}
			else
			{
				$this->WriteToTrackingService(
					Bitrix\Main\Localization\Loc::getMessage("BPSWFA_START_ERROR", ['#MESSAGE#' => $errors[0]['message']]),
					0,
					CBPTrackingType::Error
				);
			}

			return CBPActivityExecutionStatus::Closed;
		}

		if ($workflowIsCompleted || $this->UseSubscription != 'Y' || !$this->wfId)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$this->Subscribe($this);

		return CBPActivityExecutionStatus::Executing;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$currentValues = null,
		$formName = "",
		$popupWindow = null,
		$siteId = ''
	)
	{
		$runtime = CBPRuntime::GetRuntime();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");

		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $currentValues,
			'formName' => $formName,
			'siteId' => $siteId,
		]);

		$entities = static::getEntities($documentService);
		$types = $templates = [];
		$currentEntity = $currentType = $currentTemplateId = $templateParametersRender = '';

		if (!is_array($currentValues))
		{
			$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (isset($currentActivity["Properties"]['DocumentId']))
			{
				$currentValues['document_id'] = $currentActivity["Properties"]['DocumentId'];
			}
			if (isset($currentActivity["Properties"]['TemplateId']))
			{
				$currentValues['template_id'] = $currentActivity["Properties"]['TemplateId'];
			}
			if (isset($currentActivity["Properties"]['TemplateParameters']))
			{
				$currentValues['template'] = $currentActivity["Properties"]['TemplateParameters'];
			}
			if (isset($currentActivity["Properties"]['UseSubscription']))
			{
				$currentValues['use_subscription'] = $currentActivity["Properties"]['UseSubscription'];
			}
		}
		else
		{
			$currentValues['template'] = self::extractTemplateParameterValues($documentType, $currentValues['template_id'], $currentValues);
		}

		if (!empty($currentValues['template_id']))
		{
			$template = self::getTemplate($currentValues['template_id']);
			if ($template)
			{
				$currentEntity = implode('@', [$template['MODULE_ID'], $template['ENTITY']]);
				$currentType = implode('@', $template['DOCUMENT_TYPE']);
				$currentTemplateId = $template['ID'];

				if (!is_array($currentValues['template']))
				{
					$currentValues['template'] = [];
				}
			}
		}

		if ($currentEntity)
		{
			$types = self::getTypesList($currentEntity);
		}

		if ($currentTemplateId)
		{
			$templates = self::getTemplatesList($currentType);
			if ($formName === 'bizproc_automation_robot_dialog')
			{
				$templateParametersRender = self::renderRobotTemplateParametersForm(
					$dialog,
					$currentTemplateId,
					$currentValues['template']
				);
			}
			else
			{
				$templateParametersRender = self::renderTemplateParametersForm(
					$documentType,
					$currentTemplateId,
					$formName,
					$currentValues['template']
				);
			}
		}

		$dialog->setMap([
			'DOCUMENT_ID' => [
				'Name' => GetMessage('BPSWFA_PD_DOCUMENT_ID'),
				'FieldName' => 'document_id',
				'Type' => FieldType::STRING,
				'Required' => true,
			],
		]);

		$dialog->setRuntimeData([
			'isAdmin' => static::checkAdminPermissions(),
			'documentType' => $documentType,

			'entities' => $entities,
			'types' => $types,
			'templates' => $templates,

			'documentId' => !empty($currentValues['document_id']) ? $currentValues['document_id'] : null,
			'useSubscription' => $currentValues['use_subscription'] ?? 'N',
			'currentEntity' => $currentEntity,
			'currentType' => $currentType,
			'currentTemplateId' => $currentTemplateId,
			'templateParametersRender' => $templateParametersRender,

			"formName" => $formName,
		]);

		return $dialog;
	}

	private static function getEntities(CBPDocumentService $documentService): array
	{
		$entities = [];

		$entityIterator = CBPWorkflowTemplateLoader::GetList(
			['MODULE_ID' => 'ASC'],
			['<AUTO_EXECUTE' => CBPDocumentEventType::Automation],
			['MODULE_ID', 'ENTITY'],
			false,
			['MODULE_ID', 'ENTITY']
		);

		while ($row = $entityIterator->fetch())
		{
			$entityName = $documentService->getEntityName($row['MODULE_ID'], $row['ENTITY']);
			if ($entityName)
			{
				$entities[$row['MODULE_ID'] . '@' . $row['ENTITY']] = $entityName;
			}
		}

		asort($entities);

		return $entities;
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$workflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$currentValues,
		&$errors
	)
	{
		$errors = [];

		$properties = [
			'DocumentId' => $currentValues['document_id'],
			'TemplateId' => $currentValues['template_id'],
			'UseSubscription' => isset($currentValues['use_subscription']) && $currentValues['use_subscription'] == 'Y' ? 'Y' : 'N',
			'TemplateParameters' => self::extractTemplateParameterValues($documentType, $currentValues['template_id'], $currentValues, $errors),
		];

		if (count($errors) > 0)
		{
			return false;
		}

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function ValidateProperties($testProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$errors = [];

		if (!static::checkAdminPermissions())
		{
			$errors[] = [
				"code" => "AccessDenied",
				"parameter" => "Admin",
				"message" => GetMessage("BPSWFA_ACCESS_DENIED_1"),
			];

			return array_merge($errors, parent::ValidateProperties($testProperties, $user));
		}

		if (empty($testProperties['DocumentId']))
		{
			$errors[] = ["code" => "NotExist", "parameter" => "DocumentId", "message" => GetMessage("BPSWFA_ERROR_DOCUMENT_ID")];
		}

		if (empty($testProperties['TemplateId']))
		{
			$errors[] = ["code" => "NotExist", "parameter" => "TemplateId", "message" => GetMessage("BPSWFA_ERROR_TEMPLATE")];
		}

		$template = self::getTemplate($testProperties['TemplateId']);
		if ($template && $template['PARAMETERS'])
		{
			foreach ($template['PARAMETERS'] as $key => $parameter)
			{
				$value = isset($testProperties['TemplateParameters'][$key]) ? $testProperties['TemplateParameters'][$key] : null;
				if (CBPHelper::getBool($parameter['Required']) && CBPHelper::isEmptyValue($value))
				{
					$errors[] = [
						"code" => "NotExist",
						"parameter" => "TemplateParameters_" . $key,
						"message" => GetMessage("BPSWFA_TEMPLATE_PARAMETERS_ERROR", ['#NAME#' => $parameter['Name']]),
					];
				}
			}
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function getAjaxResponse($request)
	{
		$result = false;
		if (!static::checkAdminPermissions())
		{
			return $result;
		}

		if (!empty($request['entity']))
		{
			$result = ['types' => self::getTypesList($request['entity'])];
		}

		if (!empty($request['document']))
		{
			$result = ['templates' => self::getTemplatesList($request['document'])];
		}

		if (!empty($request['template_id']) && !empty($request['form_name']))
		{
			if (isset($request['isRobot']) && $request['isRobot'] === 'y')
			{
				$result = self::renderRobotTemplateParametersForm(
					self::jsObjectToPropertiesDialog($request['properties_dialog']),
					$request['template_id']
				);
			}
			else
			{
				$result = self::renderTemplateParametersForm(
					$request['document_type'],
					$request['template_id'],
					$request['form_name']
				);
			}
		}

		return $result;
	}

	protected static function jsObjectToPropertiesDialog(array $dialog)
	{
		return new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $dialog['documentType'],
			'activityName' => $dialog['activityName'],
			'workflowTemplate' => $dialog['workflowTemplate'] ?? [],
			'workflowParameters' => $dialog['workflowParameters'] ?? [],
			'currentValues' => isset($dialog['currentValues']) ? $dialog['currentValues'] : [],
			'formName' => $dialog['formName'],
			'siteId' => $dialog['siteId'],
		]);
	}

	private static function checkAdminPermissions()
	{
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		return $user->isAdmin();
	}

	private static function getTypesList($entityId)
	{
		$runtime = CBPRuntime::GetRuntime();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");

		[$moduleId, $entity] = explode('@', $entityId);
		$result = [];

		$iterator = CBPWorkflowTemplateLoader::GetList(
			['MODULE_ID' => 'ASC'],
			['MODULE_ID' => $moduleId, 'ENTITY' => $entity],
			['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'],
			false,
			['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE']
		);
		while ($row = $iterator->fetch())
		{
			$name = $documentService->getDocumentTypeName($row['DOCUMENT_TYPE']);
			if ($name)
				$result[] = ['name' => $name, 'id' => implode('@', $row['DOCUMENT_TYPE'])];
		}

		return $result;
	}

	private static function getTemplatesList($document)
	{
		$result = [];

		$iterator = CBPWorkflowTemplateLoader::GetList(
			['NAME' => 'ASC'],
			[
				'DOCUMENT_TYPE' => explode('@', $document),
				'<AUTO_EXECUTE' => CBPDocumentEventType::Automation,
			],
			false,
			false,
			['ID', 'NAME']
		);
		while ($row = $iterator->fetch())
		{
			$result[] = ['name' => $row['NAME'], 'id' => $row['ID']];
		}

		return $result;
	}

	private static function getTemplate($id)
	{
		$id = (int)$id;
		if (!isset(self::$templatesCache[$id]))
		{
			$iterator = CBPWorkflowTemplateLoader::GetList([], ["ID" => $id], false, false, ["ID", 'NAME', "MODULE_ID", "ENTITY", "DOCUMENT_TYPE", 'PARAMETERS']);
			self::$templatesCache[$id] = $iterator->fetch();
		}

		return self::$templatesCache[$id];
	}

	private static function extractTemplateParameterValues($documentType, $templateId, $request, &$errors = [])
	{
		$template = self::getTemplate($templateId);
		$result = [];

		if ($template && $template['PARAMETERS'])
		{
			$runtime = CBPRuntime::GetRuntime();
			/** @var CBPDocumentService $documentService */
			$documentService = $runtime->GetService("DocumentService");

			foreach ($template['PARAMETERS'] as $key => $parameter)
			{
				$dt = $parameter['Type'] == FieldType::USER ? $documentType : $template['DOCUMENT_TYPE'];
				$result[$key] = $documentService->GetFieldInputValue(
					$dt,
					$parameter,
					'bpswfatemplate_' . $key,
					$request,
					$errors
				);
			}
		}

		return $result;
	}

	private static function renderTemplateParametersForm(
		$documentType,
		$templateId,
		$formName,
		array $currentValues = []
	)
	{
		$result = '';
		$template = self::getTemplate($templateId);
		if ($template)
		{
			if (!empty($template['PARAMETERS']))
			{
				$runtime = CBPRuntime::GetRuntime();
				/** @var CBPDocumentService $documentService */
				$documentService = $runtime->GetService("DocumentService");

				$result .= '<tr><td colspan="2" align="center">' . GetMessage('BPSWFA_TEMPLATE_PARAMETERS') . ':</td></tr>';
				foreach ($template['PARAMETERS'] as $key => $parameter)
				{
					$parameterKeyExt = 'bpswfatemplate_' . $key;
					$parameterValue = isset($currentValues[$key]) ? $currentValues[$key] : $parameter['Default'];
					$dt = $parameter['Type'] == FieldType::USER ? $documentType : $template['DOCUMENT_TYPE'];

					$result .= '<tr>
					<td align="right" width="40%" valign="top" class="adm-detail-content-cell-l">'
						. ($parameter['Required'] ? '<span class="adm-required-field">*' : '') . htmlspecialcharsbx($parameter['Name'])
						. ($parameter['Required'] ? '</span>' : '') . ':</td>
					<td width="60%" valign="top" class="adm-detail-content-cell-r">' . $documentService->GetFieldInputControl(
							$dt,
							$parameter,
							['Form' => $formName, 'Field' => $parameterKeyExt],
							$parameterValue,
							true
						)
						. '</td></tr>';
				}
			}
		}

		return $result;
	}

	private static function renderRobotTemplateParametersForm($dialog, $templateId, array $currentValues = [])
	{
		$result = '';
		$template = self::getTemplate($templateId);
		if ($template)
		{
			if (!empty($template['PARAMETERS']))
			{
				$result .= '<div class="bizproc-automation-popup-settings">';
				$result .= GetMessage('BPSWFA_TEMPLATE_PARAMETERS') . ": ";
				$result .= '</div>';

				foreach ($template['PARAMETERS'] as $fieldId => $field)
				{
					$field['FieldName'] = 'bpswfatemplate_' . $fieldId;
					$result .= '<div class="bizproc-automation-popup-settings">';
					$result .= "<span class=bizproc-automation-popup-settings-title>{$field['Name']}: </span>";

					$result .= $dialog->renderFieldControl($field, $currentValues[$fieldId] ?? null);

					$result .= '</div>';
				}
			}
		}

		return $result;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		$runtime = CBPRuntime::GetRuntime();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");

		$currentEntity = $context['currentEntity'] ?? null;
		$currentType = $context['currentType'] ?? null;

		return [
			'DocumentId' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSWFA_RPD_DOCUMENT_ID'),
				'Type' => 'string',
				'Multiple' => false,
				'Required' => true,
			],
			'Entity' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSWFA_RPD_ENTITY'),
				'Multiple' => false,
				'Required' => true,
				'Type' => 'select',
				'Options' => static::getEntities($documentService),
			],
			'DocumentType' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSWFA_RPD_DOCUMENT_TYPE'),
				'Multiple' => false,
				'Required' => true,
				'Type' => 'select',
				'Options' => $currentEntity ? static::getTypesList($currentEntity) : [],
			],
			'TemplateId' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSWFA_RPD_TEMPLATE'),
				'Multiple' => false,
				'Required' => true,
				'Type' => 'select',
				'Options' => $currentType ? static::getTemplatesList($currentType) : [],
			],
			'UseSubscription' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSWFA_RPD_USE_SUBSCRIPTION'),
				'Multiple' => false,
				'Type' => 'bool',
				'Required' => false
			],
		];
	}

	protected function getDebugInfo(array $values = [], array $map = []): array
	{
		if (empty($map))
		{
			$map = static::getPropertiesMap(
				$this->getDocumentType(),
				[
					'currentEntity' => $values['Entity'],
					'currentType' => $values['DocumentType'],
				]
			);

			$types = [];
			foreach ($map['DocumentType']['Options'] as $type)
			{
				$types[$type['id']] = $type['name'];
			}
			$map['DocumentType']['Options'] = $types;

			$templates = [];
			foreach ($map['TemplateId']['Options'] as $template)
			{
				$templates[$template['id']] = $template['name'];
			}
			$map['TemplateId']['Options'] = $templates;
		}

		return parent::getDebugInfo($values, $map);
	}
}
