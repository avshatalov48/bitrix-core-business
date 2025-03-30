<?php

namespace Bitrix\Bizproc\Task\Dto\ExternalEventTask;

final class AddCommandDto
{
	public function __construct(
		public readonly string $id,
		public readonly array $userIds,
		public readonly string $workflowId,
	)
	{}
}
