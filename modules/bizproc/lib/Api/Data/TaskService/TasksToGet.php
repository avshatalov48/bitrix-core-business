<?php

namespace Bitrix\Bizproc\Api\Data\TaskService;

use Bitrix\Bizproc\Api\Request\TaskService\GetUserTaskListRequest;
use Bitrix\Main\ArgumentOutOfRangeException;

final class TasksToGet
{
	private array $additionalSelectFields = [];
	private array $sort = [];
	private array $filter = [];
	private int $offset = 0;
	private int $limit = 10;

	private function __construct(
		private int $targetUserId,
	)
	{}

	/**
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromRequest(GetUserTaskListRequest $request): self
	{
		$targetUserId = self::validateUserId($request->filter['USER_ID'] ?? 0);
		if (!$targetUserId)
		{
			throw new ArgumentOutOfRangeException('targetUserId', 1, null);
		}

		$tasksToGet = new self($targetUserId);

		if ($request->additionalSelectFields)
		{
			$tasksToGet->setAdditionalSelectFields($request->additionalSelectFields);
		}
		if ($request->sort)
		{
			$tasksToGet->setSort($request->sort);
		}

		$tasksToGet->offset = $request->offset;
		$tasksToGet->limit = $request->limit;
		$tasksToGet->filter = $request->filter;

		return $tasksToGet;
	}

	/**
	 * @param int $userId
	 * @return int
	 */
	private static function validateUserId(int $userId): int
	{
		return max($userId, 0);
	}

	/**
	 * @return int
	 */
	public function getTargetUserId(): int
	{
		return $this->targetUserId;
	}

	/**
	 * @param array $additionalSelectFields
	 * @return void
	 */
	private function setAdditionalSelectFields(array $additionalSelectFields): void
	{
		$additionalAllowedFields = [
			'ACTIVITY_NAME',
			'NAME',
			'DESCRIPTION',
			'DELEGATION_TYPE',
			'WORKFLOW_TEMPLATE_ID',
			'MODULE_ID',
			'ENTITY',
			'DOCUMENT_ID',
			'WORKFLOW_TEMPLATE_NAME',
			'WORKFLOW_TEMPLATE_TEMPLATE_ID',
			'WORKFLOW_STARTED',
			'WORKFLOW_STARTED_BY',
		];

		$fields = [];
		foreach ($additionalSelectFields as $fieldId)
		{
			if (in_array($fieldId, $additionalAllowedFields, true))
			{
				$fields[] = $fieldId;
			}
		}

		$this->additionalSelectFields = $fields;
	}

	/**
	 * @return array
	 */
	public function getAdditionalSelectFields(): array
	{
		return $this->additionalSelectFields;
	}

	/**
	 * @param array $sort
	 * @return void
	 */
	private function setSort(array $sort): void
	{
		$this->sort = $sort;
	}

	/**
	 * @return array
	 */
	public function getSort(): array
	{
		return $this->sort;
	}

	/**
	 * @return array
	 */
	public function getFilter(): array
	{
		return $this->filter;
	}

	/**
	 * @return int
	 */
	public function getOffset(): int
	{
		return $this->offset;
	}

	/**
	 * @return int
	 */
	public function getLimit(): int
	{
		return $this->limit;
	}

	/**
	 * @return array
	 */
	public function buildSelectFields(): array
	{
		$selectFields = [
			'ID' => 'ID',
			'WORKFLOW_ID' => 'WORKFLOW_ID',
			'ACTIVITY' => 'ACTIVITY',
			'MODIFIED' => 'MODIFIED',
			'OVERDUE_DATE' => 'OVERDUE_DATE',
			'PARAMETERS' => 'PARAMETERS',
			'IS_INLINE' => 'IS_INLINE',
			'STATUS' => 'STATUS',
			'DOCUMENT_NAME' => 'DOCUMENT_NAME',
			'USER_ID' => 'USER_ID',
			'USER_STATUS' => 'USER_STATUS',
			'WORKFLOW_STATE' => 'WORKFLOW_STATE',
		];

		foreach ($this->getAdditionalSelectFields() as $fieldId)
		{
			$selectFields[$fieldId] = $fieldId;
		}

		return array_keys($selectFields);
	}
}
