<?php

namespace Bitrix\Lists\Api\Data\ServiceFactory;

use Bitrix\Bizproc\WorkflowInstanceTable;
use Bitrix\Lists\Api\Data\Filter;
use Bitrix\Main\Loader;

class ListsToGetFilter extends Filter
{
	public const ALLOWABLE_FIELDS = [
		'ID',
		'NAME',
		'TIMESTAMP_X',
		'DATE_CREATE',
		'WORKFLOW_STATE',
		'FIND', // SEARCHABLE_CONTENT
		'SEARCHABLE_CONTENT',
		'CREATED_BY',
		'IBLOCK_TYPE',
	];

	protected array $computedFilter = [
		'state' => null
	];

	public function setField(string $fieldId, $value, string $operator = ''): static
	{
		if ($fieldId === 'FIND' || $fieldId === 'SEARCHABLE_CONTENT')
		{
			return $this->setSearchableContent($value);
		}

		if ($fieldId === 'WORKFLOW_STATE')
		{
			return $this->setWorkflowState($value);
		}

		return parent::setField($fieldId, $value, $operator);
	}

	public function getFieldValue(string $fieldId)
	{
		if ($fieldId === 'WORKFLOW_STATE')
		{
			return $this->computedFilter['state'];
		}

		return parent::getFieldValue($fieldId);
	}

	public function setSearchableContent(string $value): static
	{
		if ($value !== '')
		{
			$this->filter['?SEARCHABLE_CONTENT'] = $value;
			$this->keyMatching['SEARCHABLE_CONTENT'] = '?SEARCHABLE_CONTENT';
		}

		return $this;
	}

	public function setWorkflowState(string $state): static
	{
		if ($state === 'R' || $state === 'C')
		{
			$this->computedFilter['state'] = $state;
			$this->keyMatching['WORKFLOW_STATE'] = 'WORKFLOW_STATE';
		}

		return $this;
	}

	public function setCreatedBy(int $userId): static
	{
		if ($userId >= 0)
		{
			$this->setField('CREATED_BY', (string)$userId, '=');
		}

		return $this;
	}

	public function setIBlockType(string $iBlockType): static
	{
		if (!empty($iBlockType))
		{
			$this->setField('IBLOCK_TYPE', $iBlockType, '=');
		}

		return $this;
	}

	protected function execComputedFilter(): void
	{
		if ($this->computedFilter['state'] !== null)
		{
			$this->execWorkflowStateFilter();
		}
	}

	private function execWorkflowStateFilter(): void
	{
		$state = $this->computedFilter['state'];

		if (Loader::includeModule('bizproc') && in_array($state, ['R', 'C'], true))
		{
			$query =
				WorkflowInstanceTable::query()
					->setDistinct()
					->setSelect(['DOCUMENT_ID'])
					->where('MODULE_ID', 'lists')
					->where('ENTITY', \BizprocDocument::class)
			;
			if ($this->hasField('CREATED_BY'))
			{
				$query->where('STARTED_BY',$this->getFieldValue('CREATED_BY'));
			}

			$activeWorkflow = array_column($query->exec()->fetchAll(), 'DOCUMENT_ID');

			if ($state === 'R')
			{
				$this->filter['=ID'] = $activeWorkflow;
				$this->keyMatching['ID'] = '=ID';
			}

			if ($state === 'C')
			{
				$this->filter['!=ID'] = $activeWorkflow;
				$this->keyMatching['ID'] = '!=ID';
			}
		}
	}
}
