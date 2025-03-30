<?php

namespace Bitrix\Bizproc\Task\Dto;

final class UpdateTaskDto
{
	public function __construct(
		public readonly ?int $status = null, // \CBPTaskStatus
		public readonly array $users = [],
		public readonly ?array $parameters = null,
	)
	{}
}
