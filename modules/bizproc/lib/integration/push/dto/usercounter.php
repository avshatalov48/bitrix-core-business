<?php

namespace Bitrix\Bizproc\Integration\Push\Dto;

final class UserCounter
{
	public function __construct(
		public readonly int $all,
		public readonly int $new,
		public readonly int $allUnread,
	) {}
}
