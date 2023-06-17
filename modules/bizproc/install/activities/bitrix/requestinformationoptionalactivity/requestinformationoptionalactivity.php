<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Bizproc;

$runtime = CBPRuntime::GetRuntime();
$runtime->IncludeActivityFile('RequestInformationActivity');

class CBPRequestInformationOptionalActivity extends CBPRequestInformationActivity
{
	const ACTIVITY = 'RequestInformationOptionalActivity';
	const CONTROLS_PREFIX = 'bprioact_';

	private $cancelUsers = array();

	public function __construct($name)
	{
		parent::__construct($name);

		unset($this->arProperties['Changes']);

		$this->arProperties['TaskButtonCancelMessage'] = '';
		$this->arProperties['CancelType'] = 'any';
		$this->arProperties['SaveVariables'] = '';
	}

	protected function getPropertiesTypesMap()
	{
		$map = parent::getPropertiesTypesMap();
		unset($map['Changes']);
		return $map;
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();
		$this->cancelUsers = array();
	}

	protected function getTaskParameters($documentId, $documentService)
	{
		$taskParameters = parent::getTaskParameters($documentId, $documentService);

		$taskParameters['TaskButtonCancelMessage'] =
			$this->IsPropertyExists('TaskButtonCancelMessage')
				? $this->TaskButtonCancelMessage
				: GetMessage("BPRIOA_ACT_BUTTON2")
		;
		$taskParameters['SaveVariables'] = CBPHelper::getBool($this->SaveVariables);

		if ($taskParameters['TaskButtonCancelMessage'] == '')
		{
			$taskParameters['TaskButtonCancelMessage'] = GetMessage("BPRIOA_ACT_BUTTON2");
		}
		return $taskParameters;
	}

