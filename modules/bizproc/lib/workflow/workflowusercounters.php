<?php

namespace Bitrix\Bizproc\Workflow;

class WorkflowUserCounters
{
	private const TASK_COUNTER_CODE = 'bp_tasks';
	private const COMMENT_COUNTER_CODE = 'bp_wf_comments';
	private const WORKFLOW_COUNTER_CODE = 'bp_workflow';
	private readonly int $userId;
	private readonly string $siteId;
	private static array $isNeedSyncWorkflow = [];

	public function __construct(int $userId)
	{
		$this->userId = max($userId, 0);
		$this->siteId = '**';

		if (!isset(self::$isNeedSyncWorkflow[$this->userId]))
		{
			[
				'task' => $task,
				'comment' => $comment,
				'workflow' => $workflow,
			] = $this->getCounters();

			$newWorkflow = ($task ?: 0) + ($comment ?: 0);

			self::$isNeedSyncWorkflow[$this->userId] = $workflow !== $newWorkflow;
		}
	}

	public function incrementTask(int $increment = 1): void
	{
		if ($increment <= 0)
		{
			return;
		}

		$this->syncWorkflowIfNeed();
		$this->increment(self::TASK_COUNTER_CODE, $increment);
		$this->increment(self::WORKFLOW_COUNTER_CODE, $increment);
	}

	public function decrementTask(int $decrement = 1): void
	{
		if ($decrement <= 0)
		{
			return;
		}

		$this->syncWorkflowIfNeed();
		$this->decrement(self::TASK_COUNTER_CODE, $decrement);
		$this->decrement(self::WORKFLOW_COUNTER_CODE, $decrement);
	}

	public function incrementComment(int $increment = 1): void
	{
		if ($increment <= 0)
		{
			return;
		}

		$this->syncWorkflowIfNeed();
		$this->increment(self::COMMENT_COUNTER_CODE, $increment);
		$this->increment(self::WORKFLOW_COUNTER_CODE, $increment);
	}

	public function decrementComment(int $decrement = 1): void
	{
		if ($decrement <= 0)
		{
			return;
		}

		$this->syncWorkflowIfNeed();
		$this->decrement(self::COMMENT_COUNTER_CODE, $decrement);
		$this->decrement(self::WORKFLOW_COUNTER_CODE, $decrement);
	}

	public function setTask(int $value = 0): void
	{
		if ($value < 0)
		{
			return;
		}

		$task = $this->get(self::TASK_COUNTER_CODE);
		if ($task !== $value)
		{
			$this->set(self::TASK_COUNTER_CODE, $value);
		}

		$this->syncWorkflow();
	}

	public function setComment(int $value = 0): void
	{
		if ($value < 0)
		{
			return;
		}

		$comment = $this->get(self::COMMENT_COUNTER_CODE);
		if ($comment !== $value)
		{
			$this->set(self::COMMENT_COUNTER_CODE, $value);
		}

		$this->syncWorkflow();
	}

	private function syncWorkflowIfNeed(): void
	{
		if (self::$isNeedSyncWorkflow[$this->userId])
		{
			$this->syncWorkflow();
			self::$isNeedSyncWorkflow[$this->userId] = false;
		}
	}

	private function syncWorkflow(): void
	{
		[
			'task' => $task,
			'comment' => $comment,
			'workflow' => $workflow,
		] = $this->getCounters();

		$newWorkflow = ($task ?: 0) + ($comment ?: 0);
		if ($newWorkflow !== $workflow)
		{
			$this->set(self::WORKFLOW_COUNTER_CODE, $newWorkflow);
		}
	}

	private function getCounters(): array
	{
		$task = $this->get(self::TASK_COUNTER_CODE);
		$comment = $this->get(self::COMMENT_COUNTER_CODE);
		$workflow = $this->get(self::WORKFLOW_COUNTER_CODE);

		return [
			'task' => $task,
			'comment' => $comment,
			'workflow' => $workflow
		];
	}

	private function increment(string $code, int $increment): void
	{
		\CUserCounter::Increment($this->userId, $code, $this->siteId, true, $increment);
	}

	private function decrement(string $code, int $decrement): void
	{
		\CUserCounter::Decrement($this->userId, $code, $this->siteId, true, $decrement);
	}

	private function set(string $code, int $value): void
	{
		\CUserCounter::Set($this->userId, $code, $value, $this->siteId);
	}

	private function get(string $code): false|int
	{
		return \CUserCounter::GetValue($this->userId, $code, $this->siteId);
	}
}
