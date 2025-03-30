<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Task\Dto\ExternalEventTask\AddCommandDto;
use Bitrix\Bizproc\Task\Dto\ExternalEventTask\RemoveCommandDto;
use Bitrix\Bizproc\Task\Dto\MarkCompletedTaskDto;
use Bitrix\Bizproc\Task\ExternalEventTask;
use Bitrix\Bizproc\Task\Manager;
use Bitrix\Main\Localization\Loc;

/**
 * @property-read array $Permission
 * @property-read int $SenderUserId
 */
class CBPHandleExternalEventActivity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener, IBPEventDrivenActivity
{
	private bool $isInEventActivityMode = false;
	private bool $canAddTask = true;
	private array $taskUsers = [];
	private ?int $taskId = null;

	public function __construct($name)
	{
		parent::__construct($name);

		$this->arProperties = [
			'Title' => '',
			'Permission' => [],
			'SenderUserId' => null
		];

		$this->setPropertiesTypes([
			'SenderUserId' => [
				'Type' => 'user',
			],
		]);
	}

	public function subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$this->isInEventActivityMode = true;

		$users = [];
		$permissions = $this->Permission;
		if (is_array($permissions) && $permissions)
		{
			foreach ($permissions as $val)
			{
				$users[] = (str_starts_with($val, '{=') ? $val : '{=user:' . $val . '}');
			}
		}

		if ($users)
		{
			$this->writeToTrackingService(str_replace(
				['#EVENT#', '#VAL#'], [$this->name, implode(', ', $users)],
				Loc::getMessage('BPHEEA_TRACK'))
			);
		}

		$stateService = $this->workflow->getService('StateService');
		$stateService->addStateParameter(
			$this->getWorkflowInstanceId(),
			[
				'NAME' => $this->name,
				'TITLE' => $this->Title,
				'PERMISSION' => $this->Permission,
			]
		);

