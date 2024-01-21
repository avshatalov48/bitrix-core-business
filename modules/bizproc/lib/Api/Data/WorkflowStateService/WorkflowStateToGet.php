<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowStateService;

use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Main\DB\SqlExpression;

class WorkflowStateToGet
{
	private array $select = ['ID', 'MODULE_ID', 'DOCUMENT_ID', 'ENTITY'];
	private int $filterUserId = 0;
	private ?string $filterPresetId;
	private ?array $filterWorkflowIds;
	private int $limit = 0;
	private int $offset = 0;
	private bool $isSelectAllFields = false;

	private bool $countTotal = false;

	public function setAdditionalSelectFields(array $additionalSelect): static
	{
		$allowedFields = $this->getAllowedAdditionalFields();

		foreach ($additionalSelect as $fieldId)
		{
			if (in_array($fieldId, $allowedFields, true) && !in_array($fieldId, $this->select, true))
			{
				$this->select[] = $fieldId;
			}
		}

		return $this;
	}

	public function setSelectAllFields(bool $flag = true): static
	{
		$this->isSelectAllFields = $flag;

		return $this;
	}

	public function setFilterUserId(int $userId): static
	{
		$this->filterUserId = $userId;

		return $this;
	}

	public function getFilterUserId(): int
	{
		return $this->filterUserId;
	}

	public function setFilterPresetId(string $presetId): static
	{
		if (WorkflowStateFilter::isDefined($presetId))
		{
			$this->filterPresetId = $presetId;
		}

		return $this;
	}

	public function getFilterPresetId(): ?string
	{
		return $this->filterPresetId;
	}

	public function setFilterWorkflowIds(array $workflowIds): static
	{
		$this->filterWorkflowIds = $workflowIds;

		return $this;
	}

	public function getFilterWorkflowIds(): ?array
	{
		return $this->filterWorkflowIds;
	}

	public function setLimit(int $limit): static
	{
		if ($limit >= 0)
		{
			$this->limit = $limit;
		}

		return $this;
	}

	public function setOffset(int $offset): static
	{
		if ($offset >= 0)
		{
			$this->offset = $offset;
		}

		return $this;
	}

	public function countTotal(bool $count = true): static
	{
		$this->countTotal = $count;

		return $this;
	}

	public function isCountingTotal(): bool
	{
		return $this->countTotal;
	}

	public function getSelect(): array
	{
		if ($this->isSelectAllFields)
		{
			return array_merge($this->select, $this->getAllowedAdditionalFields());
		}

		return $this->select;
	}

	public function getOrmFilter(): array
	{
		$filter = [
			'=USER_ID' => $this->filterUserId,
		];

		if (!empty($this->filterWorkflowIds))
		{
			$filter['@WORKFLOW_ID'] = $this->filterWorkflowIds;
		}

		$filterPresetId = $this->filterPresetId ?? WorkflowStateFilter::PRESET_DEFAULT;

		if ($filterPresetId === WorkflowStateFilter::PRESET_STARTED)
		{
			$filter['=IS_AUTHOR'] = 1;
		}
		elseif ($filterPresetId === WorkflowStateFilter::PRESET_HAS_TASK)
		{
			$filter['>TASK_STATUS'] = WorkflowUserTable::TASK_STATUS_NONE;
		}
		elseif ($filterPresetId === WorkflowStateFilter::PRESET_ALL_COMPLETED)
		{
			$filter['=WORKFLOW_STATUS'] = WorkflowUserTable::WORKFLOW_STATUS_COMPLETED;
		}
		else // ($filterPresetId === WorkflowStateFilter::PRESET_IN_WORK)
		{
			$filter['=WORKFLOW_STATUS'] = new SqlExpression('?i', WorkflowUserTable::WORKFLOW_STATUS_ACTIVE);
			$filter[] = [
				'LOGIC' => 'OR',
				'=IS_AUTHOR' => 1,
				'=TASK_STATUS' => WorkflowUserTable::TASK_STATUS_ACTIVE,
			];
		}

		return $filter;
	}

	public function getOrder(): array
	{
		$filterPresetId = $this->filterPresetId ?? WorkflowStateFilter::PRESET_DEFAULT;

		if ($filterPresetId === WorkflowStateFilter::PRESET_ALL_COMPLETED)
		{
			return ['MODIFIED' => 'DESC'];
		}

		return ['TASK_STATUS' => 'DESC', 'MODIFIED' => 'DESC'];
	}

	public function getLimit(): int
	{
		return $this->limit;
	}

	public function getOffset(): int
	{
		return $this->offset;
	}

	private function getAllowedAdditionalFields(): array
	{
		return [
			'STARTED_BY',
			'STARTED',
			'MODIFIED',
			'WORKFLOW_TEMPLATE_ID',
			'TEMPLATE.NAME',
			// 'DOCUMENT_ID_INT',
			//'STATE',
			'STATE_TITLE',
			// 'STATE_PARAMETERS',

			'TASKS.ID',
			'TASKS.ACTIVITY',
			'TASKS.MODIFIED',
			'TASKS.OVERDUE_DATE',
			'TASKS.NAME',
			'TASKS.DESCRIPTION',
			'TASKS.STATUS',
			'TASKS.IS_INLINE',
			'TASKS.DELEGATION_TYPE',
			'TASKS.PARAMETERS',

			'TASKS.TASK_USERS.USER_ID',
			'TASKS.TASK_USERS.STATUS',
			'TASKS.TASK_USERS.DATE_UPDATE',
			'TASKS.TASK_USERS.ORIGINAL_USER_ID',
		];
	}
}