	public function Cancel()
	{
		parent::Cancel();
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

	protected function closeActivity()
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

	protected function completeTask($eventParameters, $rootActivity)
	{
		$cancel = !empty($eventParameters['CANCEL']);

		$taskService = $this->workflow->GetService("TaskService");
		$taskService->MarkCompleted($this->taskId, $eventParameters["REAL_USER_ID"], $cancel ? CBPTaskUserStatus::Cancel : CBPTaskUserStatus::Ok);

		$this->WriteToTrackingService(
			str_replace(
				array("#PERSON#", "#COMMENT#"),
				array("{=user:user_" . $eventParameters["REAL_USER_ID"] . "}", ($eventParameters["COMMENT"] <> '' ? ": " . $eventParameters["COMMENT"] : "")),
				GetMessage($cancel ? 'BPRIOA_ACT_CANCEL_TRACK' : 'BPRIOA_ACT_APPROVE_TRACK')
			),
			$eventParameters["REAL_USER_ID"]
		);

		if ($cancel)
			$this->cancelUsers[] = $eventParameters['USER_ID'];

		if ($cancel && $this->CancelType == 'all')
		{
			$users = empty($this->taskUsers)
				? CBPHelper::ExtractUsers($this->Users, $this->GetDocumentId())
				: $this->taskUsers;
			foreach ($users as $userId)
			{
				if (!in_array($userId, $this->cancelUsers))
					return;
			}
		}

		if (isset($eventParameters["RESPONCE"]) && ($cancel == false || CBPHelper::getBool($this->SaveVariables)))
		{
			$this->ResponcedInformation = $eventParameters["RESPONCE"];
			$rootActivity->SetVariables($eventParameters["RESPONCE"]);
		}

		$this->taskStatus = $cancel ? CBPTaskStatus::CompleteCancel : CBPTaskStatus::CompleteOk;
		$this->Unsubscribe($this);

		$cancel ? $this->closeActivity() : $this->executeOnOk();
	}

	protected function executeOnOk()
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

	public static function ShowTaskForm($arTask, $userId, $userName = "", $arRequest = null)
	{
		[$form, $buttons] = parent::ShowTaskForm($arTask, $userId, $userName, $arRequest);
		$buttons .= '<input type="submit" name="cancel" value="' . ($arTask["PARAMETERS"]["TaskButtonCancelMessage"] <> '' ? $arTask["PARAMETERS"]["TaskButtonCancelMessage"] : GetMessage("BPRIOA_ACT_BUTTON2")) . '"/>';
		return [$form, $buttons];
	}

	protected static function getCommentRequiredStar($arTask): string
	{
		$required = parent::getCommentRequiredStar($arTask);

		if (isset($arTask['PARAMETERS']['CommentRequired']))
		{
			switch ($arTask['PARAMETERS']['CommentRequired'])
			{
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

		return $required;
	}

	public static function getTaskControls($task)
	{
		$taskControls = parent::getTaskControls($task);
		$taskControls['BUTTONS'][] = [
			'TYPE' => 'submit',
			'TARGET_USER_STATUS' => CBPTaskUserStatus::Cancel,
			'NAME' => 'cancel',
			'VALUE' => 'Y',
			'TEXT' => $task['PARAMETERS']['TaskButtonCancelMessage'] <> '' ? $task["PARAMETERS"]["TaskButtonCancelMessage"] : GetMessage("BPAA_ACT_BUTTON2")
		];
		return $taskControls;
	}

	protected static function getEventParameters($task, $request)
	{
		$result = [
			'COMMENT' => isset($request['task_comment']) ? trim($request['task_comment']) : ''
		];

		if(isset($request['INLINE_USER_STATUS']) && $request['INLINE_USER_STATUS'] === \CBPTaskUserStatus::Cancel)
		{
			$request['cancel'] = true;
		}

		if (empty($request['cancel']) || $task['PARAMETERS']['SaveVariables'])
		{
			$result['RESPONCE'] =
				isset($request['fields'])
					? static::prepareResponseFields($task, $request['fields'])
					: static::getTaskResponse($task)
			;
		}
		if(!empty($request['cancel']))
		{
			$result['CANCEL'] = true;
		}

		return $result;
	}

	protected static function getDefaultLabels()
	{
		$defaultLabels = parent::getDefaultLabels();
		$defaultLabels['task_button_cancel_message'] = GetMessage('BPRIOA_ACT_BUTTON2');
		return $defaultLabels;
	}

	protected static function getPropertiesDialogMap()
	{
		return array_merge(parent::getPropertiesDialogMap(), [
			'CancelType' => [
				'Name' => GetMessage('BPRIA_PD_CANCEL_TYPE'),
				'FieldName' => 'cancel_type',
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					'any' => GetMessage('BPRIA_PD_CANCEL_TYPE_ANY'),
					'all' => GetMessage('BPRIA_PD_CANCEL_TYPE_ALL'),
				],
				'Default' => 'any',
			],
			'TaskButtonCancelMessage' => [
				'Name' => GetMessage('BPAR_PD_TASK_BUTTON_CANCEL_MESSAGE'),
				'FieldName' => 'task_button_cancel_message',
				'Type' => Bizproc\FieldType::STRING,
				'Required' => true,
				'Default' => GetMessage('BPSFA_PD_CANCEL')
			],
			'SaveVariables' => [
				'Name' => GetMessage("BPRIA_PD_SAVE_VARIABLES"),
				'FieldName' => 'save_variables',
				'Type' => Bizproc\FieldType::SELECT,
				'Required' => true,
				'Options' => [
					'Y' => GetMessage('BPSFA_PD_YES'),
					'N' => GetMessage('BPSFA_PD_NO')
				],
				'Default' => 'N'
			],
			'CommentRequired' => [
				'Name' => GetMessage('BPAR_COMMENT_REQUIRED'),
				'FieldName' => 'comment_required',
				'Type' => Bizproc\FieldType::SELECT,
				'Options' => [
					'N' => GetMessage('BPSFA_PD_NO'),
					'Y' => GetMessage('BPSFA_YES'),
					'YA' => GetMessage('BPSFA_COMMENT_REQUIRED_YA'),
					'YR' => GetMessage("BPSFA_COMMENT_REQUIRED_YR"),
				],
				'Default' => 'N'
			],
		]);
	}

	protected static function validateTaskEventParameters($arTask, $eventParameters)
	{
		parent::validateTaskEventParameters($arTask, $eventParameters);

		if (self::validateRequiredCommentInTaskEventParameters($arTask, $eventParameters))
		{
			$label =
				$arTask["PARAMETERS"]["CommentLabelMessage"] !== ''
					? $arTask["PARAMETERS"]["CommentLabelMessage"]
					: GetMessage("BPAR_ACT_COMMENT")
			;
			throw new CBPArgumentNullException(
				'task_comment',
				GetMessage("BPRIA_ACT_COMMENT_ERROR", array(
					'#COMMENT_LABEL#' => $label
				))
			);
		}

		return true;
	}

	private static function validateRequiredCommentInTaskEventParameters($arTask, $eventParameters): bool
	{
		$showComment = $arTask['PARAMETERS']['ShowComment'] ?? '';
		$commentEmpty = empty($eventParameters['COMMENT']);
		$commentRequiredValue = $arTask['PARAMETERS']['CommentRequired'] ?? '';

		$commentRequired = false;
		if (
			$commentRequiredValue === 'Y'
			|| ($commentRequiredValue === 'YA' && !$eventParameters['CANCEL'])
			|| ($commentRequiredValue === 'YR' && $eventParameters['CANCEL'])
		)
		{
			$commentRequired = true;
		}

		return ($showComment === 'Y' && $commentEmpty && $commentRequired);
	}
}
