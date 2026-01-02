<?php

namespace Bitrix\Bizproc\Internal\Entity\WorkflowState;

use Bitrix\Bizproc\Internal\Entity\BaseEntityCollection;

/**
 * @method WorkflowState|null getFirstCollectionItem()
 * @method \ArrayIterator<WorkflowState> getIterator()
 */
class WorkflowStateCollection extends BaseEntityCollection
{
	public function __construct(WorkflowState ...$workflowStates)
	{
		foreach ($workflowStates as $workflowState)
		{
			$this->collectionItems[] = $workflowState;
		}
	}
}
