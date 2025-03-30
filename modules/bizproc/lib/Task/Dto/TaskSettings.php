<?php

namespace Bitrix\Bizproc\Task\Dto;

final class TaskSettings
{
	public function __construct(
		public readonly string $name = '',
		public readonly string $description = '',
		public readonly bool $isInline = false,
		public readonly int $delegationType = \CBPTaskDelegationType::Subordinate,
		public readonly array $parameters = [],
	)
	{}
}
