<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPRequestInformationOptionalActivity
	extends CBPCompositeActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	const ACTIVITY = 'RequestInformationOptionalActivity';
	const CONTROLS_PREFIX = 'bprioact_';

	private $taskId = 0;
	private $taskUsers = array();
	private $subscriptionId = 0;
	private $isInEventActivityMode = false;
	private $taskStatus = false;
	private $cancelUsers = array();

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
			'TaskButtonCancelMessage' => '',
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
			'CancelType' => 'any',
			'DelegationType' => 0,
		);

		$this->SetPropertiesTypes(array(
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
		));
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->TaskId = 0;
		$this->Comments = '';
		$this->InfoUser = null;
		$this->IsTimeout = 0;
		$this->cancelUsers = array();
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

		$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPRIOA_ACT_TRACK1")));

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);

		$arParameters = $this->Parameters;
		if (!is_array($arParameters))
			$arParameters = array($arParameters);

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arParameters["DOCUMENT_ID"] = $documentId;
		$arParameters["DOCUMENT_URL"] = $documentService->GetDocumentAdminPage($documentId);
		$arParameters["DOCUMENT_TYPE"] = $this->GetDocumentType();
		$arParameters["FIELD_TYPES"] = $documentService->GetDocumentFieldTypes($arParameters["DOCUMENT_TYPE"]);
		$arParameters["REQUEST"] = array();
		$arParameters["TaskButtonMessage"] = $this->IsPropertyExists("TaskButtonMessage") ? $this->TaskButtonMessage : GetMessage("BPRIOA_ACT_BUTTON1");
		if (strlen($arParameters["TaskButtonMessage"]) <= 0)
			$arParameters["TaskButtonMessage"] = GetMessage("BPRIOA_ACT_BUTTON1");
		$arParameters["TaskButtonCancelMessage"] = $this->IsPropertyExists("TaskButtonCancelMessage") ? $this->TaskButtonCancelMessage : GetMessage("BPRIOA_ACT_BUTTON2");
		if (strlen($arParameters["TaskButtonCancelMessage"]) <= 0)
			$arParameters["TaskButtonCancelMessage"] = GetMessage("BPRIOA_ACT_BUTTON2");
		$arParameters["CommentLabelMessage"] = $this->IsPropertyExists("CommentLabelMessage") ? $this->CommentLabelMessage : GetMessage("BPRIOA_ACT_COMMENT");
		if (strlen($arParameters["CommentLabelMessage"]) <= 0)
			$arParameters["CommentLabelMessage"] = GetMessage("BPRIOA_ACT_COMMENT");
		$arParameters["ShowComment"] = $this->IsPropertyExists("ShowComment") ? $this->ShowComment : "Y";
		if ($arParameters["ShowComment"] != "Y" && $arParameters["ShowComment"] != "N")
			$arParameters["ShowComment"] = "Y";

		$arParameters["CommentRequired"] = $this->IsPropertyExists("CommentRequired") ? $this->CommentRequired : "N";
		$arParameters["AccessControl"] = $this->IsPropertyExists("AccessControl") && $this->AccessControl == 'Y' ? 'Y' : 'N';

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

				$arParameters["REQUEST"][] = $v;
			}
		}

		$overdueDate = $this->OverdueDate;
		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$overdueDate = ConvertTimeStamp(time() + max($timeoutDuration, CBPSchedulerService::getDelayMinLimit()), "FULL");
		}

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
				"PARAMETERS" => $arParameters,
				'DELEGATION_TYPE' => (int)$this->DelegationType,
				'DOCUMENT_NAME' => $documentService->GetDocumentName($documentId)
			)
		);
		$this->TaskId = $this->taskId;
		$this->taskUsers = $arUsers;

		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
		{
			$message = ($this->IsPropertyExists("StatusMessage") && strlen($this->StatusMessage) > 0) ? $this->StatusMessage : GetMessage("BPRIOA_ACT_INFO");
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

		for ($i = count($this->arActivities) - 1; $i >= 0; $i--)
		{
			$activity = $this->arActivities[$i];
			if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			{
				$this->workflow->CancelActivity($activity);
				return CBPActivityExecutionStatus::Canceling;
			}

			if (($activity->executionStatus == CBPActivityExecutionStatus::Canceling)
				|| ($activity->executionStatus == CBPActivityExecutionStatus::Faulting))
				return CBPActivityExecutionStatus::Canceling;

			if ($activity->executionStatus == CBPActivityExecutionStatus::Closed)
				return CBPActivityExecutionStatus::Closed;
		}

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
				$this->ExecuteOnCancel();
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

		$cancel = !empty($eventParameters['CANCEL']);

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $eventParameters["REAL_USER_ID"], $cancel ? CBPTaskUserStatus::Cancel : CBPTaskUserStatus::Ok);

		$this->WriteToTrackingService(
			str_replace(
				array("#PERSON#", "#COMMENT#"),
				array("{=user:user_".$eventParameters["REAL_USER_ID"]."}", (strlen($eventParameters["COMMENT"]) > 0 ? ": ".$eventParameters["COMMENT"] : "")),
				GetMessage($cancel ? 'BPRIOA_ACT_CANCEL_TRACK' : 'BPRIOA_ACT_APPROVE_TRACK')
			),
			$eventParameters["REAL_USER_ID"]
		);

		if ($cancel)
			$this->cancelUsers[] = $eventParameters['USER_ID'];

		if ($cancel && $this->CancelType == 'all')
		{
			foreach ($arUsers as $userId)
			{
				if (!in_array($userId, $this->cancelUsers))
					return;
			}
		}

		if (isset($eventParameters["RESPONCE"]))
		{
			$this->ResponcedInformation = $eventParameters["RESPONCE"];
			$rootActivity->SetVariables($eventParameters["RESPONCE"]);
		}

		$this->taskStatus = $cancel ? CBPTaskStatus::CompleteCancel : CBPTaskStatus::CompleteOk;
		$this->Unsubscribe($this);

		$cancel ? $this->ExecuteOnCancel() : $this->ExecuteOnOk();
	}

	protected function ExecuteOnOk()
	{
		if (count($this->arActivities) <= 0)
		{
			$this->workflow->CloseActivity($this);
			return;
		}

		/** @var CBPActivity $activity */
		$activity = $this->arActivities[0];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($activity);
	}

	protected function ExecuteOnCancel()
	{
		if (count($this->arActivities) <= 1)
		{
			$this->workflow->CloseActivity($this);
			return;
		}

		/** @var CBPActivity $activity */
		$activity = $this->arActivities[1];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($activity);
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

		if ($arTask["PARAMETERS"] && is_array($arTask["PARAMETERS"]) && count($arTask["PARAMETERS"]) > 0
			&& $arTask["PARAMETERS"]["REQUEST"] && is_array($arTask["PARAMETERS"]["REQUEST"]) && count($arTask["PARAMETERS"]["REQUEST"]) > 0)
		{
			foreach ($arTask["PARAMETERS"]["REQUEST"] as $parameter)
			{
				if (strlen($parameter["Name"]) <= 0)
					continue;

				$form .=
					'<tr><td valign="top" width="30%" align="right" class="bizproc-field-name">'.($parameter["Required"] ? '<span class="required">*</span><span class="adm-required-field">'.$parameter["Title"].':</span>' : $parameter["Title"].":")
					.($parameter["Description"]? '<br/><span class="bizproc-field-description">'.$parameter["Description"].'</span>' : '')
					.'</td>'.
					'<td valign="top" width="70%" class="bizproc-field-value">';

				if ($arRequest === null)
					$realValue = $parameter["Default"];
				else
					$realValue = $arRequest[static::CONTROLS_PREFIX.$parameter["Name"]];

				$form .= $documentService->GetFieldInputControl(
					$arTask["PARAMETERS"]["DOCUMENT_TYPE"],
					$parameter,
					array("task_form1", static::CONTROLS_PREFIX.$parameter["Name"]),
					$realValue,
					false,
					true
				);

				$form .= '</td></tr>';
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
			$form .=
				'<tr><td valign="top" width="30%" align="right" class="bizproc-field-name">'
				.(strlen($arTask["PARAMETERS"]["CommentLabelMessage"]) > 0 ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPRIOA_ACT_COMMENT"))
				.$required
				.':</td>'.
				'<td valign="top" width="70%" class="bizproc-field-value">'.
				'<textarea rows="3" cols="50" name="task_comment">'.htmlspecialcharsbx($commentText).'</textarea>'.
				'</td></tr>';
		}

		$buttons =
			'<input type="submit" name="approve" value="'.(strlen($arTask["PARAMETERS"]["TaskButtonMessage"]) > 0 ? $arTask["PARAMETERS"]["TaskButtonMessage"] : GetMessage("BPRIOA_ACT_BUTTON1")).'"/>
			<input type="submit" name="cancel" value="'.(strlen($arTask["PARAMETERS"]["TaskButtonCancelMessage"]) > 0 ? $arTask["PARAMETERS"]["TaskButtonCancelMessage"] : GetMessage("BPRIOA_ACT_BUTTON2")).'"/>';

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
					'TEXT'  => strlen($arTask["PARAMETERS"]["TaskButtonMessage"]) > 0 ? $arTask["PARAMETERS"]["TaskButtonMessage"] : GetMessage("BPAA_ACT_BUTTON1")
				),
				array(
					'TYPE'  => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::Cancel,
					'NAME'  => 'cancel',
					'VALUE' => 'Y',
					'TEXT'  => strlen($arTask["PARAMETERS"]["TaskButtonCancelMessage"]) > 0 ? $arTask["PARAMETERS"]["TaskButtonCancelMessage"] : GetMessage("BPAA_ACT_BUTTON2")
				)
			)
		);
	}
	protected static function getEventParameters($task, $request)
	{
		$result = array(
			'COMMENT' => isset($request['task_comment']) ? trim($request['task_comment']) : ''
		);

		if (empty($request['cancel']))
			$result['RESPONCE'] = static::getTaskResponse($task);
		else
			$result['CANCEL']  = true;

		return $result;
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
					throw new CBPArgumentNullException($parameter["Name"], str_replace("#PARAM#", htmlspecialcharsbx($parameter["Title"]), GetMessage("BPRIOA_ARGUMENT_NULL")));
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
			$label = strlen($arTask["PARAMETERS"]["CommentLabelMessage"]) > 0 ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPAR_ACT_COMMENT");
			throw new CBPArgumentNullException(
				'task_comment',
				GetMessage("BPRIOA_ACT_COMMENT_ERROR", array(
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
				if (!is_array($userId) && (strlen(trim($userId)) > 0) || is_array($userId) && (count($userId) > 0))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
			$arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPRIOA_ACT_PROP_EMPTY1"));

		if (!array_key_exists("Name", $arTestProperties) || strlen($arTestProperties["Name"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "Name", "message" => GetMessage("BPRIOA_ACT_PROP_EMPTY4"));

		if (!array_key_exists("RequestedInformation", $arTestProperties) || !is_array($arTestProperties["RequestedInformation"]) || count($arTestProperties["RequestedInformation"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "RequestedInformation", "message" => GetMessage("BPRIOA_ACT_PROP_EMPTY2"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	private function CalculateTimeoutDuration()
	{
		$timeoutDuration = ($this->IsPropertyExists("TimeoutDuration") ? $this->TimeoutDuration : 0);

		$timeoutDurationType = ($this->IsPropertyExists("TimeoutDurationType") ? $this->TimeoutDurationType : "s");
		$timeoutDurationType = strtolower($timeoutDurationType);
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

		return $timeoutDuration;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null)
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arMap = array(
			"Users" => "requested_users",
			"OverdueDate" => "requested_overdue_date",
			"Name" => "requested_name",
			"Description" => "requested_description",
			"Parameters" => "requested_parameters",
			"RequestedInformation" => "requested_information",
			"TaskButtonMessage" => "task_button_message",
			"TaskButtonCancelMessage" => 'task_button_cancel_message',
			"CommentLabelMessage" => "comment_label_message",
			"ShowComment" => "show_comment",
			'CommentRequired' => 'comment_required',
			"StatusMessage" => "status_message",
			"SetStatusMessage" => "set_status_message",
			'AccessControl' => 'access_control',
			"TimeoutDuration" => "timeout_duration",
			"TimeoutDurationType" => "timeout_duration_type",
			"CancelType" => "cancel_type",
			"DelegationType" => "delegation_type",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "Users")
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						else
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
					}
					else
					{
						$arCurrentValues[$arMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
					$arCurrentValues[$arMap[$k]] = "";
			}
		}

		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);
		unset($arFieldTypes['N:Sequence']);
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		$ar = array();
		$j = -1;
		if (array_key_exists("requested_information", $arCurrentValues) && is_array($arCurrentValues["requested_information"]))
		{
			for ($i = 0, $cnt = count($arCurrentValues["requested_information"]) + 1; $i < $cnt; $i++)
			{
				if (strlen($arCurrentValues["requested_information"][$i]["Name"]) <= 0)
					continue;

				$j++;
				$ar[$j] = $arCurrentValues["requested_information"][$i];
				$ar[$j]["Required"] = ($ar[$j]["Required"] ? "Y" : "N");
				$ar[$j]["Multiple"] = ($ar[$j]["Multiple"] ? "Y" : "N");
			}
		}

		$arCurrentValues["requested_information"] = $ar;
		if (strlen($arCurrentValues['comment_label_message']) <= 0)
			$arCurrentValues['comment_label_message'] = GetMessage("BPRIOA_ACT_COMMENT");
		if (strlen($arCurrentValues['task_button_message']) <= 0)
			$arCurrentValues['task_button_message'] = GetMessage("BPRIOA_ACT_BUTTON1");
		if (strlen($arCurrentValues['task_button_cancel_message']) <= 0)
			$arCurrentValues['task_button_cancel_message'] = GetMessage("BPRIOA_ACT_BUTTON2");
		if (strlen($arCurrentValues['status_message']) <= 0)
			$arCurrentValues['status_message'] = GetMessage("BPRIOA_ACT_INFO");
		if (strlen($arCurrentValues["timeout_duration_type"]) <= 0)
			$arCurrentValues["timeout_duration_type"] = "s";

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFields", $arDocumentFields, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
				"formName" => $formName,
				"popupWindow" => &$popupWindow,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"requested_users" => "Users",
			"requested_overdue_date" => "OverdueDate",
			"requested_name" => "Name",
			"requested_description" => "Description",
			"requested_parameters" => "Parameters",
			"requested_information" => "RequestedInformation",
			"task_button_message" => "TaskButtonMessage",
			"task_button_cancel_message" => 'TaskButtonCancelMessage',
			"comment_label_message" => "CommentLabelMessage",
			"show_comment" => "ShowComment",
			'comment_required' => 'CommentRequired',
			"status_message" => "StatusMessage",
			"set_status_message" => "SetStatusMessage",
			'access_control' => 'AccessControl',
			"timeout_duration" => "TimeoutDuration",
			"timeout_duration_type" => "TimeoutDurationType",
			"cancel_type" => "CancelType",
			"delegation_type" => "DelegationType",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "requested_users")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["requested_users"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$ar = array();
		$j = -1;

		if (array_key_exists("RequestedInformation", $arProperties) && is_array($arProperties["RequestedInformation"]))
		{
			foreach ($arProperties["RequestedInformation"] as $arRI)
			{
				if (strlen($arRI["Name"]) <= 0)
					continue;

				$j++;
				$ar[$j] = $arRI;
				$ar[$j]["Required"] = ($arRI["Required"] == "Y");
				$ar[$j]["Multiple"] = ($arRI["Multiple"] == "Y");
			}
		}

		$arProperties["RequestedInformation"] = $ar;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		if (is_array($arProperties["RequestedInformation"]))
		{
			foreach ($arProperties["RequestedInformation"] as $v)
			{
				$arWorkflowVariables[$v["Name"]] = $v;
				$arWorkflowVariables[$v["Name"]]["Name"] = $v["Title"];
			}
		}

		return true;
	}
}