<?php

namespace Bitrix\Bizproc\Task;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Task\Data\TaskData;
use Bitrix\Bizproc\Task\Dto\AddTaskDto;
use Bitrix\Bizproc\Task\Dto\CompleteTaskDto;
use Bitrix\Bizproc\Task\Dto\DeleteTaskDto;
use Bitrix\Bizproc\Task\Dto\MarkCompletedTaskDto;
use Bitrix\Bizproc\Task\Dto\UpdateTaskDto;

interface Task
{
	public static function getAssociatedActivity(): string;

	public static function add(AddTaskDto $task): ?Task;

	public function __construct(TaskData $task, int $userId);

	public function update(UpdateTaskDto $updateData): Result;

	public function markCompleted(MarkCompletedTaskDto $markCompletedData): Result;

	public function complete(CompleteTaskDto $completeData): Result;

	public function delete(DeleteTaskDto $deleteData): Result;

	public function getTaskControls(): array;

	public function postTaskForm(array $request): Result;
}
