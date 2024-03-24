<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowStateService;

use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Main\ArgumentException;
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

	private array $selectTaskFields = [];
	private ?int $selectTaskLimit = 50;

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

	public function setTaskSelectFields(array $taskSelect): static
	{
		$allowedTaskFields = $this->getAllowedTaskFields();

		foreach ($taskSelect as $fieldId)
		{
			if (in_array($fieldId, $allowedTaskFields, true))
			{
				$this->selectTaskFields[] = $fieldId;
			}
		}

		$this->selectTaskFields = array_unique($this->selectTaskFields);

		return $this;
	}

	public function getSelectTaskFields(): array
	{
		return $this->selectTaskFields;
	}

	/**
	 * Sets tasks limit to get for each workflow. Throws argument exception when $taskLimit is non-positive integer.
	 *
	 * @param int|null $taskLimit
	 * @return $this
	 * @throws ArgumentException
	 */
	public function setSelectTaskLimit(?int $taskLimit): static
	{
		if (is_int($taskLimit))
		{
			if ($taskLimit <= 0)
			{
				throw new ArgumentException('Task limit must be positive integer or null');
			}
			$this->selectTaskLimit = $taskLimit;
		}
		else
		{
			$this->selectTaskLimit = null;
		}

		return $this;
	}

	public function getSelectTaskLimit(): ?int
	{
		return $this->selectTaskLimit;
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

		if (
			$filterPresetId === WorkflowStateFilter::PRESET_ALL_COMPLETED
			|| $filterPresetId === WorkflowStateFilter::PRESET_STARTED
		)
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
		return array_merge(
			[
				'STARTED_BY',
				'STARTED',
				'MODIFIED',
				'WORKFLOW_TEMPLATE_ID',
				'TEMPLATE.NAME',
				// 'DOCUMENT_ID_INT',
				//'STATE',
				'STATE_TITLE',
				// 'STATE_PARAMETERS',
			],
		);
	}

	private function getAllowedTaskFields(): array
	{
		return [
			'ID',
			'ACTIVITY',
			'MODIFIED',
			'OVERDUE_DATE',
			'NAME',
			'DESCRIPTION',
			'STATUS',
			'IS_INLINE',
			'DELEGATION_TYPE',
			'PARAMETERS',

			'TASK_USERS.USER_ID',
			'TASK_USERS.STATUS',
			'TASK_USERS.DATE_UPDATE',
			'TASK_USERS.ORIGINAL_USER_ID',
		];
	}
}
