<?php

namespace Bitrix\Bizproc\Task\Dto\ExternalEventTask;

final class RemoveCommandDto
{
	public function __construct(
		public readonly string $id,
		public readonly array $userIds,
	)
	{}
}
