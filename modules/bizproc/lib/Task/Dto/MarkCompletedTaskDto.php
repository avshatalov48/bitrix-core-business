<?php

namespace Bitrix\Bizproc\Task\Dto;

use Bitrix\Bizproc\Task\Data\ActivityData;

final class MarkCompletedTaskDto
{
	public function __construct(
		public readonly int $userId,
		public readonly ?int $status = null,
		public readonly ActivityData $data = new ActivityData([]),
	)
	{}
}
