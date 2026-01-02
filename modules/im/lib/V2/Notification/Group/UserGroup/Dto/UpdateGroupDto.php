<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup\Dto;

use Bitrix\Main\Validation\Rule\Length;
use Bitrix\Main\Validation\Rule\PositiveNumber;

class UpdateGroupDto
{
	public function __construct(
		#[Length(min: 1, max: 255)]
		public readonly string $title,
		#[PositiveNumber]
		public readonly int $groupId,
	) {}
}