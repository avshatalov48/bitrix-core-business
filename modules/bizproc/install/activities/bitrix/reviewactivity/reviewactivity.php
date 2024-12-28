<?php

use Bitrix\Bizproc\Result\RenderedResult;
use Bitrix\Bizproc\Result\ResultDto;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\Mixins\ErrorHandling;
use Bitrix\Main\Type\DateTime;

class CBPReviewActivity extends CBPCompositeActivity implements IBPEventActivity, IBPActivityExternalEventListener
{
	use ErrorHandling;

	private $taskId = 0;
	private $taskUsers = array();
	private $subscriptionId = 0;

	private $isInEventActivityMode = false;
	private $taskStatus = false;

	private $arReviewResults = array();
	private $arReviewOriginalResults = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Users" => null,
			"ApproveType" => "all",
			"OverdueDate" => null,
			"Name" => null,
			"Description" => null,
			"Parameters" => null,
			"ReviewedCount" => 0,
			"TotalCount" => 0,
			"StatusMessage" => "",
			"SetStatusMessage" => "Y",
			"TaskButtonMessage" => "",
			"TimeoutDuration" => 0,
			"TimeoutDurationType" => "s",
			"IsTimeout" => 0,
			"TaskId" => 0,
			"Comments" => "",
			"CommentLabelMessage" => "",
			"ShowComment" => "Y",
			'CommentRequired' => 'N',
			'AccessControl' => 'N',
			"LastReviewer" => null,
			"LastReviewerComment" => '',
			'DelegationType' => 0,
		);

		$this->SetPropertiesTypes(array(
			'TaskId' => ['Type' => 'int'],
			'Comments' => array(
				'Type' => 'string',
			),
			'ReviewedCount' => array(
				'Type' => 'int',
			),
			'TotalCount' => array(
				'Type' => 'int',
			),
			'IsTimeout' => array(
				'Type' => 'int',
			),
			'LastReviewer' => array(
				'Type' => 'user',
			),
			'LastReviewerComment' => array(
				'Type' => 'string',
			),
		));
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->TaskId = 0;
		$this->arReviewResults = array();
		$this->arReviewOriginalResults = array();
		$this->ReviewedCount = 0;
		$this->Comments = '';
		$this->IsTimeout = 0;
		$this->LastReviewer = null;
		$this->LastReviewerComment = '';
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

		$this->WriteToTrackingService(
			GetMessage("BPAR_ACT_TRACK3", ['#VAL#' => "{=user:".implode("}, {=user:", $arUsersTmp)."}"])
		);

		$arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);

		$arParameters = $this->Parameters;
		if (!is_array($arParameters))
			$arParameters = array($arParameters);
		$arParameters["DOCUMENT_ID"] = $documentId;
		$arParameters["DOCUMENT_URL"] = $documentService->GetDocumentAdminPage($documentId);
		$arParameters["TaskButtonMessage"] = $this->IsPropertyExists("TaskButtonMessage") ? $this->TaskButtonMessage : GetMessage("BPAR_ACT_BUTTON2");
		if ($arParameters["TaskButtonMessage"] == '')
			$arParameters["TaskButtonMessage"] = GetMessage("BPAR_ACT_BUTTON2");
		$arParameters["CommentLabelMessage"] = $this->IsPropertyExists("CommentLabelMessage") ? $this->CommentLabelMessage : GetMessage("BPAR_ACT_COMMENT");
		if ($arParameters["CommentLabelMessage"] == '')
			$arParameters["CommentLabelMessage"] = GetMessage("BPAR_ACT_COMMENT");
		$arParameters["ShowComment"] = $this->IsPropertyExists("ShowComment") ? $this->ShowComment : "Y";
		if ($arParameters["ShowComment"] != "Y" && $arParameters["ShowComment"] != "N")
			$arParameters["ShowComment"] = "Y";
		if ($this->isPropertyExists('ApproveType'))
		{
			$arParameters['ApproveType'] = $this->ApproveType;
		}
		else
		{
			$arParameters['ApproveType'] = 'all';
		}

		$arParameters["CommentRequired"] = $this->IsPropertyExists("CommentRequired") ? $this->CommentRequired : "N";
		$arParameters["AccessControl"] = $this->IsPropertyExists("AccessControl") && $this->AccessControl == 'Y' ? 'Y' : 'N';

		$overdueDate = $this->OverdueDate;
		$timeoutDuration = $this->calculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$overdueDate = DateTime::createFromTimestamp(
				time() + max($timeoutDuration, CBPSchedulerService::getDelayMinLimit())
			);
		}

		/** @var CBPTaskService $taskService */
		$taskService = $this->workflow->GetService("TaskService");
		$this->taskId = $taskService->CreateTask(
			array(
				"USERS" => $arUsers,
				"WORKFLOW_ID" => $this->GetWorkflowInstanceId(),
				"ACTIVITY" => "ReviewActivity",
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
			$message = (!empty($this->StatusMessage) && is_string($this->StatusMessage))
				? $this->StatusMessage
				: GetMessage("BPAR_ACT_INFO")
			;
			$this->SetStatusTitle(str_replace(
				array("#PERC#", "#PERCENT#", "#REV#", "#REVIEWED#", "#TOT#", "#TOTAL#", "#REVIEWERS#"),
				array(0, 0, 0, 0, $totalCount, $totalCount, ""),
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

		if ($this->subscriptionId > 0)
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

	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->taskId > 0)
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
			return;

		if (($arEventParameters['SchedulerService'] ?? null) === 'OnAgent')
		{
			$this->IsTimeout = 1;
			$this->taskStatus = CBPTaskStatus::Timeout;
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);

			return;
		}

		if (!array_key_exists("USER_ID", $arEventParameters) || intval($arEventParameters["USER_ID"]) <= 0)
			return;

		if (empty($arEventParameters["REAL_USER_ID"]))
			$arEventParameters["REAL_USER_ID"] = $arEventParameters["USER_ID"];

		$arUsers = $this->taskUsers;
		if (empty($arUsers)) //compatibility
			$arUsers = CBPHelper::ExtractUsers($this->Users, $this->GetDocumentId(), false);

		$arEventParameters["USER_ID"] = intval($arEventParameters["USER_ID"]);
		$arEventParameters["REAL_USER_ID"] = intval($arEventParameters["REAL_USER_ID"]);
		if (!in_array($arEventParameters["USER_ID"], $arUsers))
			return;

		if ($this->IsPropertyExists("LastReviewer"))
			$this->LastReviewer = "user_".$arEventParameters["REAL_USER_ID"];
		if ($this->IsPropertyExists("LastReviewerComment"))
			$this->LastReviewerComment = (string)$arEventParameters["COMMENT"];

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $arEventParameters["REAL_USER_ID"], CBPTaskUserStatus::Ok);

		$dbUser = CUser::GetById($arEventParameters["REAL_USER_ID"]);
		if ($arUser = $dbUser->Fetch())
			$this->Comments = $this->Comments.
				CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), $arUser)." (".$arUser["LOGIN"].")".
				(($arEventParameters["COMMENT"] <> '') ? ": " : "").$arEventParameters["COMMENT"]."\n";

		$this->WriteToTrackingService(
				str_replace(
					array("#PERSON#", "#COMMENT#"),
					array("{=user:user_".$arEventParameters["REAL_USER_ID"]."}", ($arEventParameters["COMMENT"] <> '' ? ": ".$arEventParameters["COMMENT"] : "")),
					GetMessage("BPAR_ACT_REVIEW_TRACK")
				),
				$arEventParameters["REAL_USER_ID"]
			);

		$result = "Continue";

		$this->arReviewOriginalResults[] = $arEventParameters["USER_ID"];
		$this->arReviewResults[] = $arEventParameters["REAL_USER_ID"];
		$this->ReviewedCount = count($this->arReviewResults);

		if ($this->IsPropertyExists("ApproveType") && $this->ApproveType == "any")
		{
			$result = "Finish";
		}
		else
		{
			$allAproved = true;
			foreach ($arUsers as $userId)
			{
				if (!in_array($userId, $this->arReviewOriginalResults))
					$allAproved = false;
			}

			if ($allAproved)
				$result = "Finish";
		}

		if (!$this->IsPropertyExists("SetStatusMessage") || $this->SetStatusMessage == "Y")
		{
			$messageTemplate = \CBPHelper::stringify($this->StatusMessage);
			if (!$messageTemplate)
			{
				$messageTemplate = GetMessage("BPAR_ACT_INFO");
			}
			$votedPercent = (int)($this->ReviewedCount / $this->TotalCount * 100);
			$votedCount = $this->ReviewedCount;
			$totalCount = $this->TotalCount;

			$reviewers = "";
			if (mb_strpos($messageTemplate, "#REVIEWERS#") !== false)
				$reviewers = $this->GetReviewersNames();
			if ($reviewers == "")
				$reviewers = GetMessage("BPAA_ACT_APPROVERS_NONE");

			$this->SetStatusTitle(str_replace(
				array("#PERC#", "#PERCENT#", "#REV#", "#REVIEWED#", "#TOT#", "#TOTAL#", "#REVIEWERS#"),
				array($votedPercent, $votedPercent, $votedCount, $votedCount, $totalCount, $totalCount, $reviewers),
				$messageTemplate
			));
		}

		if ($result != "Continue")
		{
			$this->WriteToTrackingService(GetMessage("BPAR_ACT_REVIEWED"));

			$result = $this->getResult();
			if ($result)
			{
				$this->fixResult($result);
			}

			$this->taskStatus = CBPTaskStatus::CompleteOk;
			$this->Unsubscribe($this);
			$this->workflow->CloseActivity($this);
		}
	}

	protected function getResult(): ResultDto|null
	{
		$usages = $this->collectPropertyUsages('Description');

		if (!empty($usages))
		{
			$documentService = $this->workflow->GetService('DocumentService');
			$type = $this->getDocumentType();
			$id = $this->getDocumentId();

			$fileFields = array_filter(
				$documentService->getDocumentFields($type),
				function($field)
				{
					return ($field['Type'] === 'file');
				},
			);

			if (!empty($fileFields))
			{
				foreach ($usages as $usage)
				{
					if ($usage[0] === 'Document' && isset($fileFields[$usage[1]]))
					{
						$document = $documentService->getDocument($id, $type);
						$resultValue = [
							'DOCUMENT_ID' => $id,
							'DOCUMENT_TYPE' => $type,
							'DOCUMENT_FIELD_TYPE' => $fileFields[$usage[1]],
							'DOCUMENT_FIELD_VALUE' => $document[$usage[1]] ?? null,
							'USERS' => $this->arReviewResults ?? [],
						];

						return new ResultDto(get_class($this), $resultValue);
					}
				}
			}
		}

		return null;
	}

	public static function renderResult(array $result, string $workflowId, int $userId): RenderedResult
	{
		if (!self::checkResultViewRights($result, $workflowId, $userId))
		{

			return RenderedResult::makeNoRights();
		}

		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		$value = $documentService->getFieldInputValuePrintable(
			$result['DOCUMENT_TYPE'],
			$result['DOCUMENT_FIELD_TYPE'],
			$result['DOCUMENT_FIELD_VALUE'],
		);

		if (is_string($value))
		{

			return new RenderedResult($value, RenderedResult::BB_CODE_RESULT);
		}

		return RenderedResult::makeNoResult();
	}

	private function GetReviewersNames()
	{
		$result = "";

		if (count($this->arReviewResults) > 0)
		{
			$dbUsers = CUser::GetList(
				"",
				"",
				array("ID" => implode('|', $this->arReviewResults)),
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

	protected function OnEvent(CBPActivity $sender)
	{
		$sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->CloseActivity($this);
	}

	public static function ShowTaskForm($arTask, $userId, $userName = "")
	{
		$form = '';

		if (!array_key_exists("ShowComment", $arTask["PARAMETERS"]) || ($arTask["PARAMETERS"]["ShowComment"] != "N"))
		{
			$required = '';
			if (isset($arTask['PARAMETERS']['CommentRequired']) && $arTask['PARAMETERS']['CommentRequired'] == 'Y')
			{
				$required = '<span style="color: red">*</span>';
			}

			$form .=
				'<tr><td valign="top" width="40%" align="right" class="bizproc-field-name">'
					.($arTask["PARAMETERS"]["CommentLabelMessage"] <> '' ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPAR_ACT_COMMENT"))
					.$required
				.':</td>'.
				'<td valign="top" width="60%" class="bizproc-field-value">'.
				'<textarea rows="3" cols="50" name="task_comment"></textarea>'.
				'</td></tr>';
		}

		$buttons = '<input type="submit" name="review" value="'.($arTask["PARAMETERS"]["TaskButtonMessage"] <> '' ? $arTask["PARAMETERS"]["TaskButtonMessage"] : GetMessage("BPAR_ACT_BUTTON2")).'"/>';

		return array($form, $buttons);
	}

	public static function getTaskControls($task)
	{
		$controls = [
			'BUTTONS' => [
				[
					'TYPE' => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::Ok,
					'NAME' => 'review',
					'VALUE' => 'Y',
					'TEXT' => $task["PARAMETERS"]["TaskButtonMessage"] ?: GetMessage("BPAR_ACT_BUTTON2"),
				]
			]
		];

		if (($task["PARAMETERS"]["ShowComment"] ?? 'N') !== "N")
		{
			$controls['FIELDS'] = [
				[
					'Id' => 'task_comment',
					'Type' => 'text',
					'Name' => $task["PARAMETERS"]["CommentLabelMessage"] ?: GetMessage("BPAR_ACT_COMMENT"),
					'Required' => (($task['PARAMETERS']['CommentRequired'] ?? 'N') === 'Y'),
				],
			];
		}

		return $controls;
	}

	public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = "", $realUserId = null)
	{
		self::$errors = new ErrorCollection();
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
				"COMMENT" => trim($arRequest['fields']['task_comment'] ?? ($arRequest['task_comment'] ?? '')),
			);

			if (isset($arRequest['INLINE_USER_STATUS']) && $arRequest['INLINE_USER_STATUS'] != CBPTaskUserStatus::Ok)
				throw new CBPNotSupportedException(GetMessage("BPAA_ACT_NO_ACTION"));


			if (
				isset($arTask['PARAMETERS']['ShowComment'])
				&& $arTask['PARAMETERS']['ShowComment'] === 'Y'
				&& isset($arTask['PARAMETERS']['CommentRequired'])
				&& empty($arEventParameters['COMMENT'])
				&& $arTask['PARAMETERS']['CommentRequired'] === 'Y'
			)
			{
				$label = $arTask["PARAMETERS"]["CommentLabelMessage"] <> '' ? $arTask["PARAMETERS"]["CommentLabelMessage"] : GetMessage("BPAR_ACT_COMMENT");
				self::$errors->setError(
					new Error(
						Loc::getMessage('BPAA_ACT_COMMENT_ERROR', ['#COMMENT_LABEL#' => $label]),
						0,
						'task_comment',
					)
				);
			}

			if (static::hasErrors())
			{
				foreach (static::getErrors() as $error)
				{
					$arErrors[] = [
						'code' => $error->getCode(),
						'message' =>  $error->getMessage(),
						'file' => null,
						'customData' => $error->getCustomData(),
					];
				}

				return false;
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
		$errors = [];

		if (CBPHelper::isEmptyValue($arTestProperties['Users'] ?? null))
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "Users",
				"message" => GetMessage("BPAR_ACT_PROP_EMPTY1")
			];
		}

		if (empty($arTestProperties["Name"]))
		{
			$errors[] = [
				"code" => "NotExist",
				"parameter" => "Name",
				"message" => GetMessage("BPAR_ACT_PROP_EMPTY4")
			];
		}

		return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
	}

	private function calculateTimeoutDuration()
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

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"Users" => "review_users",
			"ApproveType" => "approve_type",
			"OverdueDate" => "review_overdue_date",
			"Name" => "review_name",
			"Description" => "review_description",
			"Parameters" => "review_parameters",
			"StatusMessage" => "status_message",
			"SetStatusMessage" => "set_status_message",
			"TaskButtonMessage" => "task_button_message",
			"CommentLabelMessage" => "comment_label_message",
			"ShowComment" => "show_comment",
			'CommentRequired' => 'comment_required',
			"TimeoutDuration" => "timeout_duration",
			"TimeoutDurationType" => "timeout_duration_type",
			"AccessControl" => "access_control",
			"DelegationType" => "delegation_type",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
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
		}

		if ($arCurrentValues['status_message'] == '')
			$arCurrentValues['status_message'] = GetMessage("BPAR_ACT_INFO");
		if ($arCurrentValues['comment_label_message'] == '')
			$arCurrentValues['comment_label_message'] = GetMessage("BPAR_ACT_COMMENT");
		if ($arCurrentValues['task_button_message'] == '')
			$arCurrentValues['task_button_message'] = GetMessage("BPAR_ACT_BUTTON2");
		if ($arCurrentValues["timeout_duration_type"] == '')
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

		$arMap = array(
			"review_users" => "Users",
			"approve_type" => "ApproveType",
			"review_overdue_date" => "OverdueDate",
			"review_name" => "Name",
			"review_description" => "Description",
			"review_parameters" => "Parameters",
			"status_message" => "StatusMessage",
			"set_status_message" => "SetStatusMessage",
			"task_button_message" => "TaskButtonMessage",
			"comment_label_message" => "CommentLabelMessage",
			"show_comment" => "ShowComment",
			'comment_required' => 'CommentRequired',
			"timeout_duration" => "TimeoutDuration",
			"timeout_duration_type" => "TimeoutDurationType",
			"access_control" => "AccessControl",
			"delegation_type" => "DelegationType",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "review_users")
			{
				continue;
			}

			$arProperties[$value] = $arCurrentValues[$key] ?? null;
		}

		$arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["review_users"], $documentType, $arErrors);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
