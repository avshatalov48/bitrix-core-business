<?php

namespace Bitrix\Bizproc\Task\Dto;

final class AddTaskDto
{
	public function __construct(
		public readonly string $workflowId,
		public readonly array $complexDocumentId,
		public readonly array $userIds,
		public readonly string $activityName,
		public readonly ?TaskSettings $settings = null,
	)
	{}
}
