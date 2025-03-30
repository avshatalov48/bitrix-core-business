<?php

namespace Bitrix\Bizproc\Task;

use Bitrix\Bizproc\Error;
use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Task\Data\TaskData;
use Bitrix\Bizproc\Task\Dto\CompleteTaskDto;
use Bitrix\Bizproc\Task\Dto\DeleteTaskDto;
use Bitrix\Bizproc\Task\Dto\UpdateTaskDto;
use Bitrix\Main\Type\Collection;

abstract class BaseTask implements Task
{
	protected TaskData $task;
	protected int $userId;
	protected int $originalUserId;

	public function __construct(TaskData $task, int $userId)
	{
		$this->task = $task;
		$this->userId = max($userId, 0);

		$this->originalUserId = max(\CBPTaskService::getOriginalTaskUserId($task->id, $this->userId), 0);
	}

	public function getId(): int
	{
		return $this->task->id;
	}

	public static function add(Dto\AddTaskDto $task): ?Task
	{
		if (!$task->settings || empty(static::getAssociatedActivity()))
		{
			return null;
		}

		$taskData = [
			'WORKFLOW_ID' => $task->workflowId,
			'USERS' => $task->userIds,
			'ACTIVITY' => static::getAssociatedActivity(),
			'ACTIVITY_NAME' => $task->activityName,
			'NAME' => $task->settings->name,
			'DESCRIPTION' => $task->settings->description,
			'IS_INLINE' => $task->settings->isInline ? 'Y' : 'N',
			'DELEGATION_TYPE' => $task->settings->delegationType,
			'PARAMETERS' => array_merge(
				$task->settings->parameters,
				['DOCUMENT_ID' => $task->complexDocumentId, 'WORKFLOW_ID' => $task->workflowId]
			),
		];

		$taskService = static::getTaskService();
		$taskId = $taskService::add($taskData);
		if (!$taskId)
		{
			return null;
		}

		$taskData['ID'] = $taskId;

		return new static(TaskData::createFromArray($taskData), 0);
	}

	public function update(UpdateTaskDto $updateData): Result
	{
		$fields = [];

		if ($updateData->users) // can not be empty. if empty then use delete
		{
			$users = $updateData->users;
			Collection::normalizeArrayValuesByInt($users, false);
			if ($users)
			{
				$fields['USERS'] = $users;
			}
		}

		if ($updateData->parameters !== null)
		{
			$fields['PARAMETERS'] = $updateData->parameters;
		}

		if ($updateData->status !== null && $updateData->status > \CBPTaskStatus::Running)
		{
			$fields['STATUS'] = $updateData->status;
		}

		if ($fields)
		{
			$taskService = static::getTaskService();
			$taskService::update($this->getId(), $fields);

			return Result::createOk();
		}

		return Result::createError(new Error('empty fields'));
	}

	public function delete(DeleteTaskDto $deleteData): Result
	{
		$taskService = static::getTaskService();
		$taskService::delete($this->getId());

		return Result::createOk();
	}

	public function complete(CompleteTaskDto $completeData): Result
	{
		if ($completeData->status <= \CBPTaskStatus::Running)
		{
			return Result::createError(new Error('not finish status'));
		}

		return $this->update(new UpdateTaskDto(status: \CBPTaskStatus::CompleteOk));
	}

	protected static function getTaskService(): \CBPTaskService
	{
		static $taskService;
		$taskService ??= \CBPRuntime::getRuntime()->getTaskService();

		return $taskService;
	}
}
