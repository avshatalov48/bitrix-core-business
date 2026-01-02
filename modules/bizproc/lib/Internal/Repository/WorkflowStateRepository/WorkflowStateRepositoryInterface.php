<?php

namespace Bitrix\Bizproc\Internal\Repository\WorkflowStateRepository;

interface WorkflowStateRepositoryInterface
{
	/**
	 * @param array $select
	 * @param \Bitrix\Main\Type\Date $beforeDate
	 * @param int $limit
	 * @return \Bitrix\Bizproc\Internal\Entity\WorkflowState\WorkflowStateCollection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getStaleWorkflowsWithoutTasks(
		array $select,
		\Bitrix\Main\Type\Date $beforeDate,
		int $limit
	): \Bitrix\Bizproc\Internal\Entity\WorkflowState\WorkflowStateCollection;
}
