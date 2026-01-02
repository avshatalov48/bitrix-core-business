<?php

namespace Bitrix\Bizproc\Internal\Repository\WorkflowStateRepository;

use Bitrix\Bizproc\Internal\Entity\WorkflowState\WorkflowStateCollection;
use Bitrix\Bizproc\Internal\Repository\Mapper\WorkflowStateMapper;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Main\ORM\Query\QueryHelper;
use Bitrix\Main\Type\Date;

class WorkflowStateRepository implements WorkflowStateRepositoryInterface
{
	public function __construct(private readonly WorkflowStateMapper $mapper)
	{
	}

	public function getStaleWorkflowsWithoutTasks(
		array $select,
		Date $beforeDate,
		int $limit
	): WorkflowStateCollection
	{
		$query =
			WorkflowStateTable::query()
				->setSelect($select)
				->whereNull('INSTANCE.ID')
				->whereNull('TASKS.ID')
				->where('STARTED', '<', $beforeDate)
				->setLimit($limit)
		;
		$ormWorkflowStates = $query->fetchCollection();

		$workflowStates = [];
		foreach ($ormWorkflowStates as $ormWorkflowState)
		{
			$workflowStates[] = $this->mapper->convertFromOrm($ormWorkflowState);
		}

		return new WorkflowStateCollection(...$workflowStates);
	}
}
