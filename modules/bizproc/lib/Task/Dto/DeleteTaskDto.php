<?php

namespace Bitrix\Bizproc\Task\Dto;

use Bitrix\Bizproc\Task\Data\ActivityData;

final class DeleteTaskDto
{
	public function __construct(
		public readonly ActivityData $activityData = new ActivityData([]),
	)
	{}
}
