<?
use \Bitrix\Bizproc\FieldType;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPStartWorkflowActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private static $templatesCache = array();
	protected $wfId = null;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			'Title'      => '',
			'DocumentId' => null,
			'TemplateId' => 0,
			'TemplateParameters' => array(),
			'UseSubscription' => 'N',

			'WorkflowId' => null
		);

		$this->SetPropertiesTypes(array(
			'WorkflowId' => array(
				'Type' => 'string'
			)
		));
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();
		$this->WorkflowId = null;
	}

	public function OnExternalEvent($eventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed || $this->wfId != $eventParameters[0])
			return;

		$this->Unsubscribe($this);
		$this->workflow->CloseActivity($this);
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception('eventHandler');

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->SubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'bizproc', "OnWorkflowComplete", $this->wfId);

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception('eventHandler');

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->UnSubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'bizproc', "OnWorkflowComplete", $this->wfId);

		$this->wfId = null;
		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
	}

	public function Cancel()
	{
		if ($this->UseSubscription == 'Y')
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	public function HandleFault(Exception $exception)
	{
		if ($exception == null)
			throw new Exception("exception");

		$status = $this->Cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
			return CBPActivityExecutionStatus::Faulting;

		return $status;
	}

	public function Execute()
	{
		if (!$this->TemplateId)
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$rootDocumentId = $rootActivity->GetDocumentId();
		$templateId = 0;
		if (method_exists($rootActivity, 'GetWorkflowTemplateId'))
		{
			$templateId = $rootActivity->GetWorkflowTemplateId();
			if ((int) $templateId === (int) $this->TemplateId)
			{
				$this->WriteToTrackingService(GetMessage("BPSWFA_SELFSTART_ERROR"));
				return CBPActivityExecutionStatus::Closed;
			}
		}

		$template = self::getTemplate($this->TemplateId);

		if (!$template || !$this->DocumentId)
			return CBPActivityExecutionStatus::Closed;

		$documentId = $template['DOCUMENT_TYPE'];
		$documentId[2] = $this->DocumentId;

		//if Multiple, take only first Id
		if (is_array($documentId[2]))
		{
			reset($documentId[2]);
			$documentId[2] = current($documentId[2]);
		}

		/** @var CBPDocumentService $documentService */
		$documentService = CBPRuntime::GetRuntime()->GetService('DocumentService');
		$documentId = $documentService->normalizeDocumentId($documentId);

		$parameters = $this->TemplateParameters;
		if (!is_array($parameters))
			$parameters = array();

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
							$userIds[$i] = 'user_'.$uid;
					}
					$parameters[$key] = $userIds;
				}
			}
		}

		$parameters[CBPDocument::PARAM_TAGRET_USER] = $this->GetRootActivity()->{CBPDocument::PARAM_TAGRET_USER};

		$this->wfId = CBPDocument::StartWorkflow(
			$template['ID'],
			$documentId,
			$parameters,
			$errors,
			array('workflowId' => $this->GetWorkflowInstanceId(), 'templateId' => $templateId)
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
				throw new Exception($errors[0]['message']);
			else
				$this->WriteToTrackingService(GetMessage("BPSWFA_START_ERROR", array('#MESSAGE#' => $errors[0]['message'])));

			return CBPActivityExecutionStatus::Closed;
		}

		if ($workflowIsCompleted || $this->UseSubscription != 'Y')
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);
		return CBPActivityExecutionStatus::Executing;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $currentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService("DocumentService");

		$entities = $types = $templates = array();
		$currentEntity = $currentType = $currentTemplateId = $templateParametersRender = '';

		$entityIterator = CBPWorkflowTemplateLoader::GetList(
			array('MODULE_ID' => 'ASC'),
			array(),
			array('MODULE_ID', 'ENTITY'),
			false,
			array('MODULE_ID', 'ENTITY')
		);
		while ($row = $entityIterator->fetch())
		{
			$entityName = $documentService->getEntityName($row['MODULE_ID'], $row['ENTITY']);
			if ($entityName)
				$entities[$row['MODULE_ID'].'@'.$row['ENTITY']] = $entityName;
		}

		if (!is_array($currentValues))
		{
			$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (isset($currentActivity["Properties"]['DocumentId']))
				$currentValues['document_id'] = $currentActivity["Properties"]['DocumentId'];
			if (isset($currentActivity["Properties"]['TemplateId']))
				$currentValues['template_id'] = $currentActivity["Properties"]['TemplateId'];
			if (isset($currentActivity["Properties"]['TemplateParameters']))
				$currentValues['template'] = $currentActivity["Properties"]['TemplateParameters'];
			if (isset($currentActivity["Properties"]['UseSubscription']))
				$currentValues['use_subscription'] = $currentActivity["Properties"]['UseSubscription'];
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
				$currentEntity = implode('@', array($template['MODULE_ID'], $template['ENTITY']));
				$currentType = implode('@', $template['DOCUMENT_TYPE']);
				$currentTemplateId = $template['ID'];

				if (!is_array($currentValues['template']))
					$currentValues['template'] = array();
			}
		}

		if ($currentEntity)
			$types = self::getTypesList($currentEntity);

		if ($currentTemplateId)
		{
			$templates = self::getTemplatesList($currentType);
			$templateParametersRender = self::renderTemplateParametersForm($documentType, $currentTemplateId, $formName, $currentValues['template']);
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				'isAdmin' => static::checkAdminPermissions(),
				'documentType' => $documentType,

				'entities' => $entities,
				'types' => $types,
				'templates' => $templates,

				'documentId' => !empty($currentValues['document_id']) ? $currentValues['document_id'] : null,
				'useSubscription' => $currentValues['use_subscription'],
				'currentEntity' => $currentEntity,
				'currentType' => $currentType,
				'currentTemplateId' => $currentTemplateId,
				'templateParametersRender' => $templateParametersRender,

				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$workflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $currentValues, &$errors)
	{
		$errors = array();

		$properties = array(
			'DocumentId' => $currentValues['document_id'],
			'TemplateId' => $currentValues['template_id'],
			'UseSubscription' => isset($currentValues['use_subscription']) && $currentValues['use_subscription'] == 'Y' ? 'Y' : 'N',
			'TemplateParameters' => self::extractTemplateParameterValues($documentType, $currentValues['template_id'], $currentValues, $errors),
		);

		if (count($errors) > 0)
			return false;

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
			return false;

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		return true;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = array();

		if (!static::checkAdminPermissions())
		{
			$errors[] = array(
					"code"      => "AccessDenied",
					"parameter" => "Admin",
					"message"   => GetMessage("BPSWFA_ACCESS_DENIED")
			);
			return array_merge($errors, parent::ValidateProperties($testProperties, $user));
		}

		if (empty($testProperties['DocumentId']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "DocumentId", "message" => GetMessage("BPSWFA_ERROR_DOCUMENT_ID"));
		}

		if (empty($testProperties['TemplateId']))
		{
			$errors[] = array("code" => "NotExist", "parameter" => "TemplateId", "message" => GetMessage("BPSWFA_ERROR_TEMPLATE"));
		}

		$template = self::getTemplate($testProperties['TemplateId']);
		if ($template && $template['PARAMETERS'])
		{
			foreach ($template['PARAMETERS'] as $key => $parameter)
			{
				$value = isset($testProperties['TemplateParameters'][$key]) ? $testProperties['TemplateParameters'][$key] : null;
				if (CBPHelper::getBool($parameter['Required']) && CBPHelper::isEmptyValue($value))
				{
					$errors[] = array(
						"code" => "NotExist",
						"parameter" => "TemplateParameters_".$key,
						"message" => GetMessage("BPSWFA_TEMPLATE_PARAMETERS_ERROR", array('#NAME#' => $parameter['Name']))
					);
				}
			}
		}


		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function getAjaxResponse($request)
	{
		$result = false;
		if (!static::checkAdminPermissions())
			return $result;

		if (!empty($request['entity']))
		{
			$result = array('types' => self::getTypesList($request['entity']));
		}

		if (!empty($request['document']))
		{
			$result = array('templates' => self::getTemplatesList($request['document']));
		}

		if (!empty($request['template_id']) && !empty($request['form_name']))
		{
			$result = self::renderTemplateParametersForm($request['document_type'], $request['template_id'], $request['form_name']);
		}

		return $result;
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

		list($moduleId, $entity) = explode('@', $entityId);
		$result = array();

		$iterator = CBPWorkflowTemplateLoader::GetList(
			array('MODULE_ID' => 'ASC'),
			array('MODULE_ID' => $moduleId, 'ENTITY' => $entity),
			array('MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'),
			false,
			array('MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE')
		);
		while ($row = $iterator->fetch())
		{
			$name = $documentService->getDocumentTypeName($row['DOCUMENT_TYPE']);
			if ($name)
				$result[] = array('name' => $name, 'id' => implode('@', $row['DOCUMENT_TYPE']));
		}

		return $result;
	}

	private static function getTemplatesList($document)
	{
		$result = array();

		$iterator = CBPWorkflowTemplateLoader::GetList(
			array('NAME' => 'ASC'),
			array(
				'DOCUMENT_TYPE' => explode('@', $document),
				'!AUTO_EXECUTE' => CBPDocumentEventType::Automation
			),
			false,
			false,
			array('ID', 'NAME')
		);
		while ($row = $iterator->fetch())
		{
			$result[] = array('name' => $row['NAME'], 'id' => $row['ID']);
		}
		return $result;
	}

	private static function getTemplate($id)
	{
		$id = (int) $id;
		if (!isset(self::$templatesCache[$id]))
		{
			$iterator = CBPWorkflowTemplateLoader::GetList(array(), array("ID" => $id), false, false, array("ID", 'NAME', "MODULE_ID", "ENTITY", "DOCUMENT_TYPE", 'PARAMETERS'));
			self::$templatesCache[$id] = $iterator->fetch();
		}
		return self::$templatesCache[$id];
	}

	private static function extractTemplateParameterValues($documentType, $templateId, $request, &$errors = array())
	{
		$template = self::getTemplate($templateId);
		$result = array();

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
					'bpswfatemplate_'.$key,
					$request,
					$errors
				);
			}
		}

		return $result;
	}

	private static function renderTemplateParametersForm($documentType, $templateId, $formName, array $currentValues = array())
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

				$result .= '<tr><td colspan="2" align="center">'.GetMessage('BPSWFA_TEMPLATE_PARAMETERS').':</td></tr>';
				foreach ($template['PARAMETERS'] as $key => $parameter)
				{
					$parameterKeyExt = 'bpswfatemplate_'.$key;
					$parameterValue = isset($currentValues[$key]) ? $currentValues[$key] : $parameter['Default'];
					$dt = $parameter['Type'] == FieldType::USER ? $documentType : $template['DOCUMENT_TYPE'];

					$result .= '<tr>
					<td align="right" width="40%" valign="top" class="adm-detail-content-cell-l">'
					.($parameter['Required'] ? '<span class="adm-required-field">*' : '').htmlspecialcharsbx($parameter['Name'])
					.($parameter['Required'] ? '</span>' : '').':</td>
					<td width="60%" valign="top" class="adm-detail-content-cell-r">'.$documentService->GetFieldInputControl(
							$dt,
							$parameter,
							array('Form' => $formName, 'Field' => $parameterKeyExt),
							$parameterValue,
							true
						)
					.'</td></tr>';
				}
			}
		}
		return $result;
	}
}