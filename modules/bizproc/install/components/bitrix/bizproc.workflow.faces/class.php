<?php

use Bitrix\Main;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!\Bitrix\Main\Loader::includeModule('bizproc'))
{
	return;
}

class BizprocWorkflowFaces extends \CBitrixComponent
{

	protected $workflowId;

	protected function getWorkflowId()
	{
		if ($this->workflowId === null)
		{
			$this->workflowId = !empty($this->arParams['WORKFLOW_ID']) ? preg_replace('#[^A-Z0-9\.]#i', '', $this->arParams['WORKFLOW_ID']) : 0;
		}
		return $this->workflowId;
	}

	protected function getTargetTaskId()
	{
		return isset($this->arParams['TARGET_TASK_ID']) ? (int)$this->arParams['TARGET_TASK_ID'] : 0;
	}

	protected function getStartedBy($workflowState)
	{
		if ($workflowState['STARTED_BY'])
		{
			$iterator = CUser::GetList("id", "asc",
				array('ID' => $workflowState['STARTED_BY']),
				array('FIELDS' => array('ID', 'PERSONAL_PHOTO', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'TITLE'))
			);
			$startedUser = $iterator->fetch();
			if ($startedUser)
				return $startedUser;
		}
		return false;
	}

	protected function getWorkflowStateInfo()
	{
		if (!empty($this->arParams['~WORKFLOW_STATE_INFO']) && is_array($this->arParams['~WORKFLOW_STATE_INFO']))
			return $this->arParams['~WORKFLOW_STATE_INFO'];
		return \CBPStateService::getWorkflowStateInfo($this->getWorkflowId());
	}

	protected function rebuildTaskList(&$tasks, $taskId)
	{
		$target = null;
		$historyMode = false;
		while ($task = array_shift($tasks['RUNNING']))
		{
			if ($task['ID'] == $taskId)
			{
				$target = $task;
				break;
			}
		}
		if (!$target)
		{
			while ($task = array_shift($tasks['COMPLETED']))
			{
				if ($task['ID'] == $taskId)
				{
					$target = $task;
					break;
				}
				else
					$historyMode = true;
			}
		}
		if ($target)
		{
			$tasks['RUNNING'] = array($target);
		}

		$tasks['COMPLETED_CNT'] = sizeof($tasks['COMPLETED']);
		$tasks['RUNNING_CNT'] = sizeof($tasks['RUNNING']);
		$tasks['RUNNING_ALL_USERS'] = isset($tasks['RUNNING'][0]) ? $tasks['RUNNING'][0]['USERS'] : array();
		$tasks['IS_HISTORY'] = $historyMode;
	}

	public function executeComponent()
	{
		if ($this->getWorkflowId())
		{
			$workflowState = $this->getWorkflowStateInfo();
			$tasks = CBPViewHelper::getWorkflowTasks($workflowState['ID'], true, true);
			$lastUserStatus = CBPTaskUserStatus::Ok;
			if (isset($tasks['COMPLETED'][0]['USERS'][0]['STATUS']))
				$lastUserStatus = $tasks['COMPLETED'][0]['USERS'][0]['STATUS'];

			if ($this->getTargetTaskId())
				$this->rebuildTaskList($tasks, $this->getTargetTaskId());

			$this->arResult = array(
				'WORKFLOW_ID' => $this->getWorkflowId(),
				'STATE_TITLE' => $workflowState['WORKFLOW_STATUS'] === null && empty($tasks['IS_HISTORY'])? $workflowState['STATE_TITLE'] : '',
				'TASKS' => $tasks,
				'STARTED_BY' => $this->getStartedBy($workflowState),
				'DOCUMENT_ID' => $workflowState['DOCUMENT_ID'],
				'LAST_USER_STATUS' => $lastUserStatus,
			);
		}

		if (
			isset($this->arParams['SITE_TEMPLATE_ID'])
			&& $this->arParams['SITE_TEMPLATE_ID'] <> ''
		)
		{
			$this->setSiteTemplateId($this->arParams['SITE_TEMPLATE_ID']);
		}

		$this->includeComponentTemplate();
	}

	public static function prepareTasksForJs($tasks)
	{
		$result = array();
		foreach ($tasks as $task)
		{
			$t = array(
				'ID' => $task['ID'],
				'NAME' => $task['NAME'],
				'USERS' => array()
			);
			foreach ($task['USERS'] as $user)
			{
				$t['USERS'][] = array(
					'USER_ID' => $user['USER_ID'],
					'STATUS' => $user['STATUS'],
					'FULL_NAME' => htmlspecialcharsbx($user['FULL_NAME']),
					'PHOTO_SRC' => htmlspecialcharsbx($user['PHOTO_SRC']),
				);
			}
			$result[] = $t;
		}
		return $result;
	}
}