		$this->workflow->addEventHandler($this->name, $eventHandler);
		$this->addTask();
	}

	public function unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		$stateService = $this->workflow->getRuntime()->getStateService();
		$stateService->deleteStateParameter($this->getWorkflowInstanceId(), $this->name);

		$this->workflow->removeEventHandler($this->name, $eventHandler);
		$this->completeTask();
	}

	public function execute()
	{
		if ($this->isInEventActivityMode)
		{
			return CBPActivityExecutionStatus::Closed;
		}

		$this->subscribe($this);

		$this->isInEventActivityMode = false;

		return CBPActivityExecutionStatus::Executing;
	}

	public function cancel()
	{
		if (!$this->isInEventActivityMode)
		{
			$this->unsubscribe($this);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function onExternalEvent($arEventParameters = [])
	{
		if ($this->onExternalEventHandler($arEventParameters))
		{
			$this->unsubscribe($this);
			$this->workflow->closeActivity($this);
		}
	}

	public function onExternalDrivenEvent($arEventParameters = [])
	{
		return $this->onExternalEventHandler($arEventParameters);
	}

	private function onExternalEventHandler($arEventParameters = [])
	{
		$permissions = $this->Permission;
		if (is_array($permissions) && $permissions)
		{
			$senderGroups = (array_key_exists('Groups', $arEventParameters) ? $arEventParameters['Groups'] : []);
			if (!is_array($senderGroups))
			{
				$senderGroups = [$senderGroups];
			}

			if (array_key_exists('User', $arEventParameters))
			{
				$senderGroups[] = 'user_' . $arEventParameters['User'];
				$senderGroups = array_merge($senderGroups, CBPHelper::getUserExtendedGroups($arEventParameters['User']));
			}

			if (!$senderGroups || !array_intersect($permissions, $senderGroups))
			{
				return false;
			}
		}

		if ((int)$this->executionStatus !== CBPActivityExecutionStatus::Closed)
		{
			if (array_key_exists('User', $arEventParameters))
			{
				$this->SenderUserId = 'user_' . $arEventParameters['User'];
			}

			return true;
		}

		return false;
	}

	public function onStateExternalEvent($arEventParameters = [])
	{
		if (
			(int)$this->executionStatus !== CBPActivityExecutionStatus::Closed
			&& array_key_exists('User', $arEventParameters)
		)
		{
			$this->SenderUserId = 'user_' . $arEventParameters['User'];
		}
	}

	private function addTask(): void
	{
		if (!$this->canAddTask)
		{
			return;
		}

		$this->taskUsers = CBPHelper::extractUsers($this->Permission, $this->getDocumentId());
		if (!$this->taskUsers)
		{
			return;
		}

		$task = ExternalEventTask::addToTask(
			new AddCommandDto(
				id: CBPHelper::stringify($this->name),
				userIds: $this->taskUsers,
				workflowId: $this->getWorkflowInstanceId(),
			),
		);
		if ($task)
		{
			$this->taskId = $task->getId();
		}
	}

	private function completeTask(): void
	{
		if (!$this->canAddTask || !$this->taskUsers)
		{
			return;
		}

		$taskData = ExternalEventTask::getCurrentTask($this->getWorkflowInstanceId());
		if ($taskData && (int)$taskData['ID'] === $this->taskId)
		{
			$userId = (
				!empty($this->SenderUserId)
					? CBPHelper::extractFirstUser($this->SenderUserId, $this->getDocumentId())
					: 0
			);

			/** @var ExternalEventTask $task */
			$task = Manager::getTask(ExternalEventTask::getAssociatedActivity(), $taskData, $userId);

			if ($userId > 0 && in_array($userId, $this->taskUsers, true))
			{
				$task->markCompleted(new MarkCompletedTaskDto(userId: $userId));
			}

			$task->removeFromTask(new RemoveCommandDto(
				id: CBPHelper::stringify($this->name),
				userIds: $this->taskUsers
			));
		}
	}

	public static function validateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		return array_merge($arErrors, parent::validateProperties($arTestProperties, $user));
	}

	public static function getPropertiesDialog(
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

		$currentParent = &CBPWorkflowTemplateLoader::FindParentActivityByName($arWorkflowTemplate, $activityName);

		$c = count($currentParent['Children']);
		$allowSetStatus = ($c == 1 || $currentParent['Children'][$c - 1]['Type'] == 'SetStateActivity');

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (
				is_array($arCurrentActivity['Properties'])
				&& array_key_exists('Permission', $arCurrentActivity['Properties'])
			)
			{
				$arCurrentValues['permission'] = CBPHelper::usersArrayToString(
					$arCurrentActivity['Properties']['Permission'],
					$arWorkflowTemplate,
					$documentType
				);
			}

			if ($c > 1 && $currentParent['Children'][$c - 1]['Type'] == 'SetStateActivity')
			{
				$arCurrentValues['setstate'] = $currentParent['Children'][$c - 1]['Properties']['TargetStateName'];
			}
		}

		$arStates = [];
		if ($allowSetStatus)
		{
			$arStates = CBPWorkflowTemplateLoader::getStatesOfTemplate($arWorkflowTemplate);
		}

		return $runtime->executeResourceFile(
			__FILE__,
			'properties_dialog.php',
			[
				'arCurrentValues' => $arCurrentValues,
				'formName' => $formName,
				'allowSetStatus' => $allowSetStatus,
				'arStates' => $arStates,
			]
		);
	}

	public static function getPropertiesDialogValues(
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

		$runtime = CBPRuntime::getRuntime();

		$arProperties = [];

		$arProperties['Permission'] = CBPHelper::usersStringToArray(
			$arCurrentValues['permission'],
			$documentType,
			$arErrors
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arErrors = self::validateProperties(
			$arProperties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $arProperties;
		$currentParent = &CBPWorkflowTemplateLoader::FindParentActivityByName($arWorkflowTemplate, $activityName);

		$c = count($currentParent['Children']);
		if ($c == 1)
		{
			if ($arCurrentValues['setstate'] != '')
			{
				$currentParent['Children'][] = [
					'Type' => 'SetStateActivity',
					'Name' => md5(uniqid(mt_rand(), true)),
					'Properties' => ['TargetStateName' => $arCurrentValues['setstate']],
					'Children' => [],
				];
			}
		}
		elseif ($currentParent['Children'][$c - 1]['Type'] == 'SetStateActivity')
		{
			if ($arCurrentValues['setstate'] != '')
			{
				$currentParent['Children'][$c - 1]['Properties']['TargetStateName'] = $arCurrentValues['setstate'];
			}
			else
			{
				unset($currentParent['Children'][$c - 1]);
			}
		}

		return true;
	}
}
