<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Bizproc;

class CBPRequestInformationActivity
	extends CBPCompositeActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	const ACTIVITY = 'RequestInformationActivity';
	const CONTROLS_PREFIX = 'bpriact_';

	protected $taskId = 0;
	protected $taskUsers = array();
	protected $subscriptionId = 0;
	protected $isInEventActivityMode = false;
	protected $taskStatus = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Users" => null,
			"Name" => null,
			"Description" => null,
			"Parameters" => null,
			"OverdueDate" => null,
			"RequestedInformation" => null,
			"ResponcedInformation" => null,
			"TaskId" => 0,
			"Comments" => "",
			"TaskButtonMessage" => "",
			"CommentLabelMessage" => "",
			"ShowComment" => "Y",
			'CommentRequired' => 'N',
			"StatusMessage" => "",
			"SetStatusMessage" => "Y",
			"AccessControl" => "N",
			"InfoUser" => null,
			"TimeoutDuration" => 0,
			"TimeoutDurationType" => "s",
			"IsTimeout" => 0,
			"Changes" => array(),
			'DelegationType' => 0,
		);

		$this->SetPropertiesTypes($this->getPropertiesTypesMap());
	}

	protected function getPropertiesTypesMap()
	{
		return [
			'TaskId' => ['Type' => 'int'],
			'Comments' => array(
				'Type' => 'string'
			),
			'InfoUser' => array(
				'Type' => 'user'
			),
			'IsTimeout' => array(
				'Type' => 'int',
			),
			'Changes' => array(
				'Type' => 'string',
				'Multiple' => true
			),
		];
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->TaskId = 0;
		$this->Comments = '';
		$this->InfoUser = null;
		$this->IsTimeout = 0;
		$this->Changes = array();
	}

	public function Execute()
	{
		if ($this->isInEventActivityMode)
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);

		$this->isInEventActivityMode = false;
		return CBPActivityExecutionStatus::Executing;
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;

		$arUsersTmp = $this->Users;
		if (!is_array($arUsersTmp))
			$arUsersTmp = array($arUsersTmp);

		$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPRIA_ACT_TRACK1")));

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);

		$overdueDate = $this->OverdueDate;
		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$overdueDate = ConvertTimeStamp(time() + max($timeoutDuration, CBPSchedulerService::getDelayMinLimit()), "FULL");
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		/** @var CBPTaskService $taskService */
		$taskService = $this->workflow->GetService("TaskService");
		$this->taskId = $taskService->CreateTask(
			array(
				"USERS" => $arUsers,
				"WORKFLOW_ID" => $this->GetWorkflowInstanceId(),
				"ACTIVITY" => static::ACTIVITY,
				"ACTIVITY_NAME" => $this->name,
				"OVERDUE_DATE" => $overdueDate,
				"NAME" => $this->Name,
				"DESCRIPTION" => $this->Description,
				"PARAMETERS" => $this->getTaskParameters($documentId, $documentService),
				'DELEGATION_TYPE' => (int)$this->DelegationType,
				'DOCUMENT_NAME' => $documentService->GetDocumentName($documentId)
			)
		);
		$this->TaskId = $this->taskId;
		$this->taskUsers = $arUsers;

		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
		{
			$message = ($this->IsPropertyExists("StatusMessage") && $this->StatusMessage <> '') ? $this->StatusMessage : GetMessage("BPRIA_ACT_INFO");
			$this->SetStatusTitle($message);
		}

		if ($timeoutDuration > 0)
		{
			/** @var CBPSchedulerService $schedulerService */
			$schedulerService = $this->workflow->GetService("SchedulerService");
			$this->subscriptionId = $schedulerService->SubscribeOnTime($this->workflow->GetInstanceId(), $this->name, time() + $timeoutDuration);
		}

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	protected function getTaskParameters($documentId, $documentService)
	{
		$taskParameters = $this->Parameters;
		if (!is_array($taskParameters))
			$taskParameters = array($taskParameters);

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$taskParameters["DOCUMENT_ID"] = $documentId;
		$taskParameters["DOCUMENT_URL"] = $documentService->GetDocumentAdminPage($documentId);
		$taskParameters["DOCUMENT_TYPE"] = $this->GetDocumentType();
		$taskParameters["FIELD_TYPES"] = $documentService->GetDocumentFieldTypes($taskParameters["DOCUMENT_TYPE"]);
		$taskParameters["REQUEST"] = array();
		$taskParameters["TaskButtonMessage"] = $this->IsPropertyExists("TaskButtonMessage") ? $this->TaskButtonMessage : GetMessage("BPRIA_ACT_BUTTON1");
		if ($taskParameters["TaskButtonMessage"] == '')
			$taskParameters["TaskButtonMessage"] = GetMessage("BPRIA_ACT_BUTTON1");
		$taskParameters["CommentLabelMessage"] = $this->IsPropertyExists("CommentLabelMessage") ? $this->CommentLabelMessage : GetMessage("BPRIA_ACT_COMMENT");
		if ($taskParameters["CommentLabelMessage"] == '')
			$taskParameters["CommentLabelMessage"] = GetMessage("BPRIA_ACT_COMMENT");
		$taskParameters["ShowComment"] = $this->IsPropertyExists("ShowComment") ? $this->ShowComment : "Y";
		if ($taskParameters["ShowComment"] != "Y" && $taskParameters["ShowComment"] != "N")
			$taskParameters["ShowComment"] = "Y";

		$taskParameters["CommentRequired"] = $this->IsPropertyExists("CommentRequired") ? $this->CommentRequired : "N";
		$taskParameters["AccessControl"] = $this->IsPropertyExists("AccessControl") && $this->AccessControl == 'Y' ? 'Y' : 'N';

		$requestedInformation = $this->RequestedInformation;
		if ($requestedInformation && is_array($requestedInformation) && count($requestedInformation) > 0)
		{
			foreach ($requestedInformation as $v)
			{
				if (CBPHelper::isEmptyValue($v['Default']))
				{
					$varValue = $this->GetVariable($v['Name']);
					if (!CBPDocument::IsExpression($varValue))
					{
						$v['Default'] = $varValue;
					}
				}

				$taskParameters["REQUEST"][] = $v;
			}
		}
		return $taskParameters;
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$taskService = $this->workflow->GetService("TaskService");
		if ($this->taskStatus === false)
		{
			$taskService->DeleteTask($this->taskId);
		}
		else
		{
			$taskService->Update($this->taskId, array(
				'STATUS' => $this->taskStatus
			));
		}

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$schedulerService = $this->workflow->GetService("SchedulerService");
			$schedulerService->UnSubscribeOnTime($this->subscriptionId);
		}

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);

		$this->taskId = 0;
		$this->taskUsers = array();
		$this->taskStatus = false;
		$this->subscriptionId = 0;
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

	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->taskId > 0)
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	public function OnExternalEvent($eventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
			return;

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			if (array_key_exists("SchedulerService", $eventParameters) && $eventParameters["SchedulerService"] == "OnAgent")
			{
				$this->IsTimeout = 1;
				$this->taskStatus = CBPTaskStatus::Timeout;
				$this->Unsubscribe($this);
				$this->closeActivity();
				return;
			}
		}

		if (!array_key_exists("USER_ID", $eventParameters) || intval($eventParameters["USER_ID"]) <= 0)
			return;

		if (empty($eventParameters["REAL_USER_ID"]))
			$eventParameters["REAL_USER_ID"] = $eventParameters["USER_ID"];

		$rootActivity = $this->GetRootActivity();
		$arUsers = $this->taskUsers;
		if (empty($arUsers)) //compatibility
			$arUsers = CBPHelper::ExtractUsers($this->Users, $this->GetDocumentId(), false);

		$eventParameters["USER_ID"] = intval($eventParameters["USER_ID"]);
		$eventParameters["REAL_USER_ID"] = intval($eventParameters["REAL_USER_ID"]);
		if (!in_array($eventParameters["USER_ID"], $arUsers))
			return;

		$this->Comments = $eventParameters["COMMENT"];

		if ($this->IsPropertyExists("InfoUser"))
			$this->InfoUser = "user_".$eventParameters["REAL_USER_ID"];
		$this->completeTask($eventParameters, $rootActivity);
	}

	protected function closeActivity()
	{
		$this->workflow->CloseActivity($this);
	}

	protected function completeTask($eventParameters, $rootActivity)
	{
		$this->Changes = $this->findRequestChanges($this->RequestedInformation, $eventParameters["RESPONCE"]);

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $eventParameters["REAL_USER_ID"], CBPTaskUserStatus::Ok);

		$this->WriteToTrackingService(
			str_replace(
				array("#PERSON#", "#COMMENT#"),
				array("{=user:user_".$eventParameters["REAL_USER_ID"]."}", ($eventParameters["COMMENT"] <> '' ? ": ".$eventParameters["COMMENT"] : "")),
				GetMessage("BPRIA_ACT_APPROVE_TRACK")
			),
			$eventParameters["REAL_USER_ID"]
		);

		$this->ResponcedInformation = $eventParameters["RESPONCE"];
		$rootActivity->SetVariables($eventParameters["RESPONCE"]);

		$this->taskStatus = CBPTaskStatus::CompleteOk;
		$this->Unsubscribe($this);

		$this->workflow->CloseActivity($this);
	}

	protected function OnEvent(CBPActivity $sender)
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	public static function ShowTaskForm($arTask, $userId, $userName = "", $arRequest = null)
	{
		$form = '';

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$isMobile = defined('BX_MOBILE');

		if ($arTask["PARAMETERS"] && is_array($arTask["PARAMETERS"]) && count($arTask["PARAMETERS"]) > 0
			&& $arTask["PARAMETERS"]["REQUEST"] && is_array($arTask["PARAMETERS"]["REQUEST"]) && count($arTask["PARAMETERS"]["REQUEST"]) > 0)
		{
			foreach ($arTask["PARAMETERS"]["REQUEST"] as $parameter)
			{
				if ($parameter["Name"] == '')
					continue;

				$nameHtml = $parameter['Required']
					? sprintf(
						'<span class="required">*</span><span class="adm-required-field">%s:</span>',
						htmlspecialcharsbx($parameter['Title'])
					)
					: htmlspecialcharsbx($parameter['Title']) . ':'
				;

				$descriptionHtml = $parameter['Description']
					? sprintf(
						'<br/><span class="bizproc-field-description">%s</span>',
						htmlspecialcharsbx($parameter['Description'])
					)
					: ''
				;

				if ($arRequest === null)
					$realValue = $parameter['Default'];
				else
					$realValue = $arRequest[static::CONTROLS_PREFIX.$parameter['Name']];

				$controlHtml = $documentService->GetFieldInputControl(
					$arTask['PARAMETERS']['DOCUMENT_TYPE'],
					$parameter,
					['task_form1', static::CONTROLS_PREFIX.$parameter['Name']],
					$realValue,
					false,
					true
				);

				$rowHtml = '
					<tr>
						<td valign="top" width="30%%" align="right" class="bizproc-field-name">%s%s</td>
						<td valign="top" width="70%%" class="bizproc-field-value">%s</td>
					</tr>
				';

				if ($isMobile)
				{
					$rowHtml = '
						<tr>
							<td valign="top" colspan="2" class="bizproc-field-name">%s%s</td>
						</tr>
						<tr>
							<td valign="top" colspan="2" class="bizproc-field-value">%s</td>
						</tr>
					';
				}


				$form .= sprintf($rowHtml, $nameHtml, $descriptionHtml, $controlHtml);
			}
		}

		if (!array_key_exists("ShowComment", $arTask["PARAMETERS"]) || ($arTask["PARAMETERS"]["ShowComment"] != "N"))
		{
			$required = '';
			if (isset($arTask['PARAMETERS']['CommentRequired']) && $arTask['PARAMETERS']['CommentRequired'] == 'Y')
			{
				$required = '<span style="color: red">*</span>';
			}

			$commentText = $arRequest ? $arRequest['task_comment'] : '';
			$rowHtml = '
				<tr>
					<td valign="top" width="30%%" align="right" class="bizproc-field-name">%s%s:</td>
					<td valign="top" width="70%%" class="bizproc-field-value">
						<textarea rows="3" cols="50" name="task_comment">%s</textarea>
					</td>
				</tr>
			';

			if ($isMobile)
			{
				$rowHtml = '
					<tr>
						<td valign="top" colspan="2" class="bizproc-field-name">%s%s:</td>
					</tr>
					<tr>
						<td valign="top" colspan="2" class="bizproc-field-value">
							<textarea rows="3" cols="50" name="task_comment">%s</textarea>
						</td>
					</tr>
				';
			}

			$form .= sprintf(
				$rowHtml,
				$arTask["PARAMETERS"]["CommentLabelMessage"]  ?: GetMessage("BPRIA_ACT_COMMENT"),
				$required,
				htmlspecialcharsbx($commentText)
			);
		}

		$buttons = '<input type="submit" name="approve" value="'.($arTask["PARAMETERS"]["TaskButtonMessage"] <> '' ? $arTask["PARAMETERS"]["TaskButtonMessage"] : GetMessage("BPRIA_ACT_BUTTON1")).'"/>';

		return array($form, $buttons);
	}

	public static function getTaskControls($arTask)
	{
		return array(
			'BUTTONS' => array(
				array(
					'TYPE'  => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::Ok,
					'NAME'  => 'approve',
					'VALUE' => 'Y',
					'TEXT'  => $arTask["PARAMETERS"]["TaskButtonMessage"] <> '' ? $arTask["PARAMETERS"]["TaskButtonMessage"] : GetMessage("BPAA_ACT_BUTTON1")
				)
			)
		);
	}

	protected static function getEventParameters($task, $request)
	{
		return array(
			"COMMENT" => isset($request["task_comment"]) ? trim($request["task_comment"]) : '',
			"RESPONCE" => isset($request['fields'])
				? static::prepareResponseFields($task, $request['fields']) : static::getTaskResponse($task),
		);
	}

	protected static function getTaskResponse($task)
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$result = array();

		if ($task["PARAMETERS"] && is_array($task["PARAMETERS"]) && count($task["PARAMETERS"]) > 0
			&& $task["PARAMETERS"]["REQUEST"] && is_array($task["PARAMETERS"]["REQUEST"]) && count($task["PARAMETERS"]["REQUEST"]) > 0)
		{
			$request = $_REQUEST;

			foreach ($_FILES as $k => $v)
			{
				if (array_key_exists("name", $v))
				{
					if (is_array($v["name"]))
					{
						$ks = array_keys($v["name"]);
						if (!is_array($request[$k]))
							$request[$k] = array();
						for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
						{
							$ar = array();
							foreach ($v as $k1 => $v1)
								$ar[$k1] = $v1[$ks[$i]];

							$request[$k][] = $ar;
						}
					}
					else
					{
						$request[$k] = $v;
					}
				}
			}

			foreach ($task["PARAMETERS"]["REQUEST"] as $parameter)
			{
				$arErrorsTmp = array();

				$result[$parameter["Name"]] = $documentService->GetFieldInputValue(
					$task["PARAMETERS"]["DOCUMENT_TYPE"],
					$parameter,
					static::CONTROLS_PREFIX.$parameter["Name"],
					$request,
					$arErrorsTmp
				);

				if (count($arErrorsTmp) > 0)
				{
					$m = "";
					foreach ($arErrorsTmp as $e)
						$m .= $e["message"]."<br />";
					throw new CBPArgumentException($m);
				}

				if (
					CBPHelper::getBool($parameter['Required'])
					&& CBPHelper::isEmptyValue($result[$parameter['Name']])
				)
					throw new CBPArgumentNullException(
						$parameter["Name"],
						str_replace(
							"#PARAM#",
							htmlspecialcharsbx($parameter["Title"]),
							GetMessage("BPRIA_ARGUMENT_NULL")
						)
					);
			}
		}

		return $result;
	}

	protected static function prepareResponseFields($task, $values)
	{
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();

		$result = [];

		if ($task["PARAMETERS"] && is_array($task["PARAMETERS"]) && count($task["PARAMETERS"]) > 0
			&& $task["PARAMETERS"]["REQUEST"] && is_array($task["PARAMETERS"]["REQUEST"]) && count($task["PARAMETERS"]["REQUEST"]) > 0)
		{
			foreach ($task["PARAMETERS"]["REQUEST"] as $property)
			{
				$title = $property["Title"];
				$property = Bizproc\FieldType::normalizeProperty($property);
				$fieldTypeObject = $documentService->getFieldTypeObject($task["PARAMETERS"]["DOCUMENT_TYPE"], $property);
				if ($fieldTypeObject)
				{
					$fieldTypeObject->setDocumentId($task["PARAMETERS"]["DOCUMENT_ID"]);
					$result[$property['Name']] = $fieldTypeObject->internalizeValue($task['ACTIVITY_NAME'], $values[$property['Name']]);
				}

				if (
					CBPHelper::getBool($property['Required'])
					&& CBPHelper::isEmptyValue($result[$property['Name']])
				)
					throw new CBPArgumentNullException(
						$property["Name"],
						str_replace(
							"#PARAM#",
							htmlspecialcharsbx($title),
							GetMessage("BPRIA_ARGUMENT_NULL")
						)
					);
			}
		}

		return $result;
	}

	protected static function validateTaskEventParameters($arTask, $eventParameters)
	{
		if (
			isset($arTask['PARAMETERS']['ShowComment'])
			&& $arTask['PARAMETERS']['ShowComment'] === 'Y'
			&& isset($arTask['PARAMETERS']['CommentRequired'])
			&& empty($eventParameters['COMMENT'])
			&& $arTask['PARAMETERS']['CommentRequired'] === 'Y'
		)
		{
			$label = $arTask["PARAMETERS"]["CommentLabelMessage"] <> '' ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPAR_ACT_COMMENT");
			throw new CBPArgumentNullException(
				'task_comment',
				GetMessage("BPRIA_ACT_COMMENT_ERROR", array(
					'#COMMENT_LABEL#' => $label
				))
			);
		}

		return true;
	}

	public static function PostTaskForm($task, $userId, $request, &$errors, $userName = "", $realUserId = null)
	{
		$errors = array();

		try
		{
			$userId = intval($userId);
			if ($userId <= 0)
				throw new CBPArgumentNullException("userId");

			$arEventParameters = static::getEventParameters($task, $request);
			$arEventParameters["USER_ID"]= $userId;
			$arEventParameters["REAL_USER_ID"] = $realUserId;
			$arEventParameters["USER_NAME"] = $userName;

			static::validateTaskEventParameters($task, $arEventParameters);
			CBPRuntime::SendExternalEvent($task["WORKFLOW_ID"], $task["ACTIVITY_NAME"], $arEventParameters);
			return true;
		}
		catch (Exception $e)
		{
			$errors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]",
			);
		}

		return false;
	}

	private function findRequestChanges($properties, $values)
	{
		$result = array();

		foreach ($properties as $key => $property)
		{
			$a = (array) $property['Default'];
			$b = (array) (isset($values[$property['Name']]) ? $values[$property['Name']] : null);

			if ($a != $b)
				$result[$property['Name']] = $property['Title'];
		}

		return $result;
	}

	private function CalculateTimeoutDuration()
	{
		$timeoutDuration = ($this->IsPropertyExists("TimeoutDuration") ? $this->TimeoutDuration : 0);

		$timeoutDurationType = ($this->IsPropertyExists("TimeoutDurationType") ? $this->TimeoutDurationType : "s");
		$timeoutDurationType = mb_strtolower($timeoutDurationType);
		if (!in_array($timeoutDurationType, array("s", "d", "h", "m")))
			$timeoutDurationType = "s";

		$timeoutDuration = intval($timeoutDuration);
		switch ($timeoutDurationType)
		{
			case 'd':
				$timeoutDuration *= 3600 * 24;
				break;
			case 'h':
				$timeoutDuration *= 3600;
				break;
			case 'm':
				$timeoutDuration *= 60;
				break;
			default:
				break;
		}

		return min($timeoutDuration, 3600 * 24 * 365 * 5);
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId)
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$dialog = new Bizproc\Activity\PropertiesDialog(__FILE__, [
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		]);

		$dialog->setMap(static::getPropertiesDialogMap());

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$requestedInformation = $currentActivity['Properties']['RequestedInformation'] ?: [];

		$requestedVariables = [];
		foreach ($requestedInformation as $variable)
		{
			if($variable['Name'] == '')
			{
				continue;
			}

			$variable['Required'] = CBPHelper::getBool($variable['Required']) ? 'Y' : 'N';
			$variable['Multiple'] = CBPHelper::getBool($variable['Multiple']) ? 'Y' : 'N';
			$requestedVariables[] = $variable;
		}

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);
		unset($arFieldTypes['N:Sequence']);
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		$javascriptFunctions = $documentService->GetJSFunctionsForFields(
			$documentType,
			"objFields",
			$arDocumentFields,
			$arFieldTypes
		);

		$dialog->setRuntimeData([
			"requestedInformation" => $requestedVariables,
			"arDocumentFields" => $arDocumentFields,
			"arFieldTypes" => $arFieldTypes,
			"javascriptFunctions" => $javascriptFunctions,
			"formName" => $formName,
			"popupWindow" => &$popupWindow,
		]);
		return $dialog;
	}

	protected static function getDefaultLabels()
	{
		return [
			'comment_label_message' => GetMessage("BPRIA_ACT_COMMENT"),
			'task_button_message' => GetMessage("BPRIA_ACT_BUTTON1"),
			'status_message' => GetMessage("BPRIA_ACT_INFO")
		];
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$workflowTemplate, &$arWorkflowParameters, &$workflowVariables, $currentValues, &$errors)
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		$properties = [];
		$documentService = $runtime->GetService('DocumentService');

		foreach (static::getPropertiesDialogMap() as $fieldId => $fieldMap)
		{
			$field = $documentService->getFieldTypeObject($documentType, $fieldMap);
			if(!$field)
			{
				$properties[$fieldId] = $currentValues[$fieldMap['FieldName']];
				continue;
			}

			$properties[$fieldId] = $field->extractValue(
				['Field' => $fieldMap['FieldName']],
				$currentValues,
				$errors
			);
		}

		if (array_key_exists("RequestedInformation", $properties) && is_array($properties["RequestedInformation"]))
		{
			$requestedInformation = [];
			foreach ($properties["RequestedInformation"] as $fieldValue)
			{
				if ($fieldValue["Name"] == '')
				{
					continue;
				}

				$fieldValue['Required'] = CBPHelper::getBool($fieldValue['Required']);
				$fieldValue['Multiple'] = CBPHelper::getBool($fieldValue['Multiple']);

				$requestedInformation[] = $fieldValue;
			}
			$properties['RequestedInformation'] = $requestedInformation;
		}

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($workflowTemplate, $activityName);
		$currentActivity["Properties"] = $properties;

		if (is_array($properties["RequestedInformation"]))
		{
			foreach ($properties["RequestedInformation"] as $variable)
			{
				$workflowVariables[$variable['Name']] = ['Name' => $variable['Title']] + $variable;
			}
		}

		$errors = static::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
		{
			return false;
		}

		return true;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("Users", $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties["Users"]))
				$arTestProperties["Users"] = array($arTestProperties["Users"]);

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties["Users"] as $userId)
			{
				if (!is_array($userId) && (trim($userId) <> '') || is_array($userId) && (count($userId) > 0))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
			$arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPRIA_ACT_PROP_EMPTY1"));

		if (!array_key_exists("Name", $arTestProperties) || $arTestProperties["Name"] == '')
			$arErrors[] = array("code" => "NotExist", "parameter" => "Name", "message" => GetMessage("BPRIA_ACT_PROP_EMPTY4"));

		if (!array_key_exists("RequestedInformation", $arTestProperties) || !is_array($arTestProperties["RequestedInformation"]) || count($arTestProperties["RequestedInformation"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "RequestedInformation", "message" => GetMessage("BPRIA_ACT_PROP_EMPTY2"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	protected static function getPropertiesDialogMap()
	{
		return [
			"Users" => [
				'Name' => GetMessage('BPRIA_APPROVERS'),
				'FieldName' => "requested_users",
				'Type' => Bizproc\FieldType::USER,
				'Multiple' => true,
				'Required' => true,
			],
			"Name" => [
				'Name' => GetMessage('BPRIA_NAME'),
				'FieldName' => "requested_name",
				'Type' => Bizproc\FieldType::STRING,
				'Required' => true
			],
			"Description" => [
				'Name' => GetMessage('BPRIA_DESCR'),
				'FieldName' => "requested_description",
				'Type' => Bizproc\FieldType::TEXT
			],
			"TaskButtonMessage" => [
				'Name' => GetMessage('BPAR_TASK_BUTTON_MESSAGE'),
				'FieldName' => "task_button_message",
				'Type' => Bizproc\FieldType::STRING,
				'Default' => static::getDefaultLabels()['task_button_message'],
			],
			"ShowComment" => [
				'Name' => GetMessage('BPAR_SHOW_COMMENT'),
				'FieldName' => "show_comment",
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					'Y' => GetMessage('BPSFA_YES'),
					'N' => GetMessage('BPSFA_NO')
				],
				'Default' => 'Y'
			],
			'CommentRequired' => [
				'Name' => GetMessage('BPAR_COMMENT_REQUIRED'),
				'FieldName' => 'comment_required',
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					'Y' => GetMessage('BPSFA_YES'),
					'N' => GetMessage('BPSFA_NO')
				],
				'Default' => 'N'
			],
			"CommentLabelMessage" => [
				'Name' => GetMessage('BPAR_COMMENT_LABEL_MESSAGE'),
				'FieldName' => "comment_label_message",
				'Type' => Bizproc\FieldType::STRING,
				'Default' => static::getDefaultLabels()['comment_label_message']
			],
			"SetStatusMessage" => [
				'Name' => GetMessage("BPSFA_SET_STATUS_MESSAGE"),
				'FieldName' => "set_status_message",
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					'Y' => GetMessage('BPSFA_YES'),
					'N' => GetMessage('BPSFA_NO')
				],
				'Default' => 'Y'
			],
			"StatusMessage" => [
				'Name' => GetMessage("BPSFA_STATUS_MESSAGE"),
				'FieldName' => "status_message",
				'Type' => Bizproc\FieldType::STRING,
				'Default' => static::getDefaultLabels()['status_message']
			],
			"TimeoutDuration" => [
				'Name' => GetMessage("BPSFA_TIMEOUT_DURATION") . ":\n" . GetMessage('BPSFA_TIMEOUT_DURATION_HINT'),
				'FieldName' => "timeout_duration",
				'Type' => Bizproc\FieldType::INT,
			],
			"TimeoutDurationType" => [
				'Name' => '',
				'FieldName' => "timeout_duration_type",
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					's' => GetMessage("BPSFA_TIME_S"),
					'm' => GetMessage("BPSFA_TIME_M"),
					'h' => GetMessage("BPSFA_TIME_H"),
					'd' => GetMessage("BPSFA_TIME_D")
				],
				'Default' => 's',
				'Required' => true
			],
			'AccessControl' => [
				'Name' => GetMessage("BPRIA_ACCESS_CONTROL"),
				'FieldName' => 'access_control',
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					'Y' => GetMessage('BPSFA_YES'),
					'N' => GetMessage('BPSFA_NO')
				],
				'Default' => 'N'
			],
			"DelegationType" => [
				'Name' => GetMessage("BPSFA_DELEGATION_TYPE"),
				'FieldName' => "delegation_type",
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => CBPTaskDelegationType::getSelectList(),
				'Default' => CBPTaskDelegationType::Subordinate
			],
			"RequestedInformation" => [
				'FieldName' => 'requested_information',
				'Settings' => [
					'Hidden' => true
				]
			],
			"OverdueDate" => [
				'FieldName' => "requested_overdue_date",
				'Settings' => [
					'Hidden' => true
				]
			]
		];
	}

	public function collectUsages()
	{
		$usages = parent::collectUsages();
		if (is_array($this->arProperties["RequestedInformation"]))
		{
			foreach ($this->arProperties["RequestedInformation"] as $v)
			{
				$usages[] = $this->getObjectSourceType('Variable', $v["Name"]);
			}
		}
		return $usages;
	}
}