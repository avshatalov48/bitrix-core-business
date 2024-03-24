<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

class CBPApproveActivity extends CBPCompositeActivity implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $taskId = 0;
	private $taskUsers = [];
	private $subscriptionId = 0;

	private $isInEventActivityMode = false;
	private $taskStatus = false;

	private $arApproveResults = [];
	private $arApproveOriginalResults = [];

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'Users' => null,
			'ApproveType' => 'all',
			'Percent' => 100,
			'OverdueDate' => null,
			'Name' => null,
			'Description' => null,
			'Parameters' => null,
			'ApproveMinPercent' => 50,
			'ApproveWaitForAll' => 'N',
			'TaskId' => 0,
			'Comments' => '',
			'VotedCount' => 0,
			'TotalCount' => 0,
			'VotedPercent' => 0,
			'ApprovedPercent' => 0,
			'NotApprovedPercent' => 0,
			'ApprovedCount' => 0,
			'NotApprovedCount' => 0,
			'StatusMessage' => '',
			'SetStatusMessage' => 'Y',
			'LastApprover' => null,
			'LastApproverComment' => '',
			'Approvers' => '',
			'Rejecters' => '',
			'UserApprovers' => [],
			'UserRejecters' => [],
			'TimeoutDuration' => 0,
			'TimeoutDurationType' => 's',
			'IsTimeout' => 0,
			'TaskButton1Message' => '',
			'TaskButton2Message' => '',
			'CommentLabelMessage' => '',
			'ShowComment' => 'Y',
			'CommentRequired' => 'N',
			'AccessControl' => 'N',
			'DelegationType' => 0,
		];

		$this->SetPropertiesTypes([
			'TaskId' => ['Type' => 'int'],
			'Comments' => [
				'Type' => 'string',
			],
			'VotedCount' => [
				'Type' => 'int',
			],
			'TotalCount' => [
				'Type' => 'int',
			],
			'VotedPercent' => [
				'Type' => 'int',
			],
			'ApprovedPercent' => [
				'Type' => 'int',
			],
			'NotApprovedPercent' => [
				'Type' => 'int',
			],
			'ApprovedCount' => [
				'Type' => 'int',
			],
			'NotApprovedCount' => [
				'Type' => 'int',
			],
			'LastApprover' => [
				'Type' => 'user',
			],
			'LastApproverComment' => [
				'Type' => 'string',
			],
			'Approvers' => [
				'Type' => 'string',
			],
			'Rejecters' => [
				'Type' => 'string',
			],
			'UserApprovers' => [
				'Type' => 'user',
				'Multiple' => true,
			],
			'UserRejecters' => [
				'Type' => 'user',
				'Multiple' => true,
			],
			'IsTimeout' => [
				'Type' => 'int',
			],
		]);
	}

	public function execute()
	{
		if ($this->isInEventActivityMode)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$this->Subscribe($this);

		$this->isInEventActivityMode = false;

		return CBPActivityExecutionStatus::Executing;
	}

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$this->isInEventActivityMode = true;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService('DocumentService');

		$arUsersTmp = $this->Users;
		if (!is_array($arUsersTmp))
		{
			$arUsersTmp = [$arUsersTmp];
		}

		$approveType = 'all';
		if ($this->ApproveType == 'any')
		{
			$this->writeToTrackingService(
				str_replace(
					'#VAL#', '{=user:' . implode('}, {=user:', $arUsersTmp) . '}',
					Loc::getMessage('BPAA_ACT_TRACK1')
				)
			);
			$approveType = $this->ApproveType;
		}
		elseif ($this->ApproveType == 'all')
		{
			$this->writeToTrackingService(
				str_replace(
					'#VAL#', '{=user:' . implode('}, {=user:', $arUsersTmp) . '}',
					Loc::getMessage('BPAA_ACT_TRACK2')
				)
			);
		}
		elseif ($this->ApproveType == 'vote')
		{
			$this->writeToTrackingService(
				str_replace(
					'#VAL#', '{=user:' . implode('}, {=user:', $arUsersTmp) . '}',
					Loc::getMessage('BPAA_ACT_TRACK3')
				)
			);
			$approveType = $this->ApproveType;
		}

		$arUsers = CBPHelper::extractUsers($arUsersTmp, $documentId, false);

		$arParameters = $this->Parameters;
		if (!is_array($arParameters))
		{
			$arParameters = [$arParameters];
		}
		$arParameters['DOCUMENT_ID'] = $documentId;
		$arParameters['DOCUMENT_URL'] = $documentService->getDocumentAdminPage($documentId);
		$arParameters['TaskButton1Message'] =
			$this->isPropertyExists('TaskButton1Message')
				? $this->TaskButton1Message
				: Loc::getMessage('BPAA_ACT_BUTTON1')
		;
		if ($arParameters['TaskButton1Message'] == '')
		{
			$arParameters['TaskButton1Message'] = Loc::getMessage('BPAA_ACT_BUTTON1');
		}
		$arParameters['TaskButton2Message'] =
			$this->isPropertyExists('TaskButton2Message')
				? $this->TaskButton2Message
				: Loc::getMessage('BPAA_ACT_BUTTON2_MSGVER_1')
		;
		if ($arParameters['TaskButton2Message'] == '')
		{
			$arParameters['TaskButton2Message'] = Loc::getMessage('BPAA_ACT_BUTTON2_MSGVER_1');
		}
		$arParameters['CommentLabelMessage'] =
			$this->isPropertyExists('CommentLabelMessage')
				? $this->CommentLabelMessage
				: Loc::getMessage('BPAA_ACT_COMMENT')
		;
		if ($arParameters['CommentLabelMessage'] == '')
		{
			$arParameters['CommentLabelMessage'] = Loc::getMessage('BPAA_ACT_COMMENT');
		}
		$arParameters['ShowComment'] = $this->isPropertyExists('ShowComment') ? $this->ShowComment : 'Y';
		if ($arParameters['ShowComment'] != 'Y' && $arParameters['ShowComment'] != 'N')
		{
			$arParameters['ShowComment'] = 'Y';
		}

		$arParameters['CommentRequired'] =
			$this->isPropertyExists('CommentRequired')
				? $this->CommentRequired
				: 'N'
		;
		$arParameters['AccessControl'] =
			$this->isPropertyExists('AccessControl') && $this->AccessControl == 'Y'
				? 'Y'
				: 'N'
		;
		$arParameters['ApproveType'] = $approveType;

		$overdueDate = $this->OverdueDate;
		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$overdueDate = ConvertTimeStamp(
				time() + max($timeoutDuration, CBPSchedulerService::getDelayMinLimit()),
				'FULL'
			);
		}

		/** @var CBPTaskService $taskService */
		$taskService = $this->workflow->GetService('TaskService');
		$this->taskId = $taskService->createTask(
			[
				'USERS' => $arUsers,
				'WORKFLOW_ID' => $this->getWorkflowInstanceId(),
				'ACTIVITY' => 'ApproveActivity',
				'ACTIVITY_NAME' => $this->name,
				'OVERDUE_DATE' => $overdueDate,
				'NAME' => $this->Name,
				'DESCRIPTION' => $this->Description,
				'PARAMETERS' => $arParameters,
				'IS_INLINE' => $arParameters['ShowComment'] == 'Y' ? 'N' : 'Y',
				'DELEGATION_TYPE' => (int)$this->DelegationType,
				'DOCUMENT_NAME' => $documentService->getDocumentName($documentId),
			]
		);
		$this->TaskId = $this->taskId;
		$this->taskUsers = $arUsers;

		$this->TotalCount = count($arUsers);
		if (!$this->isPropertyExists('SetStatusMessage') || $this->SetStatusMessage == 'Y')
		{
			$totalCount = $this->TotalCount;
			$message =
				($this->isPropertyExists('StatusMessage') && $this->StatusMessage <> '')
					? $this->StatusMessage
					: Loc::getMessage('BPAA_ACT_INFO')
			;
			$this->setStatusTitle(str_replace(
				['#PERC#', '#PERCENT#', '#REV#', '#VOTED#', '#TOT#', '#TOTAL#', '#APPROVERS#', '#REJECTERS#'],
				[
					0,
					0,
					0,
					0,
					$totalCount,
					$totalCount,
					Loc::getMessage('BPAA_ACT_APPROVERS_NONE'),
					Loc::getMessage('BPAA_ACT_APPROVERS_NONE'),
				],
				$message
			));
		}

		if ($timeoutDuration > 0)
		{
			/** @var CBPSchedulerService $schedulerService */
			$schedulerService = $this->workflow->GetService('SchedulerService');
			$this->subscriptionId = $schedulerService->subscribeOnTime(
				$this->workflow->getInstanceId(),
				$this->name,
				time() + $timeoutDuration
			);
		}

		$this->workflow->addEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$taskService = $this->workflow->GetService('TaskService');
		if ($this->taskStatus === false)
		{
			$taskService->DeleteTask($this->taskId);
		}
		else
		{
			$taskService->Update($this->taskId, ['STATUS' => $this->taskStatus]);
		}

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			$schedulerService = $this->workflow->GetService('SchedulerService');
			$schedulerService->UnSubscribeOnTime($this->subscriptionId);
		}

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);

		$this->taskId = 0;
		$this->taskUsers = [];
		$this->taskStatus = false;
		$this->subscriptionId = 0;
	}

	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->taskId > 0)
		{
			$this->Unsubscribe($this);
		}

		for ($i = count($this->arActivities) - 1; $i >= 0; $i--)
		{
			$activity = $this->arActivities[$i];
			if ($activity->executionStatus == CBPActivityExecutionStatus::Executing)
			{
				$this->workflow->CancelActivity($activity);

				return CBPActivityExecutionStatus::Canceling;
			}

			if (
				($activity->executionStatus == CBPActivityExecutionStatus::Canceling)
				|| ($activity->executionStatus == CBPActivityExecutionStatus::Faulting)
			)
			{
				return CBPActivityExecutionStatus::Canceling;
			}

			if ($activity->executionStatus == CBPActivityExecutionStatus::Closed)
			{
				return CBPActivityExecutionStatus::Closed;
			}
		}

		return CBPActivityExecutionStatus::Closed;
	}

	protected function ExecuteOnApprove()
	{
		if (count($this->arActivities) <= 0)
		{
			$this->workflow->closeActivity($this);

			return;
		}

		$this->writeToTrackingService(Loc::getMessage('BPAA_ACT_APPROVE'));

		$activity = $this->arActivities[0];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->executeActivity($activity);
	}

	protected function ExecuteOnNonApprove()
	{
		if (count($this->arActivities) <= 1)
		{
			$this->workflow->closeActivity($this);
			return;
		}

		$this->writeToTrackingService(Loc::getMessage('BPAA_ACT_NONAPPROVE'));

		$activity = $this->arActivities[1];
		$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->executeActivity($activity);
	}

	public function OnExternalEvent($arEventParameters = [])
	{
		if ($this->executionStatus == CBPActivityExecutionStatus::Closed)
		{
			return;
		}

		$timeoutDuration = $this->CalculateTimeoutDuration();
		if ($timeoutDuration > 0)
		{
			if (array_key_exists('SchedulerService', $arEventParameters) && $arEventParameters['SchedulerService'] == 'OnAgent')
			{
				$this->IsTimeout = 1;
				$this->taskStatus = CBPTaskStatus::Timeout;
				$this->Unsubscribe($this);
				$this->writeApproversResult();
				$this->ExecuteOnNonApprove();

				return;
			}
		}

		if (!array_key_exists('USER_ID', $arEventParameters) || intval($arEventParameters['USER_ID']) <= 0)
		{
			return;
		}
		if (!array_key_exists('APPROVE', $arEventParameters))
		{
			return;
		}

		if (empty($arEventParameters['REAL_USER_ID']))
		{
			$arEventParameters['REAL_USER_ID'] = $arEventParameters['USER_ID'];
		}

		$approve = ($arEventParameters['APPROVE'] ? true : false);

		$arUsers = $this->taskUsers;
		if (empty($arUsers)) //compatibility
		{
			$arUsers = CBPHelper::extractUsers($this->Users, $this->GetDocumentId(), false);
		}

		$arEventParameters['USER_ID'] = intval($arEventParameters['USER_ID']);
		$arEventParameters['REAL_USER_ID'] = intval($arEventParameters['REAL_USER_ID']);
		if (!in_array($arEventParameters['USER_ID'], $arUsers))
		{
			return;
		}

		if ($this->isPropertyExists('LastApprover'))
		{
			$this->LastApprover = 'user_' . $arEventParameters['REAL_USER_ID'];
		}
		if ($this->isPropertyExists('LastApproverComment'))
		{
			$this->LastApproverComment = (string)$arEventParameters['COMMENT'];
		}

		$taskService = $this->workflow->GetService('TaskService');
		$taskService->markCompleted(
			$this->taskId,
			$arEventParameters['REAL_USER_ID'],
			$approve ? CBPTaskUserStatus::Yes : CBPTaskUserStatus::No
		);

		$dbUser = CUser::GetById($arEventParameters['REAL_USER_ID']);
		if($arUser = $dbUser->Fetch())
		{
			$this->Comments .=
				CUser::FormatName(
					COption::GetOptionString(
						'bizproc',
						'name_template',
						CSite::GetNameFormat(false),
						SITE_ID
					),
					$arUser
				)
				. ' ('
				. $arUser['LOGIN']
				. '): '
				. ($approve ? Loc::getMessage('BPAA_LOG_Y') : Loc::getMessage('BPAA_LOG_N_MSGVER_1'))
				. "\n"
				. (
					$arEventParameters['COMMENT'] <> ''
						? Loc::getMessage('BPAA_LOG_COMMENTS') . ': ' . $arEventParameters['COMMENT']
						: ''
				)
				. "\n"
			;
		}

		$this->writeToTrackingService(
			str_replace(
				['#PERSON#', '#COMMENT#'],
				[
					'{=user:user_' . $arEventParameters['REAL_USER_ID'] . '}',
					($arEventParameters['COMMENT'] <> '' ? ': ' . $arEventParameters['COMMENT'] : ''),
				],
				Loc::getMessage($approve ? 'BPAA_ACT_APPROVE_TRACK' : 'BPAA_ACT_NONAPPROVE_TRACK')
			),
			$arEventParameters['REAL_USER_ID']
		);

		$result = 'Continue';

		$this->arApproveOriginalResults[$arEventParameters['USER_ID']] = $approve;
		$this->arApproveResults[$arEventParameters['REAL_USER_ID']] = $approve;

		if($approve)
		{
			$this->ApprovedCount = $this->ApprovedCount + 1;
		}
		else
		{
			$this->NotApprovedCount = $this->NotApprovedCount + 1;
		}

		$this->VotedCount = count($this->arApproveResults);
		$this->VotedPercent = intval($this->VotedCount/$this->TotalCount*100);
		$this->ApprovedPercent = intval($this->ApprovedCount/$this->TotalCount*100);
		$this->NotApprovedPercent = intval($this->NotApprovedCount/$this->TotalCount*100);

		if ($this->ApproveType == 'any')
		{
			$result = ($approve ? 'Approve' : 'NonApprove');
		}
		elseif ($this->ApproveType == 'all')
		{
			if (!$approve)
			{
				$result = 'NonApprove';
			}
			else
			{
				$allAproved = true;
				foreach ($arUsers as $userId)
				{
					if (!isset($this->arApproveOriginalResults[$userId]))
					{
						$allAproved = false;
					}
				}

				if ($allAproved)
				{
					$result = 'Approve';
				}
			}
		}
		elseif ($this->ApproveType == 'vote')
		{
			if($this->ApproveWaitForAll == 'Y')
			{
				if($this->VotedPercent==100)
				{
					if (
						$this->ApprovedPercent > $this->ApproveMinPercent
						|| ($this->ApprovedPercent == 100 && $this->ApproveMinPercent == 100)
					)
					{
						$result = 'Approve';
					}
					else
					{
						$result = 'NonApprove';
					}
				}
			}
			else
			{
				$noneApprovedPercent = ($this->VotedCount-$this->ApprovedCount) / $this->TotalCount * 100;
				if (
					$this->ApprovedPercent > $this->ApproveMinPercent
					|| ($this->ApprovedPercent == 100 && $this->ApproveMinPercent == 100)
				)
				{
					$result = 'Approve';
				}
				elseif($noneApprovedPercent > 0 && $noneApprovedPercent >= 100 - $this->ApproveMinPercent)
				{
					$result = 'NonApprove';
				}
			}
		}

		$approvers = '';
		$rejecters = '';
		if (!$this->isPropertyExists('SetStatusMessage') || $this->SetStatusMessage == 'Y')
		{
			$statusMessage = $this->StatusMessage;
			$messageTemplate =
				($statusMessage && is_string($statusMessage))
					? $statusMessage
					: Loc::getMessage('BPAA_ACT_INFO')
			;
			$votedPercent = $this->VotedPercent;
			$votedCount = $this->VotedCount;
			$totalCount = $this->TotalCount;

			if (mb_strpos($messageTemplate, '#REJECTERS#') !== false)
			{
				$rejecters = $this->GetApproversNames(false);
			}
			if (mb_strpos($messageTemplate, '#APPROVERS#') !== false)
			{
				$approvers = $this->GetApproversNames(true);
			}

			$approversTmp = $approvers;
			$rejectersTmp = $rejecters;
			if ($approversTmp == '')
			{
				$approversTmp = Loc::getMessage('BPAA_ACT_APPROVERS_NONE');
			}
			if ($rejectersTmp == '')
			{
				$rejectersTmp = Loc::getMessage('BPAA_ACT_APPROVERS_NONE');
			}

			$this->SetStatusTitle(str_replace(
				['#PERC#', '#PERCENT#', '#REV#', '#VOTED#', '#TOT#', '#TOTAL#', '#APPROVERS#', '#REJECTERS#'],
				[
					$votedPercent,
					$votedPercent,
					$votedCount,
					$votedCount,
					$totalCount,
					$totalCount,
					$approversTmp,
					$rejectersTmp,
				],
				$messageTemplate
			));
		}

		if ($result != 'Continue')
		{
			$this->taskStatus = $result == 'Approve' ? CBPTaskStatus::CompleteYes : CBPTaskStatus::CompleteNo;
			$this->Unsubscribe($this);
			$this->writeApproversResult();

			if ($result == 'Approve')
			{
				$this->ExecuteOnApprove();
			}
			else
			{
				$this->ExecuteOnNonApprove();
			}
		}
	}

	private function GetApproversNames($b)
	{
		$result = '';

		$ar = [];
		foreach ($this->arApproveResults as $k => $v)
		{
			if (($b && $v )||( !$b && !$v))
			{
				$ar[] = $k;
			}
		}

		if (count($ar) > 0)
		{
			$dbUsers = CUser::GetList(
				'',
				'',
				['ID' => implode('|', $ar)],
				['FIELDS' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE']]
			);
			while ($arUser = $dbUsers->Fetch())
			{
				if ($result != '')
				{
					$result .= ', ';
				}
				$result .= CUser::FormatName(
					COption::GetOptionString(
						'bizproc',
						'name_template',
						CSite::GetNameFormat(false)
						,
						SITE_ID
					),
					$arUser
					)
					. ' ('
					. $arUser['LOGIN']
					. ')'
				;
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
		$sender->removeStatusChangeHandler(self::ClosedEvent, $this);
		$this->workflow->closeActivity($this);
	}

	protected function ReInitialize()
	{
		parent::ReInitialize();

		$this->TaskId = 0;
		$this->arApproveResults = [];
		$this->arApproveOriginalResults = [];
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

	public static function ShowTaskForm($arTask, $userId, $userName = '')
	{
		$form = '';

		if (!array_key_exists('ShowComment', $arTask['PARAMETERS']) || ($arTask['PARAMETERS']['ShowComment'] != 'N'))
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
					.($arTask['PARAMETERS']['CommentLabelMessage'] <> '' ? $arTask['PARAMETERS']['CommentLabelMessage'] : Loc::getMessage('BPAA_ACT_COMMENT'))
					.$required
				.':</td>'.
				'<td valign="top" width="60%" class="bizproc-field-value">'.
				'<textarea rows="3" cols="50" name="task_comment"></textarea>'.
				'</td></tr>';
		}

		$buttons =
			'<input type="submit" name="approve" value="'.($arTask['PARAMETERS']['TaskButton1Message'] <> '' ? $arTask['PARAMETERS']['TaskButton1Message'] : Loc::getMessage('BPAA_ACT_BUTTON1')).'"/>'.
			'<input type="submit" name="nonapprove" value="'.($arTask['PARAMETERS']['TaskButton2Message'] <> '' ? $arTask['PARAMETERS']['TaskButton2Message'] : Loc::getMessage('BPAA_ACT_BUTTON2_MSGVER_1')).'"/>';

		return array($form, $buttons);
	}

	public static function getTaskControls($task)
	{
		$controls = [
			'BUTTONS' => [
				[
					'TYPE' => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::Yes,
					'NAME' => 'approve',
					'VALUE' => 'Y',
					'TEXT' => $task['PARAMETERS']['TaskButton1Message'] ?: Loc::getMessage('BPAA_ACT_BUTTON1'),
				],
				[
					'TYPE' => 'submit',
					'TARGET_USER_STATUS' => CBPTaskUserStatus::No,
					'NAME' => 'nonapprove',
					'VALUE' => 'Y',
					'TEXT' => $task['PARAMETERS']['TaskButton2Message'] ?: Loc::getMessage('BPAA_ACT_BUTTON2_MSGVER_1'),
				],
			],
		];

		if (($task["PARAMETERS"]["ShowComment"] ?? 'N') !== "N")
		{
			$controls['FIELDS'] = [
				[
					'Id' => 'task_comment',
					'Type' => 'text',
					'Name' => $task["PARAMETERS"]["CommentLabelMessage"] ?: GetMessage("BPAA_ACT_COMMENT"),
					'Required' => (($task['PARAMETERS']['CommentRequired'] ?? '') === 'Y'),
				],
			];
		}

		return $controls;
	}

	public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = '', $realUserId = null)
	{
		$arErrors = [];

		try
		{
			$userId = intval($userId);
			if ($userId <= 0)
			{
				throw new CBPArgumentNullException('userId');
			}

			$arEventParameters = [
				'USER_ID' => $userId,
				'REAL_USER_ID' => $realUserId,
				'USER_NAME' => $userName,
				'COMMENT' => trim($arRequest['fields']['task_comment'] ?? ($arRequest['task_comment'] ?? '')),
			];

			if (
				(isset($arRequest['approve']) && $arRequest['approve'] <> '')
				|| (isset($arRequest['INLINE_USER_STATUS']) && $arRequest['INLINE_USER_STATUS'] == CBPTaskUserStatus::Yes)
			)
			{
				$arEventParameters['APPROVE'] = true;
			}
			elseif (
				(isset($arRequest['nonapprove']) && $arRequest['nonapprove'] <> '')
				|| (isset($arRequest['INLINE_USER_STATUS']) && $arRequest['INLINE_USER_STATUS'] == CBPTaskUserStatus::No)
			)
			{
				$arEventParameters['APPROVE'] = false;
			}
			else
			{
				throw new CBPNotSupportedException(Loc::getMessage('BPAA_ACT_NO_ACTION'));
			}

			if (
				isset($arTask['PARAMETERS']['ShowComment'])
				&& $arTask['PARAMETERS']['ShowComment'] === 'Y'
				&& isset($arTask['PARAMETERS']['CommentRequired'])
				&& empty($arEventParameters['COMMENT'])
				&& (
					$arTask['PARAMETERS']['CommentRequired'] === 'Y'
					|| ($arTask['PARAMETERS']['CommentRequired'] === 'YA' && $arEventParameters['APPROVE'])
					|| ($arTask['PARAMETERS']['CommentRequired'] === 'YR' && !$arEventParameters['APPROVE'])
				)
			)
			{
				$label =
					$arTask['PARAMETERS']['CommentLabelMessage'] <> ''
						? $arTask['PARAMETERS']['CommentLabelMessage']
						: Loc::getMessage('BPAA_ACT_COMMENT'
					)
				;
				throw new CBPArgumentNullException(
					'task_comment',
					Loc::getMessage('BPAA_ACT_COMMENT_ERROR', ['#COMMENT_LABEL#' => $label])
				);
			}

			CBPRuntime::SendExternalEvent($arTask['WORKFLOW_ID'], $arTask['ACTIVITY_NAME'], $arEventParameters);

			return true;
		}
		catch (Exception $e)
		{
			$arErrors[] = [
				'code' => $e->getCode(),
				'message' =>  $e->getMessage(),
				'file' => $e->getFile() . ' [' . $e->getLine() . ']',
			];
		}

		return false;
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		if (!array_key_exists('Users', $arTestProperties))
		{
			$bUsersFieldEmpty = true;
		}
		else
		{
			if (!is_array($arTestProperties['Users']))
			{
				$arTestProperties['Users'] = [$arTestProperties['Users']];
			}

			$bUsersFieldEmpty = true;
			foreach ($arTestProperties['Users'] as $userId)
			{
				if ((!is_array($userId) && (trim($userId) <> '')) || (is_array($userId) && (count($userId) > 0)))
				{
					$bUsersFieldEmpty = false;
					break;
				}
			}
		}

		if ($bUsersFieldEmpty)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'Users',
				'message' => Loc::getMessage('BPAA_ACT_PROP_EMPTY1'),
			];
		}

		if (!array_key_exists('ApproveType', $arTestProperties))
		{
			{
				$arErrors[] = [
					'code' => 'NotExist',
					'parameter' => 'ApproveType',
					'message' => Loc::getMessage('BPAA_ACT_PROP_EMPTY2'),
				];
			}
		}
		else
		{
			if (!in_array($arTestProperties['ApproveType'], ['any', 'all', 'vote']))
			{
				$arErrors[] = [
					'code' => 'NotInRange',
					'parameter' => 'ApproveType',
					'message' => Loc::getMessage('BPAA_ACT_PROP_EMPTY3'),
				];
			}
		}

		if (!array_key_exists('Name', $arTestProperties) || $arTestProperties['Name'] == '')
		{
			{
				$arErrors[] = [
					'code' => 'NotExist',
					'parameter' => 'Name',
					'message' => Loc::getMessage('BPAA_ACT_PROP_EMPTY4'),
				];
			}
		}

		return array_merge($arErrors, parent::validateProperties($arTestProperties, $user));
	}

	private function CalculateTimeoutDuration()
	{
		$timeoutDuration = ($this->IsPropertyExists('TimeoutDuration') ? $this->TimeoutDuration : 0);

		$timeoutDurationType = ($this->IsPropertyExists('TimeoutDurationType') ? $this->TimeoutDurationType : 's');
		$timeoutDurationType = mb_strtolower($timeoutDurationType);
		if (!in_array($timeoutDurationType, ['s', 'd', 'h', 'm']))
		{
			$timeoutDurationType = 's';
		}

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

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = ''
	)
	{
		$runtime = CBPRuntime::getRuntime();

		$arMap = [
			'Users' => 'approve_users',
			'ApproveType' => 'approve_type',
			'ApproveMinPercent' => 'approve_percent',
			'OverdueDate' => 'approve_overdue_date',
			'Name' => 'approve_name',
			'Description' => 'approve_description',
			'Parameters' => 'approve_parameters',
			'ApproveWaitForAll' => 'approve_wait',
			'StatusMessage' => 'status_message',
			'SetStatusMessage' => 'set_status_message',
			'TimeoutDuration' => 'timeout_duration',
			'TimeoutDurationType' => 'timeout_duration_type',
			'TaskButton1Message' => 'task_button1_message',
			'TaskButton2Message' => 'task_button2_message',
			'CommentLabelMessage' => 'comment_label_message',
			'ShowComment' => 'show_comment',
			'CommentRequired' => 'comment_required',
			'AccessControl' => 'access_control',
			'DelegationType' => 'delegation_type',
		];

		if (!is_array($arWorkflowParameters))
		{
			$arWorkflowParameters = [];
		}
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = Array();
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity['Properties']))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity['Properties']))
					{
						if ($k == 'Users')
						{
							$arCurrentValues[$arMap[$k]] = CBPHelper::usersArrayToString(
								$arCurrentActivity['Properties'][$k],
								$arWorkflowTemplate,
								$documentType
							);
						}
						elseif ($k == 'TimeoutDuration')
						{
							$arCurrentValues['timeout_duration'] = $arCurrentActivity['Properties']['TimeoutDuration'];
							if (
								!CBPActivity::isExpression($arCurrentValues['timeout_duration'])
								&& !array_key_exists('TimeoutDurationType', $arCurrentActivity['Properties'])
							)
							{
								$arCurrentValues['timeout_duration'] = intval($arCurrentValues['timeout_duration']);
								$arCurrentValues['timeout_duration_type'] = 's';
								if ($arCurrentValues['timeout_duration'] % (3600 * 24) == 0)
								{
									$arCurrentValues['timeout_duration'] = $arCurrentValues['timeout_duration'] / (3600 * 24);
									$arCurrentValues['timeout_duration_type'] = 'd';
								}
								elseif ($arCurrentValues['timeout_duration'] % 3600 == 0)
								{
									$arCurrentValues['timeout_duration'] = $arCurrentValues['timeout_duration'] / 3600;
									$arCurrentValues['timeout_duration_type'] = 'h';
								}
								elseif ($arCurrentValues['timeout_duration'] % 60 == 0)
								{
									$arCurrentValues['timeout_duration'] = $arCurrentValues['timeout_duration'] / 60;
									$arCurrentValues['timeout_duration_type'] = 'm';
								}
							}
						}
						else
						{
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity['Properties'][$k];
						}
					}
					else
					{
						if (!is_array($arCurrentValues) || !array_key_exists($arMap[$k], $arCurrentValues))
						{
							$arCurrentValues[$arMap[$k]] = '';
						}
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
				{
					$arCurrentValues[$arMap[$k]] = '';
				}
			}

			if($arCurrentValues['approve_wait'] == '')
			{
				$arCurrentValues['approve_wait'] = 'N';
			}

			if($arCurrentValues['approve_percent'] == '')
			{
				$arCurrentValues['approve_percent'] = '50';
			}
		}

		if ($arCurrentValues['status_message'] == '')
		{
			$arCurrentValues['status_message'] = Loc::getMessage('BPAA_ACT_INFO');
		}
		if ($arCurrentValues['task_button1_message'] == '')
		{
			$arCurrentValues['task_button1_message'] = Loc::getMessage('BPAA_ACT_BUTTON1');
		}
		if ($arCurrentValues['task_button2_message'] == '')
		{
			$arCurrentValues['task_button2_message'] = Loc::getMessage('BPAA_ACT_BUTTON2_MSGVER_1');
		}
		if ($arCurrentValues['comment_label_message'] == '')
		{
			$arCurrentValues['comment_label_message'] = Loc::getMessage('BPAA_ACT_COMMENT');
		}
		if ($arCurrentValues['timeout_duration_type'] == '')
		{
			$arCurrentValues['timeout_duration_type'] = 's';
		}

		$documentService = $runtime->GetService('DocumentService');
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			'properties_dialog.php',
			[
				'arCurrentValues' => $arCurrentValues,
				'arDocumentFields' => $arDocumentFields,
				'formName' => $formName,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$arErrors = [];

		$runtime = CBPRuntime::GetRuntime();

		$arMap = [
			'approve_users' => 'Users',
			'approve_type' => 'ApproveType',
			'approve_overdue_date' => 'OverdueDate',
			'approve_percent' => 'ApproveMinPercent',
			'approve_wait' => 'ApproveWaitForAll',
			'approve_name' => 'Name',
			'approve_description' => 'Description',
			'approve_parameters' => 'Parameters',
			'status_message' => 'StatusMessage',
			'set_status_message' => 'SetStatusMessage',
			'timeout_duration' => 'TimeoutDuration',
			'timeout_duration_type' => 'TimeoutDurationType',
			'task_button1_message' => 'TaskButton1Message',
			'task_button2_message' => 'TaskButton2Message',
			'comment_label_message' => 'CommentLabelMessage',
			'show_comment' => 'ShowComment',
			'comment_required' => 'CommentRequired',
			'access_control' => 'AccessControl',
			'delegation_type' => 'DelegationType',
		];

		$arProperties = [];
		foreach ($arMap as $key => $value)
		{
			if ($key == 'approve_users')
			{
				continue;
			}

			if (!empty($arCurrentValues[$key . '_X']))
			{
				$arProperties[$value] = $arCurrentValues[$key . '_X'];
			}
			else
			{
				$arProperties[$value] = $arCurrentValues[$key] ?? null;
			}
		}

		$arProperties['Users'] = CBPHelper::usersStringToArray(
			$arCurrentValues['approve_users'],
			$documentType,
			$arErrors
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arErrors = self::validateProperties($arProperties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;

		return true;
	}
}
