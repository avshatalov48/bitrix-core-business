<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPApproveActivity
	extends CBPCompositeActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $taskId = 0;
	private $taskUsers = array();
	private $subscriptionId = 0;

	private $isInEventActivityMode = false;
	private $taskStatus = false;

	private $arApproveResults = array();
	private $arApproveOriginalResults = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Users" => null,
			"ApproveType" => "all",
			"Percent" => 100,
			"OverdueDate" => null,
			"Name" => null,
			"Description" => null,
			"Parameters" => null,
			"ApproveMinPercent" => 50,
			"ApproveWaitForAll" => "N",
			"TaskId" => 0,
			"Comments" => "",
			"VotedCount" => 0,
			"TotalCount" => 0,
			"VotedPercent" => 0,
			"ApprovedPercent" => 0,
			"NotApprovedPercent" => 0,
			"ApprovedCount" => 0,
			"NotApprovedCount" => 0,
			"StatusMessage" => "",
			"SetStatusMessage" => "Y",
			"LastApprover" => null,
			"LastApproverComment" => '',
			"Approvers" => "",
			"Rejecters" => "",
			"UserApprovers" => [],
			"UserRejecters" => [],
			"TimeoutDuration" => 0,
			"TimeoutDurationType" => "s",
			"IsTimeout" => 0,
			"TaskButton1Message" => "",
			"TaskButton2Message" => "",
			"CommentLabelMessage" => "",
			"ShowComment" => "Y",
			'CommentRequired' => 'N',
			'AccessControl' => 'N',
			'DelegationType' => 0,
		);

		$this->SetPropertiesTypes(array(
			'TaskId' => ['Type' => 'int'],
			'Comments' => array(
				'Type' => 'string',
			),
			'VotedCount' => array(
				'Type' => 'int',
			),
			'TotalCount' => array(
				'Type' => 'int',
			),
			'VotedPercent' => array(
				'Type' => 'int',
			),
			'ApprovedPercent' => array(
				'Type' => 'int',
			),
			'NotApprovedPercent' => array(
				'Type' => 'int',
			),
			'ApprovedCount' => array(
				'Type' => 'int',
			),
			'NotApprovedCount' => array(
				'Type' => 'int',
			),
			'LastApprover' => array(
				'Type' => 'user',
			),
			'LastApproverComment' => array(
				'Type' => 'string',
			),
			'Approvers' => array(
				'Type' => 'string',
			),
			'Rejecters' => array(
				'Type' => 'string',
			),
			'UserApprovers' => array(
				'Type' => 'user',
				'Multiple' => true
			),
			'UserRejecters' => array(
				'Type' => 'user',
				'Multiple' => true
			),
			'IsTimeout' => array(
				'Type' => 'int',
			),
		));
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

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arUsersTmp = $this->Users;
		if (!is_array($arUsersTmp))
			$arUsersTmp = array($arUsersTmp);

		if ($this->ApproveType == "any")
			$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPAA_ACT_TRACK1")));
		elseif ($this->ApproveType == "all")
			$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPAA_ACT_TRACK2")));
		elseif ($this->ApproveType == "vote")
			$this->WriteToTrackingService(str_replace("#VAL#", "{=user:".implode("}, {=user:", $arUsersTmp)."}", GetMessage("BPAA_ACT_TRACK3")));

		$arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);

		$arParameters = $this->Parameters;
		if (!is_array($arParameters))
			$arParameters = array($arParameters);
		$arParameters["DOCUMENT_ID"] = $documentId;
		$arParameters["DOCUMENT_URL"] = $documentService->GetDocumentAdminPage($documentId);
		$arParameters["TaskButton1Message"] = $this->IsPropertyExists("TaskButton1Message") ? $this->TaskButton1Message : GetMessage("BPAA_ACT_BUTTON1");
		if (strlen($arParameters["TaskButton1Message"]) <= 0)
			$arParameters["TaskButton1Message"] = GetMessage("BPAA_ACT_BUTTON1");
		$arParameters["TaskButton2Message"] = $this->IsPropertyExists("TaskButton2Message") ? $this->TaskButton2Message : GetMessage("BPAA_ACT_BUTTON2");
		if (strlen($arParameters["TaskButton2Message"]) <= 0)
			$arParameters["TaskButton2Message"] = GetMessage("BPAA_ACT_BUTTON2");
		$arParameters["CommentLabelMessage"] = $this->IsPropertyExists("CommentLabelMessage") ? $this->CommentLabelMessage : GetMessage("BPAA_ACT_COMMENT");
		if (strlen($arParameters["CommentLabelMessage"]) <= 0)
			$arParameters["CommentLabelMessage"] = GetMessage("BPAA_ACT_COMMENT");
		$arParameters["ShowComment"] = $this->IsPropertyExists("ShowComment") ? $this->ShowComment : "Y";
		if ($arParameters["ShowComment"] != "Y" && $arParameters["ShowComment"] != "N")
			$arParameters["ShowComment"] = "Y";

		$arParameters["CommentRequired"] = $this->IsPropertyExists("CommentRequired") ? $this->CommentRequired : "N";
		$arParameters["AccessControl"] = $this->IsPropertyExists("AccessControl") && $this->AccessControl == 'Y' ? 'Y' : 'N';

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
				"ACTIVITY" => "ApproveActivity",
				"ACTIVITY_NAME" => $this->name,
				"OVERDUE_DATE" => $overdueDate,
				"NAME" => $this->Name,
				"DESCRIPTION" => $this->Description,
				"PARAMETERS" => $arParameters,
				'IS_INLINE' => $arParameters["ShowComment"] == "Y" ? 'N' : 'Y',
				'DELEGATION_TYPE' => (int)$this->DelegationType,
				'DOCUMENT_NAME' => $documentService->GetDocumentName($documentId)
			)
		);
		$this->TaskId = $this->taskId;
		$this->taskUsers = $arUsers;

		$this->TotalCount = count($arUsers);
		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
		{
			$totalCount = $this->TotalCount;
			$message = ($this->IsPropertyExists("StatusMessage") && strlen($this->StatusMessage) > 0) ? $this->StatusMessage : GetMessage("BPAA_ACT_INFO");
			$this->SetStatusTitle(str_replace(
				array("#PERC#", "#PERCENT#", "#REV#", "#VOTED#", "#TOT#", "#TOTAL#", "#APPROVERS#", "#REJECTERS#"),
				array(0, 0, 0, 0, $totalCount, $totalCount, GetMessage("BPAA_ACT_APPROVERS_NONE"), GetMessage("BPAA_ACT_APPROVERS_NONE")),
				$message
			));
		}

		if ($timeoutDuration > 0)
		{
			/** @var CBPSchedulerService $schedulerService */
			$schedulerService = $this->workflow->GetService("SchedulerService");
			$this->subscriptionId = $schedulerService->SubscribeOnTime($this->workflow->GetInstanceId(), $this->name, time() + $timeoutDuration);
		}

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	private function ReplaceTemplate($str, $ar)
	{
		$str = str_replace("%", "%2", $str);
		foreach ($ar as $key => $val)
		{
			$val = str_replace("%", "%2", $val);
			$val = str_replace("#", "%1", $val);
			$str = str_replace("#".$key."#", $val, $str);
		}
		$str = str_replace("%1", "#", $str);
		$str = str_replace("%2", "%", $str);

		return $str;
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

	protected function ExecuteOnApprove()
	{
		if (count($this->arActivities) <= 0)
		{
			$this->workflow->CloseActivity($this);
			return;
		}

		$this->WriteToTrackingService(GetMessage("BPAA_ACT_APPROVE"));

		$activity = $this->arActivities[0];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($activity);
	}

	protected function ExecuteOnNonApprove()
	{
		if (count($this->arActivities) <= 1)
		{
			$this->workflow->CloseActivity($this);
			return;
		}

		$this->WriteToTrackingService(GetMessage("BPAA_ACT_NONAPPROVE"));

		$activity = $this->arActivities[1];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->ExecuteActivity($activity);
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
			return;

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			if (array_key_exists("SchedulerService", $arEventParameters) && $arEventParameters["SchedulerService"] == "OnAgent")
			{
				$this->IsTimeout = 1;
				$this->taskStatus = CBPTaskStatus::Timeout;
				$this->Unsubscribe($this);
				$this->writeApproversResult();
				$this->ExecuteOnNonApprove();
				return;
			}
		}

		if (!array_key_exists("USER_ID", $arEventParameters) || intval($arEventParameters["USER_ID"]) <= 0)
			return;
		if (!array_key_exists("APPROVE", $arEventParameters))
			return;

		if (empty($arEventParameters["REAL_USER_ID"]))
			$arEventParameters["REAL_USER_ID"] = $arEventParameters["USER_ID"];

		$approve = ($arEventParameters["APPROVE"] ? true : false);

		$arUsers = $this->taskUsers;
		if (empty($arUsers)) //compatibility
			$arUsers = CBPHelper::ExtractUsers($this->Users, $this->GetDocumentId(), false);

		$arEventParameters["USER_ID"] = intval($arEventParameters["USER_ID"]);
		$arEventParameters["REAL_USER_ID"] = intval($arEventParameters["REAL_USER_ID"]);
		if (!in_array($arEventParameters["USER_ID"], $arUsers))
			return;

		if ($this->IsPropertyExists("LastApprover"))
			$this->LastApprover = "user_".$arEventParameters["REAL_USER_ID"];
		if ($this->IsPropertyExists("LastApproverComment"))
			$this->LastApproverComment = (string)$arEventParameters["COMMENT"];

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $arEventParameters["REAL_USER_ID"], $approve? CBPTaskUserStatus::Yes : CBPTaskUserStatus::No);

		$dbUser = CUser::GetById($arEventParameters["REAL_USER_ID"]);
		if($arUser = $dbUser->Fetch())
			$this->Comments = $this->Comments.
				CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), $arUser)." (".$arUser["LOGIN"]."): ".($approve?GetMessage("BPAA_LOG_Y"):GetMessage("BPAA_LOG_N"))."\n".
				(strlen($arEventParameters["COMMENT"]) > 0 ? GetMessage("BPAA_LOG_COMMENTS").": ".$arEventParameters["COMMENT"] : "")."\n";

		$this->WriteToTrackingService(
			str_replace(
				array("#PERSON#", "#COMMENT#"),
				array("{=user:user_".$arEventParameters["REAL_USER_ID"]."}", (strlen($arEventParameters["COMMENT"]) > 0 ? ": ".$arEventParameters["COMMENT"] : "")),
				GetMessage($approve ? "BPAA_ACT_APPROVE_TRACK" : "BPAA_ACT_NONAPPROVE_TRACK")
			),
			$arEventParameters["REAL_USER_ID"]
		);

		$result = "Continue";

		$this->arApproveOriginalResults[$arEventParameters["USER_ID"]] = $approve;
		$this->arApproveResults[$arEventParameters["REAL_USER_ID"]] = $approve;

		if($approve)
			$this->ApprovedCount = $this->ApprovedCount + 1;
		else
			$this->NotApprovedCount = $this->NotApprovedCount + 1;

		$this->VotedCount = count($this->arApproveResults);
		$this->VotedPercent = intval($this->VotedCount/$this->TotalCount*100);
		$this->ApprovedPercent = intval($this->ApprovedCount/$this->TotalCount*100);
		$this->NotApprovedPercent = intval($this->NotApprovedCount/$this->TotalCount*100);

		if ($this->ApproveType == "any")
		{
			$result = ($approve ? "Approve" : "NonApprove");
		}
		elseif ($this->ApproveType == "all")
		{
			if (!$approve)
			{
				$result = "NonApprove";
			}
			else
			{
				$allAproved = true;
				foreach ($arUsers as $userId)
				{
					if (!isset($this->arApproveOriginalResults[$userId]))
						$allAproved = false;
				}

				if ($allAproved)
					$result = "Approve";
			}
		}
		elseif ($this->ApproveType == "vote")
		{
			if($this->ApproveWaitForAll == "Y")
			{
				if($this->VotedPercent==100)
				{
					if ($this->ApprovedPercent > $this->ApproveMinPercent || $this->ApprovedPercent == 100 && $this->ApproveMinPercent == 100)
						$result = "Approve";
					else
						$result = "NonApprove";
				}
			}
			else
			{
				$noneApprovedPercent = ($this->VotedCount-$this->ApprovedCount)/$this->TotalCount*100;
				if ($this->ApprovedPercent > $this->ApproveMinPercent || $this->ApprovedPercent == 100 && $this->ApproveMinPercent == 100)
					$result = "Approve";
				elseif($noneApprovedPercent > 0 && $noneApprovedPercent >= 100 - $this->ApproveMinPercent)
					$result = "NonApprove";
			}
		}

		$approvers = "";
		$rejecters = "";
		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
		{
			$messageTemplate = ($this->IsPropertyExists("StatusMessage") && strlen($this->StatusMessage) > 0) ? $this->StatusMessage : GetMessage("BPAA_ACT_INFO");
			$votedPercent = $this->VotedPercent;
			$votedCount = $this->VotedCount;
			$totalCount = $this->TotalCount;

			if (strpos($messageTemplate, "#REJECTERS#") !== false)
				$rejecters = $this->GetApproversNames(false);
			if (strpos($messageTemplate, "#APPROVERS#") !== false)
				$approvers = $this->GetApproversNames(true);

			$approversTmp = $approvers;
			$rejectersTmp = $rejecters;
			if ($approversTmp == "")
				$approversTmp = GetMessage("BPAA_ACT_APPROVERS_NONE");
			if ($rejectersTmp == "")
				$rejectersTmp = GetMessage("BPAA_ACT_APPROVERS_NONE");

			$this->SetStatusTitle(str_replace(
				array("#PERC#", "#PERCENT#", "#REV#", "#VOTED#", "#TOT#", "#TOTAL#", "#APPROVERS#", "#REJECTERS#"),
				array($votedPercent, $votedPercent, $votedCount, $votedCount, $totalCount, $totalCount, $approversTmp, $rejectersTmp),
				$messageTemplate
			));
		}

		if ($result != "Continue")
		{
			$this->taskStatus = $result == "Approve"? CBPTaskStatus::CompleteYes : CBPTaskStatus::CompleteNo;
			$this->Unsubscribe($this);
			$this->writeApproversResult();

			if ($result == "Approve")
				$this->ExecuteOnApprove();
			else
				$this->ExecuteOnNonApprove();
		}
	}

	private function GetApproversNames($b)
	{
		$result = "";

		$ar = array();
		foreach ($this->arApproveResults as $k => $v)
		{
			if ($b && $v || !$b && !$v)
				$ar[] = $k;
		}

		if (count($ar) > 0)
		{
			$dbUsers = CUser::GetList(
				($b = ""),
				($o = ""),
				array("ID" => implode('|', $ar)),
				array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE'))
			);
			while ($arUser = $dbUsers->Fetch())
			{
				if ($result != "")
					$result .= ", ";
				$result .= CUser::FormatName(
						COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID),
						$arUser)." (".$arUser["LOGIN"].")";
			}
		}

		return $result;
	}

	private function writeApproversResult()
	{
		$this->Rejecters = $this->GetApproversNames(false);
		$this->Approvers = $this->GetApproversNames(true);

		$approvers = $rejecters = [];

		foreach ($this->arApproveResults as $userId => $vote)
		{
			$user = 'user_'.$userId;

			if ($vote)
			{
				$approvers[] = $user;
			}
			else
			{
				$rejecters[] = $user;
			}
		}
		$this->UserApprovers = $approvers;
		$this->UserRejecters = $rejecters;
	}

	protected function OnEvent(CBPActivity $sender)
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->TaskId = 0;
		$this->arApproveResults = array();
		$this->arApproveOriginalResults = array();
		$this->ApprovedCount = 0;
		$this->NotApprovedCount = 0;

		$this->VotedCount = 0;
		$this->VotedPercent = 0;
		$this->ApprovedPercent = 0;
		$this->NotApprovedPercent = 0;
		$this->Comments = '';
		$this->IsTimeout = 0;
		$this->Approvers = '';
		$this->Rejecters = '';
		$this->UserApprovers = [];
		$this->UserRejecters = [];
		$this->LastApprover = null;
		$this->LastApproverComment = '';
	}

	public static function ShowTaskForm($arTask, $userId, $userName = "")
	{
		$form = '';

		if (!array_key_exists("ShowComment", $arTask["PARAMETERS"]) || ($arTask["PARAMETERS"]["ShowComment"] != "N"))
		{
			$required = '';
			if (isset($arTask['PARAMETERS']['CommentRequired']))
			{
				switch ($arTask['PARAMETERS']['CommentRequired'])
				{
					case 'Y':
						$required = '<span>*</span>';
						break;
					case 'YA':
						$required = '<span style="color: green;">*</span>';
						break;
					case 'YR':
						$required = '<span style="color: red">*</span>';
						break;
					default:
						break;
				}
			}

			$form .=
				'<tr><td valign="top" width="40%" align="right" class="bizproc-field-name">'
					.(strlen($arTask["PARAMETERS"]["CommentLabelMessage"]) > 0 ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPAA_ACT_COMMENT"))
					.$required
				.':</td>'.
				'<td valign="top" width="60%" class="bizproc-field-value">'.
				'<textarea rows="3" cols="50" name="task_comment"></textarea>'.
				'</td></tr>';
		}

		$buttons =
			'<input type="submit" name="approve" value="'.(strlen($arTask["PARAMETERS"]["TaskButton1Message"]) > 0 ? $arTask["PARAMETERS"]["TaskButton1Message"] : GetMessage("BPAA_ACT_BUTTON1")).'"/>'.
			'<input type="submit" name="nonapprove" value="'.(strlen($arTask["PARAMETERS"]["TaskButton2Message"]) > 0 ? $arTask["PARAMETERS"]["TaskButton2Message"] : GetMessage("BPAA_ACT_BUTTON2")).'"/>';

		return array($form, $buttons);
	}

	public static function getTaskControls($arTask)
	{
		return array(
			'BUTTONS' => array(
				array(
					'TYPE'  => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::Yes,
					'NAME'  => 'approve',
					'VALUE' => 'Y',
					'TEXT'  => strlen($arTask["PARAMETERS"]["TaskButton1Message"]) > 0 ? $arTask["PARAMETERS"]["TaskButton1Message"] : GetMessage("BPAA_ACT_BUTTON1")
				),
				array(
					'TYPE'  => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::No,
					'NAME'  => 'nonapprove',
					'VALUE' => 'Y',
					'TEXT'  => strlen($arTask["PARAMETERS"]["TaskButton2Message"]) > 0 ? $arTask["PARAMETERS"]["TaskButton2Message"] : GetMessage("BPAA_ACT_BUTTON2")
				)
			)
		);
	}

	public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = "", $realUserId = null)
	{
		$arErrors = array();

		try
		{
			$userId = intval($userId);
			if ($userId <= 0)
				throw new CBPArgumentNullException("userId");

			$arEventParameters = array(
				"USER_ID" => $userId,
				"REAL_USER_ID" => $realUserId,
				"USER_NAME" => $userName,
				"COMMENT" => isset($arRequest["task_comment"]) ? trim($arRequest["task_comment"]) : '',
			);

			if (isset($arRequest['approve']) && strlen($arRequest["approve"]) > 0
				|| isset($arRequest['INLINE_USER_STATUS']) && $arRequest['INLINE_USER_STATUS'] == CBPTaskUserStatus::Yes)
				$arEventParameters["APPROVE"] = true;
			elseif (isset($arRequest['nonapprove']) && strlen($arRequest["nonapprove"]) > 0
				|| isset($arRequest['INLINE_USER_STATUS']) && $arRequest['INLINE_USER_STATUS'] == CBPTaskUserStatus::No)
				$arEventParameters["APPROVE"] = false;
			else
				throw new CBPNotSupportedException(GetMessage("BPAA_ACT_NO_ACTION"));

			if (
				isset($arTask['PARAMETERS']['ShowComment'])
				&& $arTask['PARAMETERS']['ShowComment'] === 'Y'
				&& isset($arTask['PARAMETERS']['CommentRequired'])
				&& empty($arEventParameters['COMMENT'])
				&&
				($arTask['PARAMETERS']['CommentRequired'] === 'Y'
					|| $arTask['PARAMETERS']['CommentRequired'] === 'YA' && $arEventParameters["APPROVE"]
					|| $arTask['PARAMETERS']['CommentRequired'] === 'YR' && !$arEventParameters["APPROVE"]
				)
			)
			{
				$label = strlen($arTask["PARAMETERS"]["CommentLabelMessage"]) > 0 ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPAA_ACT_COMMENT");
				throw new CBPArgumentNullException(
					'task_comment',
					GetMessage("BPAA_ACT_COMMENT_ERROR", array(
						'#COMMENT_LABEL#' => $label
					))
				);
			}

			CBPRuntime::SendExternalEvent($arTask["WORKFLOW_ID"], $arTask["ACTIVITY_NAME"], $arEventParameters);

			return true;
		}
		catch (Exception $e)
		{
			$arErrors[] = array(
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
			$arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => GetMessage("BPAA_ACT_PROP_EMPTY1"));

		if (!array_key_exists("ApproveType", $arTestProperties))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "ApproveType", "message" => GetMessage("BPAA_ACT_PROP_EMPTY2"));
		}
		else
		{
			if (!in_array($arTestProperties["ApproveType"], array("any", "all", "vote")))
				$arErrors[] = array("code" => "NotInRange", "parameter" => "ApproveType", "message" => GetMessage("BPAA_ACT_PROP_EMPTY3"));
		}

		if (!array_key_exists("Name", $arTestProperties) || strlen($arTestProperties["Name"]) <= 0)
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "Name", "message" => GetMessage("BPAA_ACT_PROP_EMPTY4"));
		}

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

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"Users" => "approve_users",
			"ApproveType" => "approve_type",
			"ApproveMinPercent" => "approve_percent",
			"OverdueDate" => "approve_overdue_date",
			"Name" => "approve_name",
			"Description" => "approve_description",
			"Parameters" => "approve_parameters",
			"ApproveWaitForAll" => "approve_wait",
			"StatusMessage" => "status_message",
			"SetStatusMessage" => "set_status_message",
			"TimeoutDuration" => "timeout_duration",
			"TimeoutDurationType" => "timeout_duration_type",
			"TaskButton1Message" => "task_button1_message",
			"TaskButton2Message" => "task_button2_message",
			"CommentLabelMessage" => "comment_label_message",
			"ShowComment" => "show_comment",
			'CommentRequired' => 'comment_required',
			"AccessControl" => "access_control",
			"DelegationType" => "delegation_type",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = Array();
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "Users")
						{
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						}
						elseif ($k == "TimeoutDuration")
						{
							$arCurrentValues["timeout_duration"] = $arCurrentActivity["Properties"]["TimeoutDuration"];
							if (!CBPActivity::isExpression($arCurrentValues["timeout_duration"])
								&& !array_key_exists("TimeoutDurationType", $arCurrentActivity["Properties"]))
							{
								$arCurrentValues["timeout_duration"] = intval($arCurrentValues["timeout_duration"]);
								$arCurrentValues["timeout_duration_type"] = "s";
								if ($arCurrentValues["timeout_duration"] % (3600 * 24) == 0)
								{
									$arCurrentValues["timeout_duration"] = $arCurrentValues["timeout_duration"] / (3600 * 24);
									$arCurrentValues["timeout_duration_type"] = "d";
								}
								elseif ($arCurrentValues["timeout_duration"] % 3600 == 0)
								{
									$arCurrentValues["timeout_duration"] = $arCurrentValues["timeout_duration"] / 3600;
									$arCurrentValues["timeout_duration_type"] = "h";
								}
								elseif ($arCurrentValues["timeout_duration"] % 60 == 0)
								{
									$arCurrentValues["timeout_duration"] = $arCurrentValues["timeout_duration"] / 60;
									$arCurrentValues["timeout_duration_type"] = "m";
								}
							}
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
						}
					}
					else
					{
						if (!is_array($arCurrentValues) || !array_key_exists($arMap[$k], $arCurrentValues))
							$arCurrentValues[$arMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
					$arCurrentValues[$arMap[$k]] = "";
			}

			if(strlen($arCurrentValues["approve_wait"])<=0)
				$arCurrentValues["approve_wait"] = "N";

			if(strlen($arCurrentValues["approve_percent"])<=0)
				$arCurrentValues["approve_percent"] = "50";
		}

		if (strlen($arCurrentValues['status_message']) <= 0)
			$arCurrentValues['status_message'] = GetMessage("BPAA_ACT_INFO");
		if (strlen($arCurrentValues['task_button1_message']) <= 0)
			$arCurrentValues['task_button1_message'] = GetMessage("BPAA_ACT_BUTTON1");
		if (strlen($arCurrentValues['task_button2_message']) <= 0)
			$arCurrentValues['task_button2_message'] = GetMessage("BPAA_ACT_BUTTON2");
		if (strlen($arCurrentValues['comment_label_message']) <= 0)
			$arCurrentValues['comment_label_message'] = GetMessage("BPAA_ACT_COMMENT");
		if (strlen($arCurrentValues["timeout_duration_type"]) <= 0)
			$arCurrentValues["timeout_duration_type"] = "s";

		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arDocumentFields" => $arDocumentFields,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"approve_users" => "Users",
			"approve_type" => "ApproveType",
			"approve_overdue_date" => "OverdueDate",
			"approve_percent" => "ApproveMinPercent",
			"approve_wait" => "ApproveWaitForAll",
			"approve_name" => "Name",
			"approve_description" => "Description",
			"approve_parameters" => "Parameters",
			"status_message" => "StatusMessage",
			"set_status_message" => "SetStatusMessage",
			"timeout_duration" => "TimeoutDuration",
			"timeout_duration_type" => "TimeoutDurationType",
			"task_button1_message" => "TaskButton1Message",
			"task_button2_message" => "TaskButton2Message",
			"comment_label_message" => "CommentLabelMessage",
			"show_comment" => "ShowComment",
			'comment_required' => 'CommentRequired',
			"access_control" => "AccessControl",
			"delegation_type" => "DelegationType",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "approve_users")
				continue;

			if(strlen($arCurrentValues[$key."_X"])>0)
				$arProperties[$value] = $arCurrentValues[$key."_X"];
			else
				$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["approve_users"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}