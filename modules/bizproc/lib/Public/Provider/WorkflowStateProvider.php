<?php

namespace Bitrix\Bizproc\Public\Provider;

use Bitrix\Bizproc\Internal\Container;
use Bitrix\Bizproc\Internal\Entity\WorkflowState\WorkflowStateCollection;
use Bitrix\Bizproc\Internal\Repository\WorkflowStateRepository\WorkflowStateRepositoryInterface;
use Bitrix\Main\Type\Date;

class WorkflowStateProvider
{
	private WorkflowStateRepositoryInterface $repository;

	public function __construct()
	{
		$this->repository = Container::getWorkflowStateRepository();
	}

	public function getStaleWorkflowsWithoutTasks(
		array $select,
		Date $beforeDate,
		int $limit
	): WorkflowStateCollection
	{
		return $this->repository->getStaleWorkflowsWithoutTasks(
			$select,
			$beforeDate,
			$limit,
		);
	}
}
