<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup\Dto;

use Bitrix\Main\Validation\Rule\Length;
use Bitrix\Main\Validation\Rule\NotEmpty;

class ConditionDto
{
	public function __construct(
		#[Length(min: 1, max: 255)]
		public readonly string $module,
		#[Length(min: 1, max: 255)]
		public readonly string $event,
		#[NotEmpty]
		public readonly int $userId,
		#[NotEmpty]
		public readonly int $groupId,
	) {}
}