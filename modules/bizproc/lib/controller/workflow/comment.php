<?php

namespace Bitrix\Bizproc\Controller\Workflow;

use Bitrix\Bizproc;
use Bitrix\Bizproc\Api\Request\WorkflowCommentService\MarkAsReadRequest;
use Bitrix\Bizproc\Api\Service\WorkflowCommentService;
use Bitrix\Main\Error;

class Comment extends Bizproc\Controller\Base
{
	/**
	 * @param string $workflowId
	 * @param int $userId
	 * @return bool|null
	 */
	public function markAsReadAction(string $workflowId, int $userId): ?bool
	{
		$currentUserId = (int)($this->getCurrentUser()?->getId());

		if ($currentUserId !== $userId)
		{
			$this->addError(new Error('access denied'));

			return null;
		}

		$service = new WorkflowCommentService();
		$service->markAsRead(new MarkAsReadRequest($workflowId, $userId));

		return true;
	}
}
