<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup\Dto;

use Bitrix\Main\Validation\Rule\Length;
use Bitrix\Main\Validation\Rule\PositiveNumber;

class CreateGroupDto
{
	public function __construct(
		#[Length(min: 1, max: 255)]
		public readonly string $title,
		#[PositiveNumber]
		public readonly int $userId,
		#[Length(min: 1, max: 255)]
		public readonly string $module,
		#[Length(min: 1, max: 255)]
		public readonly string $event,
	)
	{}
}