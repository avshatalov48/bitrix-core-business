<?php

namespace Bitrix\Bizproc\Task\Dto;

use Bitrix\Bizproc\Task\Data\ActivityData;

final class CompleteTaskDto
{
	public function __construct(
		public readonly ?int $status = null,
		public readonly ActivityData $activityData = new ActivityData([]),
	)
	{}
}
